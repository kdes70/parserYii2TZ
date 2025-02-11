<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Exception;

/**
 * Модель для таблицы products.
 *
 * @property int $id
 * @property string $name
 *
 * @property string $created_at
 * @property string $updated_at
 */
class Product extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%products}}';
    }

    public function rules(): array
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique'],
        ];
    }

    /**
     * Находит или создает продукт.
     * TODO: Вынести в репозиторий, но мне лень :) KISS
     *
     * @param string $productName
     * @return Product
     * @throws Exception
     */
    public static function findOrCreateProduct(string $productName): Product
    {
        $productName = trim($productName);
        $product = static::find()->where(['name' => $productName])->one();
        if (!$product) {
            $product = new static();
            $product->name = $productName;
            if (!$product->save()) {
                Yii::error("Ошибка сохранения продукта '{$productName}': " . json_encode($product->errors), __METHOD__);
            }
        }
        return $product;
    }
}