<?php

namespace app\components\parser;

use app\models\Category;
use app\models\Product;
use app\models\ProductCategory;
use Yii;

class BudgetParser
{
    private const int CHUNK_SIZE = 500;
    private const string STOP_WORD = 'CO-OP'; // Парсем до строки со значением CO-OP

    public function __construct(
        private readonly DataProviderInterface $dataProvider,
        private ?BudgetRepositoryInterface     $repository = null
    )
    {
        $this->repository = $repository ?? new BudgetRepository(); // типо DI,
    }

    /**
     * Парсит данные и сохраняет их в базу, возвращая статистику.
     *
     * @return array ['reads' => int','inserted' => int, 'updated' => int]
     */
    public function parseAndSave(): array
    {
        $totalRowCount = 0;
        $totalInserted = 0;
        $totalUpdated = 0;
        $header = null;
        $months = [];
        $batchInsert = [];
        $currentCategory = null;

        // Поиск строки-заголовка, содержащей названия месяцев
        foreach ($this->dataProvider->getData() as $row) {
            $possibleMonths = $this->extractMonths($row);
            if (!empty($possibleMonths)) {
                $header = $row;
                $months = $possibleMonths;
                Yii::info("Найдены месяцы: " . implode(', ', $months), __METHOD__);
                break;
            }
        }
        if ($header === null || empty($months)) {
            Yii::error("Не найден заголовок с названиями месяцев. Проверьте структуру файла.", __METHOD__);
            return ['inserted' => 0, 'updated' => 0];
        }

        // Флаг, что заголовок найден, чтобы пропустить его при обработке
        $headerFound = false;
        foreach ($this->dataProvider->getData() as $row) {
            if (!$headerFound) {
                if ($row === $header) {
                    $headerFound = true;
                }
                continue;
            }

            if (empty($row) || !isset($row[0])) {
                continue;
            }

            if (stripos((string)$row[0], self::STOP_WORD) !== false) {
                Yii::info("Найдено слово " . self::STOP_WORD . " – прекращаю парсинг.", __METHOD__);
                break;
            }

            // Если строка содержит только первую ячейку – считаем её категорией
            if ($this->isCategoryRow($row)) {
                $currentCategory = trim((string)$row[0]);
                Yii::info("Установлена категория: {$currentCategory}", __METHOD__);
                continue;
            }

            $productName = trim((string)$row[0]);
            if ($productName === '') {
                continue;
            }

            try {
                // Находим или создаем категорию и продукт
                $category = Category::findOrCreateCategory($currentCategory ?: 'Без категории');
                $product = Product::findOrCreateProduct($productName);
                // Связываем продукт с категорией
                ProductCategory::linkCategory($product->id, $category->id);

                // Формируем бюджетные записи для каждого месяца
                foreach ($months as $monthIndex => $month) {
                    $cellValue = $row[$monthIndex + 1] ?? '';
                    $amount = $this->parseAmount((string)$cellValue);
                    $batchInsert[] = [$product->id, $month, (int)date('Y'), $amount];

                    if (count($batchInsert) >= self::CHUNK_SIZE) {
                        $result = $this->repository->upsertBatch($batchInsert);
                        $totalInserted += $result['inserted'];
                        $totalUpdated += $result['updated'];
                        $batchInsert = [];
                    }
                }
            } catch (\Exception $e) {
                Yii::error("Ошибка обработки строки: " . $e->getMessage(), __METHOD__);
            }

            $totalRowCount++;
        }

        if (!empty($batchInsert)) {
            $result = $this->repository->upsertBatch($batchInsert);
            $totalInserted += $result['inserted'];
            $totalUpdated += $result['updated'];
        }

        return ['reads' => $totalRowCount, 'inserted' => $totalInserted, 'updated' => $totalUpdated];
    }

    /**
     * Извлекает названия месяцев из строки.
     *
     * @param array $row
     * @return array
     */
    private function extractMonths(array $row): array
    {
        $months = [];
        $knownMonths = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        foreach ($row as $cell) {
            if (!is_string($cell)) {
                continue;
            }
            $cellTrimmed = trim($cell);
            foreach ($knownMonths as $knownMonth) {
                if (strcasecmp($cellTrimmed, $knownMonth) === 0) {
                    $months[] = $knownMonth;
                    break;
                }
            }
        }
        return $months;
    }

    /**
     * Определяет, является ли строка категорией (если заполнена только первая ячейка).
     *
     * @param array $row
     * @return bool
     */
    private function isCategoryRow(array $row): bool
    {
        $nonEmpty = array_filter($row, function ($cell) {
            return trim((string)$cell) !== '';
        });
        return count($nonEmpty) === 1;
    }

    /**
     * Преобразует строковое значение в число (возвращает 0.0, если не числовое).
     *
     * @param string $value
     * @return float
     */
    private function parseAmount(string $value): float
    {
        $cleaned = str_replace(['$', ',', ' '], '', $value);
        return is_numeric($cleaned) ? (float)$cleaned : 0.0;
    }
}
