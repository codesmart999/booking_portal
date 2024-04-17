<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddFirstLastNamesToSystems extends AbstractMigration
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
        $table = $this->table('systems')
            ->addColumn('LastName', 'string', [
                'null' => true,
                'limit' => 255,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'InternalId',
            ])
            ->addColumn('FirstName', 'string', [
                'null' => true,
                'limit' => 255,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'InternalId',
            ])
            ->addColumn('FirstEmail', 'string', [
                'null' => true,
                'limit' => 255,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'Longitude',
            ])
            // ->addColumn('SystemType', 'string', [
            //     'null' => true,
            //     'limit' => 5,
            //     'collation' => 'utf8_general_ci',
            //     'encoding' => 'utf8',
            //     'after' => 'FullName',
            //     'default' => 'D'
            // ])
            ->update();
        
        // $this->query("UPDATE systems SET SystemType = 'N' WHERE FullName LIKE '%Nurse%'");
    }
}
