<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateTicketFollowers extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('ticket_followers', ['id' => false, 'primary_key' => ['ticket_id', 'user_id']]);

        $table
            ->addColumn('ticket_id', 'integer', [
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'null' => true,
            ])
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
