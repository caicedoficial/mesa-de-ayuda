<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * SeedComprasEmailTemplates Migration
 *
 * Seeds email templates for the Compras (Purchase Requests) module.
 * Templates support variable substitution using {{variable_name}} syntax.
 *
 * Compras Templates:
 * - nueva_compra: New purchase request creation notification
 * - compra_estado: Purchase request status change notification
 * - compra_comentario: New comment notification
 * - compra_respuesta: Agent response notification (comment + status change)
 *
 * Note: body_html is NULL for all templates. The actual HTML content
 * is rendered dynamically by the EmailService using Twig templates
 * located in templates/email/.
 *
 * @version 1.1.0 - Variables optimizadas (2026-01-05)
 */
class SeedComprasEmailTemplates extends AbstractMigration
{
    /**
     * Seed email templates for Compras module
     *
     * @return void
     */
    public function up(): void
    {
        $timestamp = date('Y-m-d H:i:s');

        $data = [
            // New Purchase Request Created
            [
                'template_key' => 'nueva_compra',
                'subject' => '[Compra #{{compra_number}}] {{subject}}',
                'body_html' => null,
                'available_variables' => json_encode([
                    'compra_number',
                    'subject',
                    'requester_name',
                    'assignee_name',
                    'priority',
                    'sla_due_date',
                    'created_date',
                    'compra_url',
                    'system_title',
                ]),
                'is_active' => true,
                'created' => $timestamp,
                'modified' => $timestamp,
            ],

            // Purchase Request Status Changed (OPTIMIZADO)
            [
                'template_key' => 'compra_estado',
                'subject' => '[Compra #{{compra_number}}] Cambio de estado',
                'body_html' => null,
                'available_variables' => json_encode([
                    'compra_number',
                    'subject',
                    'requester_name',
                    'status_change_section',  // HTML renderizado completo (nuevo + viejo estado + assignee + fecha)
                    'compra_url',
                    'system_title',
                ]),
                'is_active' => true,
                'created' => $timestamp,
                'modified' => $timestamp,
            ],

            // New Comment on Purchase Request
            [
                'template_key' => 'compra_comentario',
                'subject' => '[Compra #{{compra_number}}] Nuevo comentario',
                'body_html' => null,
                'available_variables' => json_encode([
                    'compra_number',
                    'subject',
                    'requester_name',
                    'comment_author',
                    'comment_body',
                    'attachments_list',
                    'compra_url',
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
                'template_key' => 'compra_respuesta',
                'subject' => '[Compra #{{compra_number}}] Respuesta del agente',
                'body_html' => null,
                'available_variables' => json_encode([
                    'compra_number',
                    'subject',
                    'requester_name',
                    'comment_author',
                    'comment_body',
                    'attachments_list',
                    'status_change_section',  // HTML renderizado completo (vacío si no hubo cambio)
                    'compra_url',
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
        $this->execute("DELETE FROM email_templates WHERE template_key IN ('nueva_compra', 'compra_estado', 'compra_comentario', 'compra_respuesta')");
    }
}
