<?php

use Phinx\Db\Adapter\MysqlAdapter;

class Initialization extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        if (!$this->hasTable('locations')) {
            $this->table('locations', [
                    'id' => false,
                    'primary_key' => ['LocationId'],
                    'engine' => 'InnoDB',
                    'encoding' => 'latin1',
                    'collation' => 'latin1_swedish_ci',
                    'comment' => '',
                    'row_format' => 'DYNAMIC',
                ])
                ->addColumn('LocationId', 'integer', [
                    'null' => false,
                    'limit' => '10',
                    'signed' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('LocationName', 'string', [
                    'null' => false,
                    'limit' => 40,
                    'collation' => 'latin1_swedish_ci',
                    'encoding' => 'latin1',
                    'after' => 'LocationId',
                ])
                ->addColumn('LocationAddress', 'string', [
                    'null' => false,
                    'limit' => 100,
                    'collation' => 'latin1_swedish_ci',
                    'encoding' => 'latin1',
                    'after' => 'LocationName',
                ])
                ->addColumn('LocationSuburb', 'string', [
                    'null' => false,
                    'limit' => 50,
                    'collation' => 'latin1_swedish_ci',
                    'encoding' => 'latin1',
                    'after' => 'LocationAddress',
                ])
                ->addColumn('LocationState', 'string', [
                    'null' => false,
                    'limit' => 3,
                    'collation' => 'latin1_swedish_ci',
                    'encoding' => 'latin1',
                    'after' => 'LocationSuburb',
                ])
                ->addColumn('LocationPostcode', 'string', [
                    'null' => false,
                    'limit' => 4,
                    'collation' => 'latin1_swedish_ci',
                    'encoding' => 'latin1',
                    'after' => 'LocationState',
                ])
                ->addColumn('LocationPhone', 'string', [
                    'null' => false,
                    'limit' => 20,
                    'collation' => 'latin1_swedish_ci',
                    'encoding' => 'latin1',
                    'after' => 'LocationPostcode',
                ])
                ->addColumn('deleted', 'boolean', [
                    'null' => false,
                    'default' => '0',
                    'limit' => MysqlAdapter::INT_TINY,
                    'after' => 'LocationPhone',
                ])
                ->create();
        }
        
        if (!$this->hasTable('users')) {
            $this->table('users', [
                    'id' => false,
                    'primary_key' => ['UserId'],
                    'engine' => 'InnoDB',
                    'encoding' => 'latin1',
                    'collation' => 'latin1_swedish_ci',
                    'comment' => '',
                    'row_format' => 'DYNAMIC',
                ])
                ->addColumn('UserId', 'integer', [
                    'null' => false,
                    'limit' => '10',
                    'signed' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('Username', 'string', [
                    'null' => false,
                    'limit' => 20,
                    'collation' => 'latin1_swedish_ci',
                    'encoding' => 'latin1',
                    'after' => 'UserId',
                ])
                ->addColumn('Password', 'char', [
                    'null' => false,
                    'limit' => 42,
                    'collation' => 'latin1_swedish_ci',
                    'encoding' => 'latin1',
                    'after' => 'Username',
                ])
                ->addColumn('Firstname', 'string', [
                    'null' => false,
                    'limit' => 50,
                    'collation' => 'latin1_swedish_ci',
                    'encoding' => 'latin1',
                    'after' => 'Password',
                ])
                ->addColumn('Lastname', 'string', [
                    'null' => false,
                    'limit' => 70,
                    'collation' => 'latin1_swedish_ci',
                    'encoding' => 'latin1',
                    'after' => 'Firstname',
                ])
                ->addColumn('Email', 'string', [
                    'null' => false,
                    'limit' => 30,
                    'collation' => 'latin1_swedish_ci',
                    'encoding' => 'latin1',
                    'after' => 'Lastname',
                ])
                ->addColumn('UserType', 'string', [
                    'null' => false,
                    'limit' => 10,
                    'collation' => 'latin1_swedish_ci',
                    'encoding' => 'latin1',
                    'after' => 'Email',
                ])
                ->addColumn('Active', 'string', [
                    'null' => false,
                    'default' => 'N',
                    'limit' => 1,
                    'collation' => 'latin1_swedish_ci',
                    'encoding' => 'latin1',
                    'after' => 'UserType',
                ])
                ->create();
        }

        if (!$this->hasTable('availability')) {
            $this->table('availability', [
                    'id' => false,
                    'primary_key' => ['RuleId'],
                    'engine' => 'InnoDB',
                    'encoding' => 'utf8',
                    'collation' => 'utf8_general_ci',
                    'comment' => '',
                    'row_format' => 'DYNAMIC',
                ])
                ->addColumn('RuleId', 'integer', [
                    'null' => false,
                    'limit' => MysqlAdapter::INT_BIG,
                    'identity' => 'enable',
                ])
                ->addColumn('SystemId', 'integer', [
                    'null' => false,
                    'limit' => '255',
                    'after' => 'RuleId',
                ])
                ->addColumn('SetDate', 'date', [
                    'null' => false,
                    'after' => 'SystemId',
                ])
                ->addColumn('TimeFrom', 'string', [
                    'null' => false,
                    'limit' => 64,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'SetDate',
                ])
                ->addColumn('TimeTo', 'string', [
                    'null' => false,
                    'limit' => 64,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'TimeFrom',
                ])
                ->addColumn('Available', 'integer', [
                    'null' => false,
                    'default' => '1',
                    'limit' => '1',
                    'after' => 'TimeTo',
                ])
                ->create();
        }

        if (!$this->hasTable('bookings')) {
            $this->table('bookings', [
                    'id' => false,
                    'primary_key' => ['BookingId'],
                    'engine' => 'InnoDB',
                    'encoding' => 'utf8',
                    'collation' => 'utf8_general_ci',
                    'comment' => '',
                    'row_format' => 'DYNAMIC',
                ])
                ->addColumn('BookingId', 'integer', [
                    'null' => false,
                    'limit' => '255',
                    'identity' => 'enable',
                ])
                ->addColumn('SystemId', 'integer', [
                    'null' => false,
                    'limit' => '255',
                    'after' => 'BookingId',
                ])
                ->addColumn('CustomerId', 'integer', [
                    'null' => false,
                    'limit' => '255',
                    'after' => 'SystemId',
                ])
                ->addColumn('BookingDate', 'date', [
                    'null' => false,
                    'after' => 'CustomerId',
                ])
                ->addColumn('BookingFrom', 'string', [
                    'null' => false,
                    'limit' => 12,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'BookingDate',
                ])
                ->addColumn('BookingTo', 'string', [
                    'null' => false,
                    'limit' => 12,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'BookingFrom',
                ])
                ->addColumn('BookingCode', 'string', [
                    'null' => false,
                    'limit' => 24,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'BookingTo',
                ])
                ->addColumn('IsCancelled', 'integer', [
                    'null' => false,
                    'limit' => MysqlAdapter::INT_REGULAR,
                    'after' => 'BookingCode',
                ])
                ->addColumn('Attended', 'integer', [
                    'null' => false,
                    'limit' => MysqlAdapter::INT_REGULAR,
                    'after' => 'IsCancelled',
                ])
                ->addColumn('Comments', 'text', [
                    'null' => false,
                    'limit' => 65535,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'Attended',
                ])
                ->addColumn('Message', 'text', [
                    'null' => false,
                    'limit' => 65535,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'Comments',
                ])
                ->create();
        }

        if (!$this->hasTable('customers')) {
            $this->table('customers', [
                    'id' => false,
                    'primary_key' => ['CustomerId'],
                    'engine' => 'InnoDB',
                    'encoding' => 'utf8',
                    'collation' => 'utf8_general_ci',
                    'comment' => '',
                    'row_format' => 'DYNAMIC',
                ])
                ->addColumn('CustomerId', 'integer', [
                    'null' => false,
                    'limit' => '255',
                    'identity' => 'enable',
                ])
                ->addColumn('FullName', 'string', [
                    'null' => false,
                    'limit' => 255,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'CustomerId',
                ])
                ->addColumn('Email', 'string', [
                    'null' => false,
                    'limit' => 255,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'FullName',
                ])
                ->addColumn('PostalAddr', 'string', [
                    'null' => false,
                    'limit' => 255,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'Email',
                ])
                ->addColumn('Phone', 'string', [
                    'null' => false,
                    'limit' => 255,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'PostalAddr',
                ])
                ->addColumn('Comment', 'text', [
                    'null' => false,
                    'limit' => 65535,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'Phone',
                ])
                ->addColumn('RegDate', 'date', [
                    'null' => false,
                    'after' => 'Comment',
                ])
                ->addColumn('Active', 'integer', [
                    'null' => false,
                    'default' => '1',
                    'limit' => '1',
                    'after' => 'RegDate',
                ])
                ->create();
        }

        if (!$this->hasTable('permissions')) {
            $this->table('permissions', [
                    'id' => false,
                    'primary_key' => ['id'],
                    'engine' => 'InnoDB',
                    'encoding' => 'utf8',
                    'collation' => 'utf8_general_ci',
                    'comment' => '',
                    'row_format' => 'DYNAMIC',
                ])
                ->addColumn('id', 'integer', [
                    'null' => false,
                    'limit' => '255',
                    'identity' => 'enable',
                ])
                ->addColumn('role', 'string', [
                    'null' => false,
                    'limit' => 12,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'id',
                ])
                ->addColumn('target', 'string', [
                    'null' => false,
                    'limit' => 64,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'role',
                ])
                ->addColumn('allow', 'string', [
                    'null' => false,
                    'default' => 'Y',
                    'limit' => 1,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'target',
                ])
                ->create();
        }

        if (!$this->hasTable('questions')) {
            $this->table('questions', [
                    'id' => false,
                    'primary_key' => ['QuestionId'],
                    'engine' => 'InnoDB',
                    'encoding' => 'utf8',
                    'collation' => 'utf8_general_ci',
                    'comment' => '',
                    'row_format' => 'DYNAMIC',
                ])
                ->addColumn('QuestionId', 'integer', [
                    'null' => false,
                    'limit' => '255',
                    'identity' => 'enable',
                ])
                ->addColumn('Title', 'string', [
                    'null' => false,
                    'limit' => 255,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'QuestionId',
                ])
                ->addColumn('IsOverride', 'string', [
                    'null' => false,
                    'limit' => 2,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'Title',
                ])
                ->addColumn('IsAttach', 'string', [
                    'null' => false,
                    'limit' => 2,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'IsOverride',
                ])
                ->addColumn('CustomAnswer', 'string', [
                    'null' => false,
                    'limit' => 2,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'IsAttach',
                ])
                ->addColumn('ChooseAnswer', 'blob', [
                    'null' => false,
                    'limit' => MysqlAdapter::BLOB_REGULAR,
                    'after' => 'CustomAnswer',
                ])
                ->create();
        }

        if (!$this->hasTable('services')) {
            $this->table('services', [
                    'id' => false,
                    'primary_key' => ['ServiceId'],
                    'engine' => 'InnoDB',
                    'encoding' => 'utf8',
                    'collation' => 'utf8_general_ci',
                    'comment' => '',
                    'row_format' => 'DYNAMIC',
                ])
                ->addColumn('ServiceId', 'integer', [
                    'null' => false,
                    'limit' => '10',
                    'signed' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('ServiceName', 'string', [
                    'null' => false,
                    'limit' => 32,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'ServiceId',
                ])
                ->addColumn('FullName', 'string', [
                    'null' => false,
                    'limit' => 256,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'ServiceName',
                ])
                ->addColumn('Description', 'string', [
                    'null' => false,
                    'limit' => 255,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'FullName',
                ])
                ->addColumn('Price', 'float', [
                    'null' => false,
                    'after' => 'Description',
                ])
                ->addColumn('Duration', 'string', [
                    'null' => false,
                    'limit' => 20,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'Price',
                ])
                ->addColumn('IsCharge', 'string', [
                    'null' => false,
                    'default' => 'Y',
                    'limit' => 1,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'Duration',
                ])
                ->addColumn('Permission', 'string', [
                    'null' => false,
                    'limit' => 255,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'IsCharge',
                ])
                ->addColumn('active', 'integer', [
                    'null' => false,
                    'default' => '0',
                    'limit' => MysqlAdapter::INT_REGULAR,
                    'after' => 'Permission',
                ])
                ->create();
        }

        if (!$this->hasTable('settings')) {
            $this->table('settings', [
                    'id' => false,
                    'primary_key' => ['id'],
                    'engine' => 'InnoDB',
                    'encoding' => 'utf8',
                    'collation' => 'utf8_general_ci',
                    'comment' => '',
                    'row_format' => 'DYNAMIC',
                ])
                ->addColumn('id', 'integer', [
                    'null' => false,
                    'limit' => '255',
                    'signed' => false,
                    'identity' => 'enable',
                ])
                ->addColumn('name', 'string', [
                    'null' => false,
                    'limit' => 64,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'id',
                ])
                ->addColumn('value', 'text', [
                    'null' => false,
                    'limit' => 65535,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'name',
                ])
                ->addColumn('category', 'string', [
                    'null' => false,
                    'limit' => 32,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'value',
                ])
                ->addColumn('description', 'string', [
                    'null' => false,
                    'limit' => 255,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'category',
                ])
                ->create();
        }

        if (!$this->hasTable('staff')) {
            $this->table('staff', [
                    'id' => false,
                    'primary_key' => ['StaffId'],
                    'engine' => 'InnoDB',
                    'encoding' => 'utf8',
                    'collation' => 'utf8_general_ci',
                    'comment' => '',
                    'row_format' => 'DYNAMIC',
                ])
                ->addColumn('StaffId', 'integer', [
                    'null' => false,
                    'limit' => '255',
                    'identity' => 'enable',
                ])
                ->addColumn('BusinessName', 'string', [
                    'null' => false,
                    'limit' => 255,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'StaffId',
                ])
                ->addColumn('ContactName', 'string', [
                    'null' => false,
                    'limit' => 255,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'BusinessName',
                ])
                ->addColumn('Address', 'string', [
                    'null' => false,
                    'limit' => 255,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'ContactName',
                ])
                ->addColumn('Email', 'string', [
                    'null' => false,
                    'limit' => 255,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'Address',
                ])
                ->addColumn('Phone', 'string', [
                    'null' => false,
                    'limit' => 64,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'Email',
                ])
                ->addColumn('Mobile', 'string', [
                    'null' => false,
                    'limit' => 64,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'Phone',
                ])
                ->addColumn('RegDate', 'datetime', [
                    'null' => false,
                    'after' => 'Mobile',
                ])
                ->create();
        }

        if (!$this->hasTable('sub_admins')) {
            $this->table('sub_admins', [
                    'id' => false,
                    'primary_key' => ['id'],
                    'engine' => 'InnoDB',
                    'encoding' => 'utf8',
                    'collation' => 'utf8_general_ci',
                    'comment' => '',
                    'row_format' => 'DYNAMIC',
                ])
                ->addColumn('id', 'integer', [
                    'null' => false,
                    'limit' => '255',
                    'identity' => 'enable',
                ])
                ->addColumn('UserId', 'integer', [
                    'null' => false,
                    'limit' => '255',
                    'after' => 'id',
                ])
                ->addColumn('Position', 'string', [
                    'null' => false,
                    'limit' => 64,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'UserId',
                ])
                ->addColumn('StreetAddr', 'string', [
                    'null' => false,
                    'limit' => 255,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'Position',
                ])
                ->addColumn('City', 'string', [
                    'null' => false,
                    'limit' => 255,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'StreetAddr',
                ])
                ->addColumn('State', 'string', [
                    'null' => false,
                    'limit' => 64,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'City',
                ])
                ->addColumn('Zip', 'string', [
                    'null' => false,
                    'limit' => 24,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'State',
                ])
                ->addColumn('Country', 'string', [
                    'null' => false,
                    'limit' => 64,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'Zip',
                ])
                ->addColumn('ContactPhone', 'string', [
                    'null' => false,
                    'limit' => 64,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'Country',
                ])
                ->addColumn('MobilePhone', 'string', [
                    'null' => false,
                    'limit' => 64,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'ContactPhone',
                ])
                ->addColumn('Fax', 'string', [
                    'null' => false,
                    'limit' => 20,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'MobilePhone',
                ])
                ->addColumn('RegDate', 'date', [
                    'null' => false,
                    'after' => 'Fax',
                ])
                ->create();
        }

        if (!$this->hasTable('systems')) {
            $this->table('systems', [
                    'id' => false,
                    'primary_key' => ['SystemId'],
                    'engine' => 'InnoDB',
                    'encoding' => 'utf8',
                    'collation' => 'utf8_general_ci',
                    'comment' => '',
                    'row_format' => 'DYNAMIC',
                ])
                ->addColumn('SystemId', 'integer', [
                    'null' => false,
                    'limit' => '255',
                    'identity' => 'enable',
                ])
                ->addColumn('UserId', 'integer', [
                    'null' => false,
                    'limit' => '255',
                    'after' => 'SystemId',
                ])
                ->addColumn('LocationId', 'integer', [
                    'null' => false,
                    'limit' => '64',
                    'after' => 'UserId',
                ])
                ->addColumn('FullName', 'string', [
                    'null' => false,
                    'limit' => 255,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'LocationId',
                ])
                ->addColumn('ReferenceId', 'string', [
                    'null' => false,
                    'limit' => 64,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'FullName',
                ])
                ->addColumn('Access', 'string', [
                    'null' => false,
                    'default' => 'Public',
                    'limit' => 64,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'ReferenceId',
                ])
                ->addColumn('InternalId', 'string', [
                    'null' => true,
                    'default' => null,
                    'limit' => 64,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'Access',
                ])
                ->addColumn('BusinessName', 'string', [
                    'null' => false,
                    'limit' => 255,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'InternalId',
                ])
                ->addColumn('Street', 'string', [
                    'null' => false,
                    'limit' => 255,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'BusinessName',
                ])
                ->addColumn('City', 'string', [
                    'null' => false,
                    'limit' => 255,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'Street',
                ])
                ->addColumn('State', 'string', [
                    'null' => false,
                    'limit' => 64,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'City',
                ])
                ->addColumn('PostCode', 'string', [
                    'null' => false,
                    'limit' => 24,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'State',
                ])
                ->addColumn('Country', 'string', [
                    'null' => false,
                    'limit' => 64,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'PostCode',
                ])
                ->addColumn('PStreet', 'string', [
                    'null' => false,
                    'limit' => 255,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'Country',
                ])
                ->addColumn('PCity', 'string', [
                    'null' => false,
                    'limit' => 255,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'PStreet',
                ])
                ->addColumn('PState', 'string', [
                    'null' => false,
                    'limit' => 64,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'PCity',
                ])
                ->addColumn('PPostCode', 'string', [
                    'null' => false,
                    'limit' => 24,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'PState',
                ])
                ->addColumn('Timezone', 'string', [
                    'null' => false,
                    'limit' => 255,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'PPostCode',
                ])
                ->addColumn('Latitude', 'decimal', [
                    'null' => false,
                    'default' => '0.00000000',
                    'precision' => 10,
                    'scale' => 8,
                    'after' => 'Timezone',
                ])
                ->addColumn('Longitude', 'decimal', [
                    'null' => false,
                    'default' => '0.00000000',
                    'precision' => 11,
                    'scale' => 8,
                    'after' => 'Latitude',
                ])
                ->addColumn('SecondEmail', 'string', [
                    'null' => false,
                    'limit' => 64,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'Longitude',
                ])
                ->addColumn('ThirdEmail', 'string', [
                    'null' => false,
                    'limit' => 64,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'SecondEmail',
                ])
                ->addColumn('Phone', 'string', [
                    'null' => false,
                    'limit' => 64,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'ThirdEmail',
                ])
                ->addColumn('Mobile', 'string', [
                    'null' => false,
                    'limit' => 64,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'Phone',
                ])
                ->addColumn('Fax', 'string', [
                    'null' => false,
                    'limit' => 20,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'Mobile',
                ])
                ->addColumn('Website', 'string', [
                    'null' => true,
                    'default' => null,
                    'limit' => 255,
                    'collation' => 'utf8_general_ci',
                    'encoding' => 'utf8',
                    'after' => 'Fax',
                ])
                ->addColumn('LastAccess', 'datetime', [
                    'null' => false,
                    'default' => 'CURRENT_TIMESTAMP',
                    'after' => 'Website',
                ])
                ->addColumn('RegDate', 'datetime', [
                    'null' => false,
                    'after' => 'LastAccess',
                ])
                ->create();
        }

        if (!$this->hasTable('system_services')) {
            $this->table('system_services', [
                    'id' => false,
                    'primary_key' => ['Id'],
                    'engine' => 'InnoDB',
                    'encoding' => 'utf8',
                    'collation' => 'utf8_general_ci',
                    'comment' => 'System-Service Relation',
                    'row_format' => 'DYNAMIC',
                ])
                ->addColumn('Id', 'integer', [
                    'null' => false,
                    'limit' => MysqlAdapter::INT_REGULAR,
                    'identity' => 'enable',
                ])
                ->addColumn('SystemId', 'integer', [
                    'null' => false,
                    'limit' => '255',
                    'after' => 'Id',
                ])
                ->addColumn('ServiceId', 'integer', [
                    'null' => false,
                    'limit' => '255',
                    'after' => 'SystemId',
                ])
                ->create();
        }
    }
}
