<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateTicketComments extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('ticket_comments', ['signed' => false]);

        $table
            ->addColumn('ticket_id', 'integer', [
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
            ->addIndex(['ticket_id'])
            ->addIndex(['created'])
            ->addIndex(['comment_type'])
            ->addIndex(['ticket_id', 'created'])
            ->addForeignKey('ticket_id', 'tickets', 'id', [
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
