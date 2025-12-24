<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateCompras extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('compras', ['signed' => false]);

        $table
            // Identificadores
            ->addColumn('compra_number', 'string', [
                'limit' => 20,
                'null' => false,
                'comment' => 'Format: CPR-2025-00001',
            ])
            ->addColumn('original_ticket_number', 'string', [
                'limit' => 20,
                'null' => true,
                'comment' => 'Reference to original ticket',
            ])

            // Datos principales
            ->addColumn('subject', 'string', [
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'null' => false,
            ])

            // Estado y prioridad
            ->addColumn('status', 'string', [
                'limit' => 20,
                'default' => 'nuevo',
                'null' => false,
            ])
            ->addColumn('priority', 'string', [
                'limit' => 20,
                'default' => 'media',
                'null' => false,
            ])

            // Usuarios (relaciones con users)
            ->addColumn('requester_id', 'integer', [
                'null' => false,
                'signed' => false,
                'comment' => 'Usuario que solicita la compra',
            ])
            ->addColumn('assignee_id', 'integer', [
                'null' => true,
                'signed' => false,
                'comment' => 'Usuario de compras asignado',
            ])

            // SLA (crítico)
            ->addColumn('sla_due_date', 'datetime', [
                'default' => null,
                'null' => true,
                'comment' => 'Fecha límite SLA (created + 3 días)',
            ])

            // Timestamps
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
                'comment' => 'Cuando se completó/rechazó',
            ])
            ->addColumn('first_response_at', 'datetime', [
                'default' => null,
                'null' => true,
                'comment' => 'Primera respuesta del equipo de compras',
            ])

            // Índices para optimización de consultas
            ->addIndex(['compra_number'], ['unique' => true])
            ->addIndex(['original_ticket_number'])
            ->addIndex(['priority'])
            ->addIndex(['assignee_id'])
            ->addIndex(['requester_id'])
            ->addIndex(['status', 'created'])
            ->addIndex(['assignee_id', 'status'])
            ->addIndex(['sla_due_date'])

            // Foreign keys
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
