<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreatePqrs extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('pqrs', ['signed' => false]);

        $table
            ->addColumn('pqrs_number', 'string', [
                'limit' => 20,
                'null' => false,
                'comment' => 'Format: PQRS-2025-00001',
            ])
            ->addColumn('type', 'enum', [
                'values' => ['peticion', 'queja', 'reclamo', 'sugerencia'],
                'null' => false,
            ])
            ->addColumn('subject', 'string', [
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'null' => false,
            ])
            ->addColumn('status', 'enum', [
                'values' => ['nuevo', 'en_revision', 'en_proceso', 'resuelto', 'cerrado'],
                'default' => 'nuevo',
                'null' => false,
            ])
            ->addColumn('priority', 'enum', [
                'values' => ['baja', 'media', 'alta', 'urgente'],
                'default' => 'media',
                'null' => false,
            ])
            ->addColumn('requester_name', 'string', [
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('requester_email', 'string', [
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('requester_phone', 'string', [
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('assignee_id', 'integer', [
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('ip_address', 'string', [
                'limit' => 45,
                'null' => true,
            ])
            ->addColumn('user_agent', 'text', [
                'null' => true,
            ])
            ->addColumn('source_url', 'string', [
                'limit' => 500,
                'null' => true,
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
            ->addIndex(['pqrs_number'], ['unique' => true])
            ->addIndex(['priority'])
            ->addIndex(['assignee_id'])
            ->addIndex(['status', 'created'])
            ->addIndex(['type', 'status'])
            ->addIndex(['assignee_id', 'status'])
            ->addForeignKey('assignee_id', 'users', 'id', [
                'delete' => 'SET_NULL',
                'update' => 'CASCADE',
            ])
            ->create();
    }
}
