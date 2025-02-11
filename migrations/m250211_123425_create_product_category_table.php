<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%product_category}}`.
 */
class m250211_123425_create_product_category_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('{{%product_category}}', [
            'product_id' => $this->integer()->notNull()->comment('ID продукта'),
            'category_id' => $this->integer()->notNull()->comment('ID категории'),
        ], "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->addPrimaryKey('pk_product_category', '{{%product_category}}', ['product_id', 'category_id']);

        $this->addForeignKey(
            'fk_product_category_product',
            '{{%product_category}}',
            'product_id',
            '{{%products}}',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_product_category_category',
            '{{%product_category}}',
            'category_id',
            '{{%categories}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropForeignKey('fk_product_category_product', '{{%product_category}}');
        $this->dropForeignKey('fk_product_category_category', '{{%product_category}}');
        $this->dropTable('{{%product_category}}');
    }
}
