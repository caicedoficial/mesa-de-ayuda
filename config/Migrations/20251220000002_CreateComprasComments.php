<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateComprasComments extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('compras_comments', ['signed' => false]);

        $table
            ->addColumn('compra_id', 'integer', [
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('comment_type', 'string', [
                'limit' => 20,
                'default' => 'public',
                'null' => false,
            ])
            ->addColumn('body', 'text', [
                'null' => false,
            ])
            ->addColumn('is_system_comment', 'boolean', [
                'default' => false,
                'null' => false,
            ])
            ->addColumn('sent_as_email', 'boolean', [
                'default' => false,
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

            ->addIndex(['compra_id'])
            ->addIndex(['compra_id', 'created'])
            ->addIndex(['comment_type'])

            ->addForeignKey('compra_id', 'compras', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->addForeignKey('user_id', 'users', 'id', [
                'delete' => 'SET_NULL',
                'update' => 'CASCADE',
            ])
            ->create();
    }
}
