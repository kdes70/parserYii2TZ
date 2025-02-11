<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Модель для таблицы budgets.
 *
 * @property int $id
 * @property int $product_id
 * @property string $month
 * @property int $year
 * @property float $amount
 *
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Product $product
 */
class Budget extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%budgets}}';
    }

    public function rules(): array
    {
        return [
            [['product_id', 'month', 'year'], 'required'],
            [['product_id', 'year'], 'integer'],
            [['amount'], 'number'],
            [['month'], 'string', 'max' => 20],
            [['product_id', 'month', 'year'], 'unique', 'targetAttribute' => ['product_id', 'month', 'year']],
        ];
    }

    public function getProduct(): ActiveQuery
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }
}