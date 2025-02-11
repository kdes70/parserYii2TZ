<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Exception;

/**
 * Модель для таблицы products.
 *
 * @property int $product_id
 * @property int $category_id
 */
class ProductCategory extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%product_category}}';
    }

    public function rules(): array
    {
        return [
            [['product_id', 'category_id'], 'required'],
            [['product_id', 'category_id'], 'integer'],
            [['product_id', 'category_id'], 'unique', 'targetAttribute' => ['product_id', 'category_id']],
        ];
    }

    /**
     * Связывает продукт с категорией, если такая связь ещё не существует.
     * @throws Exception
     */
    public static function linkCategory(int $productId, int $categoryId): bool
    {
        $exists = static::find()->where([
            'product_id' => $productId,
            'category_id' => $categoryId,
        ])->exists();
        if (!$exists) {
            $model = new static();
            $model->product_id = $productId;
            $model->category_id = $categoryId;
            if (!$model->save()) {
                Yii::error("Ошибка сохранения связи продукта с категорией'{$productId} - {$categoryId}': " . json_encode($model->errors), __METHOD__);
                return false;
            }
        }
        return true;
    }
}