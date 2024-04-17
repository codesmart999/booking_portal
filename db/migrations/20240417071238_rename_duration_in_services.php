<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RenameDurationInServices extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function up()
    {
        // Specify the table name and column name you want to alter
        $tableName = 'services';
        $columnName = 'Duration';
        $newColumnName = 'DurationInMins';

        // Add a new column for storing duration in minutes
        $this->table($tableName)
            ->addColumn($newColumnName, 'integer', ['null' => true, 'default' => 0])
            ->update();

        // Update the new column with duration in minutes
        $this->execute("UPDATE $tableName SET $newColumnName = EXTRACT(HOUR FROM $columnName) * 60 + EXTRACT(MINUTE FROM $columnName)");

        // Remove the original duration column
        $this->table($tableName)
            ->removeColumn($columnName)
            ->update();
    }

    public function down()
    {
        // Specify the table name and column name you want to alter
        $tableName = 'services';
        $columnName = 'Duration';
        $newColumnName = 'DurationInMins';

        // Add back the original duration column
        $this->table($tableName)
            ->addColumn($columnName, 'string', ['limit' => 5, 'null' => true])
            ->update();

        // Remove the new column for storing duration in minutes
        $this->table($tableName)
            ->removeColumn($newColumnName)
            ->update();
    }
}
