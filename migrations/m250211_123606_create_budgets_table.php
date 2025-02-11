<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%budgets}}`.
 */
class m250211_123606_create_budgets_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('{{%budgets}}', [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer()->notNull(),
            'month' => $this->string(20)->notNull(),
            'year' => $this->integer()->notNull(),
            'amount' => $this->decimal(15, 2)->defaultValue(0),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], "CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB");

        $this->addForeignKey(
            'fk_budgets_product',
            '{{%budgets}}',
            'product_id',
            '{{%products}}',
            'id',
            'CASCADE',
            'RESTRICT'
        );

        // Уникальный индекс для бюджета: (product_id, month, year)
        $this->createIndex(
            'idx_budgets_unique',
            '{{%budgets}}',
            ['product_id', 'month', 'year'],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('{{%budgets}}');
    }
}
