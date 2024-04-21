<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSystemTypeToSystems extends AbstractMigration
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
    public function change(): void
    {
        $table = $this->table('systems');

        // Add SystemType column as string with default value 'D'
        $table->addColumn('SystemType', 'string', ['default' => 'D', 'after' => 'FullName'])
              ->addColumn('MaxMultipleBookings', 'integer', ['default' => 1, 'null' => false])
              ->update();

        // Update SystemType field to 'N' if FullName contains 'Nurse'
        $this->execute("UPDATE systems SET SystemType = 'N' WHERE FullName LIKE '%Nurse%'");
    }
}
