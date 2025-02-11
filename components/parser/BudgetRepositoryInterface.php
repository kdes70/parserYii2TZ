<?php
namespace app\components\parser;

/**
 * Интерфейс репозитория для сохранения бюджетных записей.
 */
interface BudgetRepositoryInterface
{
    /**
     * Сохраняет пакет (чанк) записей бюджета.
     *
     * @param array $records Массив записей вида: [product_id, month, year, amount]
     */
    public function saveBatch(array $records): void;
}
