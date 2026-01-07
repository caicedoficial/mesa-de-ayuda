<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * SeedEmailTemplates Migration
 *
 * Seeds email templates for the Tickets module.
 * Templates support variable substitution using {{variable_name}} syntax.
 *
 * Ticket Templates:
 * - nuevo_ticket: New ticket creation notification
 * - ticket_estado: Ticket status change notification
 * - nuevo_comentario: New comment notification
 * - ticket_respuesta: Agent response notification
 *
 * Note: body_html is NULL for all templates. The actual HTML content
 * is rendered dynamically by the EmailService using Twig templates
 * located in templates/email/.
 *
 * @version 1.1.0 - Variables optimizadas (2026-01-05)
 */
class SeedEmailTemplates extends AbstractMigration
{
    /**
     * Seed email templates for Tickets module
     *
     * @return void
     */
    public function up(): void
    {
        $timestamp = date('Y-m-d H:i:s');

        $data = [
            // Ticket Created
            [
                'template_key' => 'nuevo_ticket',
                'subject' => '[Ticket #{{ticket_number}}] {{subject}}',
                'body_html' => null,
                'available_variables' => json_encode([
                    'ticket_number',
                    'subject',
                    'requester_name',
                    'created_date',
                    'ticket_url',
                    'system_title',
                ]),
                'is_active' => true,
                'created' => $timestamp,
                'modified' => $timestamp,
            ],

            // Ticket Status Changed (OPTIMIZADO)
            [
                'template_key' => 'ticket_estado',
                'subject' => '[Ticket #{{ticket_number}}] Cambio de estado',
                'body_html' => null,
                'available_variables' => json_encode([
                    'ticket_number',
                    'subject',
                    'requester_name',
                    'status_change_section',  // HTML renderizado completo (nuevo + viejo estado + assignee + fecha)
                    'ticket_url',
                    'system_title',
                ]),
                'is_active' => true,
                'created' => $timestamp,
                'modified' => $timestamp,
            ],

            // New Comment (Internal)
            [
                'template_key' => 'nuevo_comentario',
                'subject' => '[Ticket #{{ticket_number}}] Nuevo comentario',
                'body_html' => null,
                'available_variables' => json_encode([
                    'ticket_number',
                    'subject',
                    'comment_author',
                    'comment_body',
                    'attachments_list',
                    'ticket_url',
                    'agent_profile_image_url',
                    'agent_name',
                    'system_title',
                ]),
                'is_active' => true,
                'created' => $timestamp,
                'modified' => $timestamp,
            ],

            // Agent Response (OPTIMIZADO)
            [
                'template_key' => 'ticket_respuesta',
                'subject' => '[Ticket #{{ticket_number}}] Respuesta del agente',
                'body_html' => null,
                'available_variables' => json_encode([
                    'ticket_number',
                    'subject',
                    'requester_name',
                    'comment_author',
                    'comment_body',
                    'attachments_list',
                    'status_change_section',  // HTML renderizado completo (vacío si no hubo cambio)
                    'ticket_url',
                    'agent_profile_image_url',
                    'agent_name',
                    'system_title',
                ]),
                'is_active' => true,
                'created' => $timestamp,
                'modified' => $timestamp,
            ],
        ];

        $table = $this->table('email_templates');
        $table->insert($data)->save();
    }

    /**
     * Remove seeded email templates
     *
     * @return void
     */
    public function down(): void
    {
        $this->execute("DELETE FROM email_templates WHERE template_key IN ('nuevo_ticket', 'ticket_estado', 'nuevo_comentario', 'ticket_respuesta')");
    }
}
