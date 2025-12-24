<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateSystemSettings extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('system_settings', ['signed' => false]);

        $table
            ->addColumn('setting_key', 'string', [
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('setting_value', 'text', [
                'null' => true,
            ])
            ->addColumn('setting_type', 'string', [
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('description', 'string', [
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'null' => true,
            ])
            ->addIndex(['setting_key'], ['unique' => true])
            ->create();
    }
}
