<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Seed Compras Email Templates Migration
 *
 * Inserts 3 email templates for the Compras (Purchase Orders) system:
 * 1. nueva_compra - New purchase order notification
 * 2. compra_estado - Purchase order status change notification
 * 3. compra_respuesta - Unified response notification (comment + status)
 *
 * Templates are created with empty body_html for manual completion.
 */
class SeedComprasEmailTemplates extends BaseMigration
{
    /**
     * Up Method.
     *
     * @return void
     */
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');

        $this->table('email_templates')
            ->insert([
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
                        'system_title'
                    ]),
                    'is_active' => true,
                    'created' => $now,
                    'modified' => $now,
                ],
                [
                    'template_key' => 'compra_estado',
                    'subject' => '[Compra #{{compra_number}}] Cambio de estado',
                    'body_html' => null,
                    'available_variables' => json_encode([
                        'compra_number',
                        'subject',
                        'requester_name',
                        'old_status',
                        'new_status',
                        'assignee_name',
                        'updated_date',
                        'compra_url',
                        'system_title'
                    ]),
                    'is_active' => true,
                    'created' => $now,
                    'modified' => $now,
                ],
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
                        'status_change_section',
                        'old_status',
                        'old_status_key',
                        'new_status',
                        'new_status_key',
                        'assignee_name',
                        'updated_date',
                        'compra_url',
                        'system_title',
                        'agent_profile_image_url',
                        'agent_name'
                    ]),
                    'is_active' => true,
                    'created' => $now,
                    'modified' => $now,
                ],
            ])
            ->save();
    }

    /**
     * Down Method.
     *
     * @return void
     */
    public function down(): void
    {
        $this->execute("DELETE FROM email_templates WHERE template_key IN ('nueva_compra', 'compra_estado', 'compra_respuesta')");
    }
}
