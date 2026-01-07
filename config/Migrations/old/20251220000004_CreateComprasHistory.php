<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateComprasHistory extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('compras_history', ['signed' => false]);

        $table
            ->addColumn('compra_id', 'integer', [
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('changed_by', 'integer', [
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('field_name', 'string', [
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('old_value', 'string', [
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('new_value', 'string', [
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('description', 'text', [
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'null' => true,
            ])

            ->addIndex(['compra_id'])

            ->addForeignKey('compra_id', 'compras', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->addForeignKey('changed_by', 'users', 'id', [
                'delete' => 'SET_NULL',
                'update' => 'CASCADE',
            ])
            ->create();
    }
}
