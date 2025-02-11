<?php
namespace app\components\parser;

/**
 * Интерфейс репозитория для сохранения бюджетных записей.
 */
interface BudgetRepositoryInterface
{
    /**
     * Сохраняет пакет (чанк) записей бюджета с upsert-логикой.
     *
     * @param array $records Массив записей вида: [product_id, month, year, amount]
     * @return array Вернёт массив с ключами 'inserted' и 'updated'
     */
    public function upsertBatch(array $records): array;
}
