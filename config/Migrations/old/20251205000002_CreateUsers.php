<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateUsers extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('users', ['signed' => false]);

        $table
            ->addColumn('email', 'string', [
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('password', 'string', [
                'limit' => 255,
                'null' => true,
                'comment' => 'NULL for auto-created users from emails',
            ])
            ->addColumn('first_name', 'string', [
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('last_name', 'string', [
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('role', 'enum', [
                'values' => ['admin', 'agent', 'compras', 'servicio_cliente', 'requester'],
                'default' => 'requester',
                'null' => false,
            ])
            ->addColumn('organization_id', 'integer', [
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('profile_image', 'string', [
                'limit' => 255,
                'null' => true,
                'comment' => 'Path to profile photo',
            ])
            ->addColumn('is_active', 'boolean', [
                'default' => true,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'null' => true,
            ])
            ->addIndex(['email'], ['unique' => true])
            ->addIndex(['organization_id'])
            ->addForeignKey('organization_id', 'organizations', 'id', [
                'delete' => 'SET_NULL',
                'update' => 'CASCADE',
            ])
            ->create();
    }
}
