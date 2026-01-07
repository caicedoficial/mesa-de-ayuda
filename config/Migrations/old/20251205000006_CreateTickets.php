<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateTickets extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('tickets', ['signed' => false]);

        $table
            ->addColumn('ticket_number', 'string', [
                'limit' => 20,
                'null' => false,
                'comment' => 'Format: TKT-2025-00001',
            ])
            ->addColumn('gmail_message_id', 'string', [
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('gmail_thread_id', 'string', [
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('email_to', 'text', [
                'null' => true,
                'comment' => 'JSON array of To recipients',
            ])
            ->addColumn('email_cc', 'text', [
                'null' => true,
                'comment' => 'JSON array of CC recipients',
            ])
            ->addColumn('subject', 'string', [
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'null' => false,
            ])
            ->addColumn('status', 'enum', [
                'values' => ['nuevo', 'abierto', 'pendiente', 'resuelto'],
                'default' => 'nuevo',
                'null' => false,
            ])
            ->addColumn('priority', 'enum', [
                'values' => ['baja', 'media', 'alta', 'urgente'],
                'default' => 'media',
                'null' => false,
            ])
            ->addColumn('requester_id', 'integer', [
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('assignee_id', 'integer', [
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('resolved_at', 'datetime', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('first_response_at', 'datetime', [
                'default' => null,
                'null' => true,
            ])
            ->addIndex(['ticket_number'], ['unique' => true])
            ->addIndex(['gmail_message_id'], ['unique' => true])
            ->addIndex(['priority'])
            ->addIndex(['assignee_id'])
            ->addIndex(['requester_id'])
            ->addIndex(['created'])
            ->addIndex(['status', 'priority'])
            ->addIndex(['assignee_id', 'status'])
            ->addIndex(['status', 'created'])
            ->addForeignKey('requester_id', 'users', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->addForeignKey('assignee_id', 'users', 'id', [
                'delete' => 'SET_NULL',
                'update' => 'CASCADE',
            ])
            ->create();
    }
}
