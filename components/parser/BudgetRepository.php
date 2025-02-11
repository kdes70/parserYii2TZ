<?php

namespace app\components\parser;

use app\models\Budget;
use Yii;
use yii\db\Exception;
use yii\db\Expression;
use yii\db\Query;

class BudgetRepository implements BudgetRepositoryInterface
{
    /**
     * Выполняет upsert для чанка записей и возвращает количество вставленных и обновлённых строк.
     *
     * @param array $records Массив записей вида: [product_id, month, year, amount]
     * @return array ['inserted' => int, 'updated' => int]
     * @throws Exception
     */
    public function upsertBatch(array $records): array
    {
        // Если в чанке присутствуют дубли, оставляем только последний для каждой комбинации ключей.
        $deduped = [];
        foreach ($records as [$productId, $month, $year, $amount]) {
            $key = "{$productId}|{$month}|{$year}";
            $deduped[$key] = [$productId, $month, $year, $amount];
        }
        // Преобразуем обратно в индексированный массив.
        $records = array_values($deduped);

        $inserted = 0;
        $updated = 0;
        if (empty($records)) {
            return ['inserted' => 0, 'updated' => 0];
        }

        // Формируем массив составных ключей для поиска существующих записей.
        $keys = [];
        foreach ($records as [$productId, $month, $year, $amount]) {
            $keys[] = "{$productId}|{$month}|{$year}";
        }
        $keys = array_unique($keys);

        // Получаем существующие записи из БД по составным ключам.
        $existingRecords = (new Query())
            ->select(['product_id', 'month', 'year', 'amount'])
            ->from(Budget::tableName())
            ->where(new Expression(
                "CONCAT(product_id, '|', month, '|', year) IN ('" . implode("','", $keys) . "')"
            ))
            ->all();

        // Создаем карту существующих записей.
        $existingMap = [];
        foreach ($existingRecords as $row) {
            $key = "{$row['product_id']}|{$row['month']}|{$row['year']}";
            $existingMap[$key] = $row['amount'];
        }

        $newRecords = [];
        $updateRecords = [];

        foreach ($records as [$productId, $month, $year, $amount]) {
            $key = "{$productId}|{$month}|{$year}";
            if (isset($existingMap[$key])) {
                // Если сумма изменилась, добавляем для обновления
                if ((float)$existingMap[$key] !== (float)$amount) {
                    $updateRecords[] = [
                        'product_id' => $productId,
                        'month' => $month,
                        'year' => $year,
                        'amount' => $amount,
                    ];
                }
            } else {
                $newRecords[] = [$productId, $month, $year, $amount];
            }
        }

        // Вставляем новые записи пакетно
        if (!empty($newRecords)) {
            Yii::$app->db->createCommand()
                ->batchInsert(
                    Budget::tableName(),
                    ['product_id', 'month', 'year', 'amount'],
                    $newRecords
                )
                ->execute();
            $inserted = count($newRecords);
        }

        // Обновляем изменённые записи (по одной)
        foreach ($updateRecords as $record) {
            Yii::$app->db->createCommand()
                ->update(
                    Budget::tableName(),
                    ['amount' => $record['amount']],
                    'product_id = :pid AND month = :month AND year = :year',
                    [
                        ':pid' => $record['product_id'],
                        ':month' => $record['month'],
                        ':year' => $record['year'],
                    ]
                )
                ->execute();
            $updated++;
        }

        return ['inserted' => $inserted, 'updated' => $updated];
    }
}
