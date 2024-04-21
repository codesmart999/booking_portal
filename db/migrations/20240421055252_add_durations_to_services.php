<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddDurationsToServices extends AbstractMigration
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
        // Rename existing column "DurationInMins" to "DurationInMins_Doctor"
        $this->table('services')
             ->renameColumn('DurationInMins', 'DurationInMins_Doctor')
             ->update();

        // Add new column "DurationInMins_Nurse"
        $this->table('services')
             ->addColumn('DurationInMins_Nurse', 'integer', ['null' => true, 'default' => null, 'after' => 'DurationInMins_Doctor', 'default' => 0])
             ->update();
    }

    public function down()
    {
        // Remove the added column "DurationInMins_Nurse"
        $this->table('services')
             ->removeColumn('DurationInMins_Nurse')
             ->update();

        // Rename back the column "DurationInMins_Doctor" to "DurationInMins"
        $this->table('services')
             ->renameColumn('DurationInMins_Doctor', 'DurationInMins')
             ->update();
    }
}
