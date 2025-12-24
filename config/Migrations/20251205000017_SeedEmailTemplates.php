<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class SeedEmailTemplates extends AbstractMigration
{
    public function up(): void
    {
        $data = [
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
                    'system_title'
                ]),
                'is_active' => true,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s'),
            ],
            [
                'template_key' => 'ticket_estado',
                'subject' => '[Ticket #{{ticket_number}}] Cambio de estado',
                'body_html' => null,
                'available_variables' => json_encode([
                    'ticket_number',
                    'subject',
                    'requester_name',
                    'old_status',
                    'new_status',
                    'assignee_name',
                    'updated_date',
                    'ticket_url',
                    'system_title'
                ]),
                'is_active' => true,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s'),
            ],
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
                    'system_title'
                ]),
                'is_active' => true,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s'),
            ],
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
                    'status_change_section',
                    'old_status',
                    'old_status_key',
                    'new_status',
                    'new_status_key',
                    'assignee_name',
                    'updated_date',
                    'ticket_url',
                    'system_title',
                    'agent_profile_image_url',
                    'agent_name'
                ]),
                'is_active' => true,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s'),
            ],
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
                    'system_title'
                ]),
                'is_active' => true,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s'),
            ],
            [
                'template_key' => 'pqrs_estado',
                'subject' => '[PQRS #{{pqrs_number}}] Cambio de estado',
                'body_html' => null,
                'available_variables' => json_encode([
                    'pqrs_number',
                    'pqrs_type',
                    'subject',
                    'requester_name',
                    'old_status',
                    'new_status',
                    'assignee_info',
                    'system_title'
                ]),
                'is_active' => true,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s'),
            ],
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
                    'system_title',
                    'agent_profile_image_url',
                    'agent_name'
                ]),
                'is_active' => true,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s'),
            ],
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
                    'status_change_section',
                    'old_status',
                    'old_status_key',
                    'new_status',
                    'new_status_key',
                    'assignee_name',
                    'updated_date',
                    'system_title',
                    'agent_profile_image_url',
                    'agent_name'
                ]),
                'is_active' => true,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s'),
            ],
        ];

        $table = $this->table('email_templates');
        $table->insert($data)->save();
    }

    public function down(): void
    {
        $this->execute('DELETE FROM email_templates');
    }
}
