<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreatePqrsHistory extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('pqrs_history', ['signed' => false]);

        $table
            ->addColumn('pqrs_id', 'integer', [
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('field_name', 'string', [
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('old_value', 'text', [
                'null' => true,
            ])
            ->addColumn('new_value', 'text', [
                'null' => true,
            ])
            ->addColumn('changed_by', 'integer', [
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('description', 'string', [
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'null' => true,
            ])
            ->addIndex(['pqrs_id'])
            ->addIndex(['created'])
            ->addForeignKey('pqrs_id', 'pqrs', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->addForeignKey('changed_by', 'users', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->create();
    }
}
