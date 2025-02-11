<?php

namespace unit\components;

use app\components\parsers\BudgetParser;
use components\parser\DataProviderInterface;
use models\Budget;
use models\Category;
use models\Product;

/**
 * Тесты для BudgetParser с разделением на категории, продукты и бюджеты.
 */
class BudgetParserTest extends \Codeception\Test\Unit
{
    public function testParseAndSave(): void
    {
        // Фейковые данные для теста:
        $fakeData = [
            // Заголовок: первая колонка пустая, далее названия месяцев
            ['', 'January', 'February', 'March'],
            // Строка категории
            ['Electronic'],
            // Строка продукта с бюджетными данными
            ['Group TV', '$100.00', '$200.00', '$300.00'],
            // Строка продукта с частично пустыми данными
            ['Television', '', '$0.00', '$50.00'],
            // Строка для прекращения парсинга
            ['COOP']
        ];

        // Фейковый DataProvider
        $dataProvider = new class($fakeData) implements DataProviderInterface {
            private array $data;

            public function __construct(array $data)
            {
                $this->data = $data;
            }

            public function getData(): array
            {
                return $this->data;
            }
        };

        // Очищаем таблицы перед тестом
        Budget::deleteAll();
        Product::deleteAll();
        Category::deleteAll();

        $parser = new BudgetParser($dataProvider);
        $parser->parseAndSave();

        // Проверяем, что категория создана
        $category = Category::find()->where(['name' => 'Electronic'])->one();
        $this->assertNotNull($category, 'Категория "Electronic" должна быть создана');

        // Проверяем, что продукты созданы
        $groupTV = Product::find()->where(['name' => 'Group TV', 'category_id' => $category->id])->one();
        $this->assertNotNull($groupTV, 'Продукт "Group TV" должен быть создан');
        $television = Product::find()->where(['name' => 'Television', 'category_id' => $category->id])->one();
        $this->assertNotNull($television, 'Продукт "Television" должен быть создан');

        // Проверяем бюджетные записи для "Group TV"
        $budgetGroupTVJanuary = Budget::find()->where([
            'product_id' => $groupTV->id,
            'month' => 'January',
            'year' => (int)date('Y'),
        ])->one();
        $this->assertNotNull($budgetGroupTVJanuary, 'Бюджет для Group TV за January должен быть создан');
        $this->assertEquals(100.00, $budgetGroupTVJanuary->amount, 'Сумма должна быть 100.00');

        // Проверяем бюджетные записи для "Television"
        $budgetTelevisionJanuary = Budget::find()->where([
            'product_id' => $television->id,
            'month' => 'January',
            'year' => (int)date('Y'),
        ])->one();
        $this->assertNotNull($budgetTelevisionJanuary, 'Бюджет для Television за January должен быть создан');
        $this->assertEquals(0.00, $budgetTelevisionJanuary->amount, 'Пустое значение должно трактоваться как 0.00');
    }
}