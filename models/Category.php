<?php

namespace models;

use JsonException;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Exception;

/**
 * Модель для таблицы categories.
 *
 * @property int $id
 * @property string $name
 * @property string $created_at
 * @property string $updated_at
 */
class Category extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%categories}}';
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
     * Находит или создает категорию.
     *
     * @param string|null $categoryName
     * @return Category
     * @throws Exception
     * @throws JsonException
     */
    public static function findOrCreateCategory(?string $categoryName): Category
    {
        $categoryName = trim($categoryName);
        $category = static::find()->where(['name' => $categoryName])->one();
        if (!$category) {
            $category = new static();
            $category->name = $categoryName;
            if (!$category->save()) {
                Yii::error("Ошибка сохранения категории '{$categoryName}': " . json_encode($category->errors, JSON_THROW_ON_ERROR), __METHOD__);
            }
        }
        return $category;
    }
}
