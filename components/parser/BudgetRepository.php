<?php
namespace app\components\parser;

use models\Budget;
use Yii;
use yii\db\Exception;

class BudgetRepository implements BudgetRepositoryInterface
{
    public function saveBatch(array $records): void
    {
        try {
            Yii::$app->db->createCommand()
                ->batchInsert(
                    Budget::tableName(),
                    ['product_id', 'month', 'year', 'amount'],
                    $records
                )
                ->execute();
        } catch (Exception $e) {
            Yii::error("Ошибка вставки в БД: " . $e->getMessage(), __METHOD__);
        }
    }
}
