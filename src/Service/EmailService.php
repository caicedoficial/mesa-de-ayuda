<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Console\Helper as ConsoleHelper;
use Cake\Mailer\Mailer;
use Cake\Mailer\TransportFactory;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Log\Log;
use Cake\Core\Configure;

/**
 * Email Service
 *
 * Handles all email notifications using templates from database:
 * - New ticket notifications
 * - Status change notifications
 * - New comment notifications
 */
class EmailService
{
    use LocatorAwareTrait;

    /**
     * Send new ticket notification to requester
     *
     * @param \App\Model\Entity\Ticket $ticket Ticket entity
     * @return bool Success status
     */
    public function sendNewTicketNotification($ticket): bool
    {
        try {
            // Load ticket with requester
            $ticketsTable = $this->fetchTable('Tickets');
            $ticket = $ticketsTable->get($ticket->id, contain: ['Requesters']);

            // Get email template
            $template = $this->getTemplate('nuevo_ticket');

            if (!$template) {
                Log::error('Email template not found: nuevo_ticket');
                return false;
            }

            // Replace variables in subject and body
            $variables = [
                'ticket_number' => $ticket->ticket_number,
                'subject' => $ticket->subject,
                'requester_name' => $ticket->requester->name,
                'created_date' => $this->formatDate($ticket->created),
                'ticket_url' => $this->getTicketUrl($ticket->id),
            ];

            $subject = $this->replaceVariables($template->subject, $variables);
            $body = $this->replaceVariables($template->body_html, $variables);

            // Send email
            return $this->sendEmail($ticket->requester->email, $subject, $body);
        } catch (\Exception $e) {
            Log::error('Failed to send new ticket notification', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send status change notification
     *
     * @param \App\Model\Entity\Ticket $ticket Ticket entity
     * @param string $oldStatus Old status
     * @param string $newStatus New status
     * @return bool Success status
     */
    public function sendStatusChangeNotification($ticket, string $oldStatus, string $newStatus): bool
    {
        try {
            // Load ticket with requester and assignee
            $ticketsTable = $this->fetchTable('Tickets');
            $ticket = $ticketsTable->get($ticket->id, contain: ['Requesters', 'Assignees']);

            // Determine template based on status
            $templateKey = match ($newStatus) {
                'abierto' => 'ticket_abierto',
                'resuelto' => 'ticket_resuelto',
                default => null,
            };

            if (!$templateKey) {
                // No specific template for this status change
                return true;
            }

            $template = $this->getTemplate($templateKey);

            if (!$template) {
                Log::error("Email template not found: {$templateKey}");
                return false;
            }

            // Replace variables
            $variables = [
                'ticket_number' => $ticket->ticket_number,
                'subject' => $ticket->subject,
                'requester_name' => $ticket->requester->name,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'assignee_name' => $ticket->assignee ? $ticket->assignee->name : 'No asignado',
                'updated_date' => $this->formatDate($ticket->modified),
                'ticket_url' => $this->getTicketUrl($ticket->id),
            ];

            $subject = $this->replaceVariables($template->subject, $variables);
            $body = $this->replaceVariables($template->body_html, $variables);

            // Send email to requester
            return $this->sendEmail($ticket->requester->email, $subject, $body);
        } catch (\Exception $e) {
            Log::error('Failed to send status change notification', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send new comment notification
     *
     * @param \App\Model\Entity\Ticket $ticket Ticket entity
     * @param \App\Model\Entity\TicketComment $comment Comment entity
     * @return bool Success status
     */
    public function sendNewCommentNotification($ticket, $comment): bool
    {
        try {
            // Load entities with associations
            $ticketsTable = $this->fetchTable('Tickets');
            $ticket = $ticketsTable->get($ticket->id, contain: ['Requesters', 'Assignees', 'Attachments']);

            $commentsTable = $this->fetchTable('TicketComments');
            $comment = $commentsTable->get($comment->id, contain: ['Users']);

            // Get comment attachments (non-inline only)
            $commentAttachments = [];
            if (!empty($ticket->attachments)) {
                foreach ($ticket->attachments as $attachment) {
                    if ($attachment->comment_id === $comment->id && !$attachment->is_inline) {
                        $commentAttachments[] = $attachment;
                    }
                }
            }

            // Add attachment list to email body if there are attachments
            $attachmentsList = '';
            if (!empty($commentAttachments)) {
                $attachmentsList = "<p><strong>Adjuntos:</strong></p><ul>";
                foreach ($commentAttachments as $attachment) {
                    $sizeKB = number_format($attachment->file_size / 1024, 1);
                    $attachmentsList .= "<li>{$attachment->original_filename} ({$sizeKB} KB)</li>";
                }
                $attachmentsList .= "</ul>";
            }

            // Get template from database
            $template = $this->getTemplate('nuevo_comentario');
            if (!$template) {
                Log::error('Email template not found: nuevo_comentario');
                return false;
            }

            // Replace variables in subject
            $subject = str_replace('{{ticket_number}}', $ticket->ticket_number, $template->subject);

            // Replace variables in body
            $body = str_replace([
                '{{ticket_number}}',
                '{{subject}}',
                '{{comment_author}}',
                '{{comment_body}}',
                '{{attachments_list}}',
                '{{ticket_url}}',
                '{{system_title}}',
            ], [
                $ticket->ticket_number,
                $ticket->subject,
                $comment->user->name,
                $comment->body,
                $attachmentsList,
                $this->getTicketUrl($ticket->id),
                'Sistema de Soporte',
            ], $template->body_html);

            // Send email to requester with attachments
            return $this->sendEmail($ticket->requester->email, $subject, $body, $commentAttachments);
        } catch (\Exception $e) {
            Log::error('Failed to send new comment notification', [
                'ticket_id' => $ticket->id,
                'comment_id' => $comment->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get email template from database
     *
     * @param string $templateKey Template key
     * @return \App\Model\Entity\EmailTemplate|null
     */
    private function getTemplate(string $templateKey): ?\App\Model\Entity\EmailTemplate
    {
        $templatesTable = $this->fetchTable('EmailTemplates');

        return $templatesTable->find()
            ->where([
                'template_key' => $templateKey,
                'is_active' => true,
            ])
            ->first();
    }

    /**
     * Replace variables in template string
     *
     * @param string $template Template string with {{variables}}
     * @param array $variables Associative array of variable name => value
     * @return string Template with replaced variables
     */
    private function replaceVariables(string $template, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $template = str_replace("{{" . $key . "}}", (string)$value, $template);
        }

        return $template;
    }

    /**
     * Format date for email display (consistent with TimeHumanHelper::long)
     *
     * @param \DateTimeInterface|null $date Date to format
     * @return string Formatted date string
     */
    private function formatDate(?\DateTimeInterface $date): string
    {
        if ($date === null) {
            return '-';
        }

        // Convert to DateTime if needed
        if (!($date instanceof \Cake\I18n\DateTime)) {
            $date = new \Cake\I18n\DateTime($date);
        }

        // Format: "18 noviembre, 2:30 pm" (same as TimeHumanHelper::long)
        return $date->i18nFormat('d MMMM, h:mm a', null, 'es_US');
    }

    /**
     * Send email using configured mailer
     *
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body HTML body
     * @param array $attachments Array of Attachment entities (optional)
     * @return bool Success status
     */
    private function sendEmail(string $to, string $subject, string $body, array $attachments = []): bool
    {
        try {
            // Load SMTP config from database
            $settingsTable = $this->fetchTable('SystemSettings');
            $settings = $settingsTable->find()
                ->where(['setting_key IN' => [
                    'smtp_host', 'smtp_port', 'smtp_username',
                    'smtp_password', 'smtp_encryption', 'system_title'
                ]])
                ->all()
                ->combine('setting_key', 'setting_value')
                ->toArray();

            // Configure SMTP transport
            TransportFactory::setConfig('smtp_custom', [
                'className' => 'Smtp',
                'host' => $settings['smtp_host'] ?? 'smtp.gmail.com',
                'port' => (int)($settings['smtp_port'] ?? 587),
                'username' => $settings['smtp_username'] ?? '',
                'password' => $settings['smtp_password'] ?? '',
                'tls' => ($settings['smtp_encryption'] ?? 'tls') === 'tls',
            ]);

            // Create mailer
            $mailer = new Mailer('default');
            $mailer->setTransport('smtp_custom');

            // Set from address
            $fromEmail = $settings['smtp_username'] ?? 'noreply@localhost';
            $fromName = $settings['system_title'] ?? 'Sistema de Soporte';

            $mailer
                ->setEmailFormat('html')
                ->setFrom([$fromEmail => $fromName])
                ->setTo($to)
                ->setSubject($subject);

            // Add attachments if provided
            if (!empty($attachments)) {
                $attachmentService = new AttachmentService();
                $attachmentFiles = [];

                foreach ($attachments as $attachment) {
                    $filePath = $attachmentService->getFullPath($attachment);
                    if (file_exists($filePath)) {
                        $attachmentFiles[$attachment->original_filename] = [
                            'file' => $filePath,
                            'mimetype' => $attachment->mime_type,
                        ];
                    }
                }

                if (!empty($attachmentFiles)) {
                    $mailer->setAttachments($attachmentFiles);
                }
            }

            $mailer->deliver($body);

            Log::info('Email sent successfully', ['to' => $to, 'subject' => $subject]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send email', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'subject' => $subject,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get ticket URL
     *
     * @param int $ticketId Ticket ID
     * @return string Full URL to ticket
     */
    private function getTicketUrl(int $ticketId): string
    {
        $baseUrl = Configure::read('App.fullBaseUrl', 'http://localhost:8765');
        return $baseUrl . '/tickets/view/' . $ticketId;
    }

    /**
     * Get SMTP configuration from system settings
     *
     * @return array SMTP configuration
     */
    public function getSmtpConfig(): array
    {
        $settingsTable = $this->fetchTable('SystemSettings');
        $settings = $settingsTable->find()
            ->select(['setting_key', 'setting_value'])
            ->where(['setting_key LIKE' => 'smtp_%'])
            ->toArray();

        $config = [];
        foreach ($settings as $setting) {
            $key = str_replace('smtp_', '', $setting->setting_key);
            $config[$key] = $setting->setting_value;
        }

        return $config;
    }

    /**
     * Send new PQRS notification to requester
     *
     * @param \App\Model\Entity\Pqr $pqrs PQRS entity
     * @return bool Success status
     */
    public function sendNewPqrsNotification($pqrs): bool
    {
        try {
            // Get template from database
            $template = $this->getTemplate('nuevo_pqrs');
            if (!$template) {
                Log::error('Email template not found: nuevo_pqrs');
                return false;
            }

            // Map PQRS type to friendly label
            $typeLabels = [
                'peticion' => 'Petición',
                'queja' => 'Queja',
                'reclamo' => 'Reclamo',
                'sugerencia' => 'Sugerencia',
            ];

            // Replace variables in subject
            $subject = str_replace([
                '{{pqrs_number}}',
                '{{subject}}',
            ], [
                $pqrs->pqrs_number,
                $pqrs->subject,
            ], $template->subject);

            // Replace variables in body
            $body = str_replace([
                '{{pqrs_number}}',
                '{{pqrs_type}}',
                '{{subject}}',
                '{{requester_name}}',
                '{{created_date}}',
                '{{system_title}}',
            ], [
                $pqrs->pqrs_number,
                $typeLabels[$pqrs->type] ?? ucfirst($pqrs->type),
                $pqrs->subject,
                $pqrs->requester_name,
                $this->formatDate($pqrs->created),
                'Sistema de Atención al Cliente',
            ], $template->body_html);

            return $this->sendEmail($pqrs->requester_email, $subject, $body);
        } catch (\Exception $e) {
            Log::error('Failed to send new PQRS notification', [
                'pqrs_id' => $pqrs->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send PQRS status change notification
     *
     * @param \App\Model\Entity\Pqr $pqrs PQRS entity
     * @param string $oldStatus Old status
     * @param string $newStatus New status
     * @return bool Success status
     */
    public function sendPqrsStatusChangeNotification($pqrs, string $oldStatus, string $newStatus): bool
    {
        try {
            // Get template from database
            $template = $this->getTemplate('pqrs_estado');
            if (!$template) {
                Log::error('Email template not found: pqrs_estado');
                return false;
            }

            $statusLabels = [
                'nuevo' => 'Nuevo',
                'en_revision' => 'En Revisión',
                'en_proceso' => 'En Proceso',
                'resuelto' => 'Resuelto',
                'cerrado' => 'Cerrado',
            ];

            $typeLabels = [
                'peticion' => 'Petición',
                'queja' => 'Queja',
                'reclamo' => 'Reclamo',
                'sugerencia' => 'Sugerencia',
            ];

            // Build assignee info if there's an assignee
            $assigneeInfo = '';
            if (!empty($pqrs->assignee_id)) {
                $pqrsTable = $this->fetchTable('Pqrs');
                $pqrsWithAssignee = $pqrsTable->get($pqrs->id, contain: ['Assignees']);
                if ($pqrsWithAssignee->assignee) {
                    $assigneeInfo = "<p><strong>Asignado a:</strong> {$pqrsWithAssignee->assignee->name}</p>";
                }
            }

            // Replace variables in subject
            $subject = str_replace('{{pqrs_number}}', $pqrs->pqrs_number, $template->subject);

            // Replace variables in body
            $body = str_replace([
                '{{pqrs_number}}',
                '{{pqrs_type}}',
                '{{subject}}',
                '{{requester_name}}',
                '{{old_status}}',
                '{{new_status}}',
                '{{assignee_info}}',
                '{{system_title}}',
            ], [
                $pqrs->pqrs_number,
                $typeLabels[$pqrs->type] ?? ucfirst($pqrs->type),
                $pqrs->subject,
                $pqrs->requester_name,
                $statusLabels[$oldStatus] ?? ucfirst($oldStatus),
                $statusLabels[$newStatus] ?? ucfirst($newStatus),
                $assigneeInfo,
                'Sistema de Atención al Cliente',
            ], $template->body_html);

            return $this->sendEmail($pqrs->requester_email, $subject, $body);
        } catch (\Exception $e) {
            Log::error('Failed to send PQRS status change notification', [
                'pqrs_id' => $pqrs->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send PQRS new comment notification
     *
     * @param \App\Model\Entity\Pqr $pqrs PQRS entity
     * @param \App\Model\Entity\PqrsComment $comment Comment entity
     * @return bool Success status
     */
    public function sendPqrsNewCommentNotification($pqrs, $comment): bool
    {
        try {
            // Only send for public comments
            if ($comment->comment_type !== 'public') {
                return true;
            }

            // Get template from database
            $template = $this->getTemplate('pqrs_comentario');
            if (!$template) {
                Log::error('Email template not found: pqrs_comentario');
                return false;
            }

            // Load comment with user
            $commentsTable = $this->fetchTable('PqrsComments');
            $comment = $commentsTable->get($comment->id, contain: ['Users']);

            $author = $comment->user ? $comment->user->name : 'Sistema';

            // Replace variables in subject
            $subject = str_replace('{{pqrs_number}}', $pqrs->pqrs_number, $template->subject);

            // Replace variables in body
            $body = str_replace([
                '{{pqrs_number}}',
                '{{subject}}',
                '{{comment_author}}',
                '{{comment_body}}',
                '{{system_title}}',
            ], [
                $pqrs->pqrs_number,
                $pqrs->subject,
                $author,
                $comment->body,
                'Sistema de Atención al Cliente',
            ], $template->body_html);

            return $this->sendEmail($pqrs->requester_email, $subject, $body);
        } catch (\Exception $e) {
            Log::error('Failed to send PQRS comment notification', [
                'pqrs_id' => $pqrs->id,
                'comment_id' => $comment->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
