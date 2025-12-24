<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateOrganizations extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('organizations', ['signed' => false]);

        $table
            ->addColumn('name', 'string', [
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('domain', 'string', [
                'limit' => 255,
                'null' => true,
                'comment' => 'Email domain for auto-assignment',
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'null' => true,
            ])
            ->addIndex(['name'])
            ->create();
    }
}
