<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateTicketsTags extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('tickets_tags', ['id' => false, 'primary_key' => ['ticket_id', 'tag_id']]);

        $table
            ->addColumn('ticket_id', 'integer', [
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('tag_id', 'integer', [
                'null' => false,
                'signed' => false,
            ])
            ->addForeignKey('ticket_id', 'tickets', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->addForeignKey('tag_id', 'tags', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->create();
    }
}
