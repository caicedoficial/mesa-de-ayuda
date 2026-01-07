<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreatePqrsComments extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('pqrs_comments', ['signed' => false]);

        $table
            ->addColumn('pqrs_id', 'integer', [
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('body', 'text', [
                'null' => false,
            ])
            ->addColumn('comment_type', 'enum', [
                'values' => ['public', 'internal'],
                'default' => 'public',
                'null' => false,
            ])
            ->addColumn('is_system_comment', 'boolean', [
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
            ->addIndex(['pqrs_id'])
            ->addIndex(['created'])
            ->addForeignKey('pqrs_id', 'pqrs', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->addForeignKey('user_id', 'users', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->create();
    }
}
