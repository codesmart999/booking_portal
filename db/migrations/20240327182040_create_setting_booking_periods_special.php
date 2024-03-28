<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class CreateSettingBookingPeriodsSpecial extends AbstractMigration
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
        $this->table('setting_bookingperiods_special', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => 'This is a table of available/unavailable booking periods with systemIDs',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_BIG,
                'identity' => 'enable',
            ])
            ->addColumn('SystemId', 'integer', [
                'null' => false,
                'default' => '0'
            ])
            ->addColumn('SetDate', 'date', [
                'null' => false,
                'default' => '1970-01-01'
            ])
            ->addColumn('FromInMinutes', 'integer', [
                'null' => false,
                'default' => '0'
            ])
            ->addColumn('ToInMinutes', 'integer', [
                'null' => false,
                'default' => '0'
            ])
            ->addColumn('isAvailable', 'boolean', [
                'null' => false,
                'default' => '1',
                'comment' => '1 means Available, 0 is means Unavailable',
                'limit' => MysqlAdapter::INT_TINY
            ])
            ->addColumn('createdAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updatedAt', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->create();
    }
}
