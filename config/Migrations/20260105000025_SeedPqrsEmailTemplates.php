<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * SeedPqrsEmailTemplates Migration
 *
 * Seeds email templates for the PQRS (Peticiones, Quejas, Reclamos y Sugerencias) module.
 * Templates support variable substitution using {{variable_name}} syntax.
 *
 * PQRS Templates:
 * - nuevo_pqrs: New PQRS submission confirmation
 * - pqrs_estado: PQRS status change notification
 * - pqrs_comentario: New comment notification
 * - pqrs_respuesta: Agent response notification
 *
 * Note: body_html is NULL for all templates. The actual HTML content
 * is rendered dynamically by the EmailService using Twig templates
 * located in templates/email/.
 *
 * @version 1.1.0 - Variables optimizadas (2026-01-05)
 */
class SeedPqrsEmailTemplates extends AbstractMigration
{
    /**
     * Seed email templates for PQRS module
     *
     * @return void
     */
    public function up(): void
    {
        $timestamp = date('Y-m-d H:i:s');

        $data = [
            // New PQRS Created
            [
                'template_key' => 'nuevo_pqrs',
                'subject' => '[PQRS #{{pqrs_number}}] {{subject}}',
                'body_html' => null,
                'available_variables' => json_encode([
                    'pqrs_number',
                    'pqrs_type',
                    'subject',
                    'requester_name',
                    'created_date',
                    'system_title',
                ]),
                'is_active' => true,
                'created' => $timestamp,
                'modified' => $timestamp,
            ],

            // PQRS Status Changed (OPTIMIZADO)
            [
                'template_key' => 'pqrs_estado',
                'subject' => '[PQRS #{{pqrs_number}}] Cambio de estado',
                'body_html' => null,
                'available_variables' => json_encode([
                    'pqrs_number',
                    'pqrs_type',
                    'subject',
                    'requester_name',
                    'status_change_section',  // HTML renderizado completo (nuevo + viejo estado + assignee + fecha)
                    'system_title',
                ]),
                'is_active' => true,
                'created' => $timestamp,
                'modified' => $timestamp,
            ],

            // New Comment on PQRS
            [
                'template_key' => 'pqrs_comentario',
                'subject' => '[PQRS #{{pqrs_number}}] Nuevo comentario',
                'body_html' => null,
                'available_variables' => json_encode([
                    'pqrs_number',
                    'subject',
                    'comment_author',
                    'comment_body',
                    'attachments_list',
                    'agent_profile_image_url',
                    'agent_name',
                    'system_title',
                ]),
                'is_active' => true,
                'created' => $timestamp,
                'modified' => $timestamp,
            ],

            // Agent Response (Comment + Status Change) (OPTIMIZADO)
            [
                'template_key' => 'pqrs_respuesta',
                'subject' => '[PQRS #{{pqrs_number}}] Respuesta del equipo',
                'body_html' => null,
                'available_variables' => json_encode([
                    'pqrs_number',
                    'pqrs_type',
                    'subject',
                    'requester_name',
                    'comment_author',
                    'comment_body',
                    'attachments_list',
                    'status_change_section',  // HTML renderizado completo (vacío si no hubo cambio)
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
        $this->execute("DELETE FROM email_templates WHERE template_key IN ('nuevo_pqrs', 'pqrs_estado', 'pqrs_comentario', 'pqrs_respuesta')");
    }
}
