<?php

namespace app\components\parser;

use models\Category;
use models\Product;
use Yii;

/**
 * Парсер бюджета, реализующий принципы SOLID, DRY и KISS.
 *
 * Responsibilities:
 *  - Чтение данных из источника (через DataProviderInterface)
 *  - Трансформация данных в набор бюджетных записей
 *  - Делегирование сохранения данных репозиторию
 */
class BudgetParser
{
    private const int CHUNK_SIZE = 500;

    private BudgetRepositoryInterface $repository;

    public function __construct(
        private readonly DataProviderInterface $dataProvider,
        ?BudgetRepositoryInterface             $repository = null
    )
    {
        $this->repository = $repository ?? new BudgetRepository();
    }

    /**
     * Основной метод для парсинга и импорта данных.
     */
    public function parseAndSave(): void
    {
        $header = null;
        $months = [];
        $batchInsert = [];
        $currentCategory = null;

        foreach ($this->dataProvider->getData() as $row) {
            // Первая строка – заголовок с названиями месяцев
            if ($header === null) {
                $header = $row;
                $months = $this->extractMonths($header);
                continue;
            }

            if (empty($row) || !isset($row[0])) {
                continue;
            }

            // Если найдено слово "COOP" в первой ячейке, прекращаем обработку
            if (stripos((string)$row[0], 'COOP') !== false) {
                break;
            }

            // Если строка не содержит символ '$', считаем её категорией
            if ($this->isCategoryRow($row)) {
                $currentCategory = trim((string)$row[0]);
                continue;
            }

            $productName = trim((string)$row[0]);
            if ($productName === '') {
                continue;
            }

            try {
                // Получаем или создаем категорию и продукт
                $category = Category::findOrCreateCategory($currentCategory ?: 'Без категории');
                $product = Product::findOrCreateProduct($category->id, $productName);

                // Преобразуем бюджетные данные для каждого месяца и накапливаем для пакетной вставки
                foreach ($months as $monthIndex => $month) {
                    $cellValue = $row[$monthIndex + 1] ?? '';
                    $amount = $this->parseAmount($cellValue);
                    $batchInsert[] = [$product->id, $month, (int)date('Y'), $amount];

                    if (count($batchInsert) >= self::CHUNK_SIZE) {
                        $this->repository->saveBatch($batchInsert);
                        $batchInsert = [];
                    }
                }
            } catch (\Exception $e) {
                Yii::error("Ошибка обработки строки: " . $e->getMessage(), __METHOD__);
            }
        }

        if (!empty($batchInsert)) {
            $this->repository->saveBatch($batchInsert);
        }
    }

    /**
     * Извлекает названия месяцев из заголовка.
     *
     * @param array $header
     * @return array
     */
    private function extractMonths(array $header): array
    {
        $months = [];
        $knownMonths = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        foreach ($header as $cell) {
            if (!is_string($cell) || empty($cell)) {
                continue;
            }
            $cellTrimmed = trim($cell);
            if (in_array($cellTrimmed, $knownMonths, true)) {
                $months[] = $cellTrimmed;
            }
        }
        return $months;
    }

    /**
     * Определяет, является ли строка категорией (нет значений с символом '$').
     *
     * @param array $row
     * @return bool
     */
    private function isCategoryRow(array $row): bool
    {
        foreach ($row as $cell) {
            if (!is_string($cell) || empty($cell)) {
                continue;
            }
            if (str_contains($cell, '$')) {
                return false;
            }
        }
        return true;
    }

    /**
     * Преобразует строковое значение суммы в число.
     *
     * @param string $value
     * @return float|null
     */
    private function parseAmount(string $value): ?float
    {
        $cleaned = str_replace(['$', ',', ' '], '', $value);
        return is_numeric($cleaned) ? (float)$cleaned : null;
    }
}
