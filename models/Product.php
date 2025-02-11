<?php

namespace models;

use JsonException;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Exception;

/**
 * Модель для таблицы products.
 *
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Category $category
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
            [['category_id', 'name'], 'required'],
            [['category_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['category_id', 'name'], 'unique', 'targetAttribute' => ['category_id', 'name']],
        ];
    }

    /**
     * Получаем связанную категорию.
     */
    public function getCategory(): ActiveQuery
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    /**
     * Находит или создает продукт.
     *
     * @param int $categoryId
     * @param string $productName
     * @return Product
     * @throws Exception
     * @throws JsonException
     */
    public static function findOrCreateProduct(int $categoryId, string $productName): Product
    {
        $product = static::find()->where([
            'category_id' => $categoryId,
            'name' => $productName,
        ])->one();

        if (!$product) {
            $product = new static();
            $product->category_id = $categoryId;
            $product->name = $productName;
            if (!$product->save()) {
                Yii::error("Ошибка сохранения продукта '{$productName}': " . json_encode($product->errors, JSON_THROW_ON_ERROR), __METHOD__);
            }
        }
        return $product;
    }
}