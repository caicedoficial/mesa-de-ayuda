<?php
declare(strict_types=1);

namespace App\Service\Renderer;

use Cake\Core\Configure;
use Cake\I18n\DateTime;

/**
 * Notification Renderer
 *
 * Responsible for formatting data and generating content for notifications
 * (Email HTML snippets and WhatsApp text messages).
 */
class NotificationRenderer
{
    /**
     * Format date for display
     *
     * @param \DateTimeInterface|string|null $date Date to format
     * @return string Formatted date
     */
    public function formatDate($date): string
    {
        if ($date === null) {
            return '-';
        }

        if (!($date instanceof DateTime)) {
            $date = new DateTime($date);
        }

        return $date->i18nFormat('d MMMM, h:mm a', null, 'es_US');
    }

    /**
     * Get Ticket URL
     *
     * @param int $id Ticket ID
     * @return string Full URL
     */
    public function getTicketUrl(int $id): string
    {
        $baseUrl = Configure::read('App.fullBaseUrl', 'http://localhost:8765');
        return $baseUrl . '/tickets/view/' . $id;
    }

    /**
     * Get Status Label
     *
     * @param string $status Status key
     * @return string Human readable label
     */
    public function getStatusLabel(string $status): string
    {
        $labels = [
            'nuevo' => 'Nuevo',
            'abierto' => 'Abierto',
            'pendiente' => 'Pendiente',
            'resuelto' => 'Resuelto',
            'cerrado' => 'Cerrado',
            'en_revision' => 'En Revisión',
            'en_proceso' => 'En Proceso',
            'aprobado' => 'Aprobado',
            'completado' => 'Completado',
            'rechazado' => 'Rechazado',
        ];

        return $labels[$status] ?? ucfirst($status);
    }

    /**
     * Get Type Label (for PQRS)
     *
     * @param string $type Type key
     * @return string Human readable label
     */
    public function getTypeLabel(string $type): string
    {
        $labels = [
            'peticion' => 'Petición',
            'queja' => 'Queja',
            'reclamo' => 'Reclamo',
            'sugerencia' => 'Sugerencia',
        ];

        return $labels[$type] ?? ucfirst($type);
    }

    /**
     * Render attachments list as HTML
     *
     * @param array $attachments Array of attachment entities
     * @return string HTML list
     */
    public function renderAttachmentsHtml(array $attachments): string
    {
        if (empty($attachments)) {
            return '';
        }

        $html = '<td>';
        $html .= '<p><strong>Archivos Adjuntos</strong></p><ul>';

        foreach ($attachments as $attachment) {
            $sizeKB = number_format($attachment->file_size / 1024, 1);
            $html .= "<li>{$attachment->original_filename} ({$sizeKB} KB)</li>";
        }

        $html .= '</ul>';

        return $html;
    }

    /**
     * Render status change section as HTML
     *
     * @param string $oldStatus Old status key
     * @param string $newStatus New status key
     * @param string $assigneeName Assignee name
     * @return string HTML section
     */
    public function renderStatusChangeHtml(string $oldStatus, string $newStatus, string $assigneeName): string
    {
        $oldLabel = $this->getStatusLabel($oldStatus);
        $newLabel = $this->getStatusLabel($newStatus);

        return '<td>' .
            '<p><strong>Cambio de Estado</strong></p>' .
            '<p>' .
            '<span class="status-badge status-' . $oldStatus . '">' . $oldLabel . '</span>' .
            '<span style="margin: 0 10px;">→</span>' .
            '<span class="status-badge status-' . $newStatus . '">' . $newLabel . '</span>' .
            '</p>' .
            '<p>Asignado a: <strong>' . $assigneeName . '</strong></p>' .
            '</td>';
    }

    /**
     * Render WhatsApp message for new ticket
     *
     * @param \App\Model\Entity\Ticket $ticket Ticket entity
     * @return string Message text
     */
    public function renderWhatsappNewTicket(\App\Model\Entity\Ticket $ticket): string
    {
        return "🎫 *Nuevo Ticket Creado*\n\n" .
            "📋 Ticket: *{$ticket->ticket_number}*\n" .
            "👤 Solicitante: {$ticket->requester->name}\n" .
            "📧 Email: {$ticket->requester->email}\n" .
            "📝 Asunto: {$ticket->subject}\n" .
            "📅 Fecha: {$this->formatDate($ticket->created)}\n\n" .
            "_Sistema de Tickets - Soporte_";
    }

    /**
     * Render WhatsApp message for status change
     *
     * @param \App\Model\Entity\Ticket $ticket Ticket entity
     * @param string $oldStatus Old status
     * @param string $newStatus New status
     * @return string Message text
     */
    public function renderWhatsappStatusChange(\App\Model\Entity\Ticket $ticket, string $oldStatus, string $newStatus): string
    {
        $emojis = [
            'nuevo' => '🆕',
            'abierto' => '🔴',
            'pendiente' => '🔵',
            'resuelto' => '✅',
        ];
        $emoji = $emojis[$newStatus] ?? '📌';
        $assigneeName = $ticket->assignee ? $ticket->assignee->name : 'Sin asignar';

        return "{$emoji} *Cambio de Estado de Ticket*\n\n" .
            "📋 Ticket: *{$ticket->ticket_number}*\n" .
            "📝 Asunto: {$ticket->subject}\n" .
            "👤 Solicitante: {$ticket->requester->name}\n" .
            "🔄 Estado anterior: {$oldStatus}\n" .
            "🔄 Estado nuevo: *{$newStatus}*\n" .
            "👨‍💼 Asignado a: {$assigneeName}\n\n" .
            "_Sistema de Tickets - Soporte_";
    }

    /**
     * Render WhatsApp message for new comment
     *
     * @param \App\Model\Entity\Ticket|\App\Model\Entity\Pqr $entity Ticket or PQRS entity
     * @param \App\Model\Entity\TicketComment|\App\Model\Entity\PqrsComment $comment Comment entity
     * @param bool $isPqrs Whether it is a PQRS
     * @return string Message text
     */
    public function renderWhatsappNewComment($entity, $comment, bool $isPqrs = false): string
    {
        $number = $isPqrs ? $entity->pqrs_number : $entity->ticket_number;
        $type = $isPqrs ? 'PQRS' : 'Ticket';
        $footer = $isPqrs ? '_Sistema de PQRS_' : '_Sistema de Tickets - Soporte_';

        $commentText = strip_tags($comment->body);
        if (mb_strlen($commentText) > 200) {
            $commentText = mb_substr($commentText, 0, 197) . '...';
        }

        $authorName = $comment->user ? $comment->user->name : 'Sistema';

        return "💬 *Nuevo Comentario en {$type}*\n\n" .
            "📋 {$type}: *{$number}*\n" .
            "📝 Asunto: {$entity->subject}\n" .
            "👤 Comentario de: {$authorName}\n" .
            "📅 Fecha: {$this->formatDate($comment->created)}\n\n" .
            "💭 Comentario:\n{$commentText}\n\n" .
            $footer;
    }

    /**
     * Render WhatsApp message for unified response
     *
     * @param \App\Model\Entity\Ticket|\App\Model\Entity\Pqr $entity Ticket or PQRS entity
     * @param mixed $comment Comment entity
     * @param string $oldStatus Old status
     * @param string $newStatus New status
     * @param bool $isPqrs Whether it is a PQRS
     * @return string Message text
     */
    public function renderWhatsappResponse($entity, $comment, string $oldStatus, string $newStatus, bool $isPqrs = false): string
    {
        $number = $isPqrs ? $entity->pqrs_number : $entity->ticket_number;
        $type = $isPqrs ? 'PQRS' : 'Ticket';
        $footer = $isPqrs ? '_Sistema de Atención al Cliente - PQRS_' : '_Sistema de Tickets - Soporte_';

        $hasStatusChange = ($oldStatus !== $newStatus);

        $commentText = strip_tags($comment->body);
        if (mb_strlen($commentText) > 300) {
            $commentText = mb_substr($commentText, 0, 297) . '...';
        }

        $header = $isPqrs
            ? "💬 *Respuesta del Equipo*\n\n"
            : "💬 *Respuesta del Agente*\n\n";

        $typeLine = '';
        if ($isPqrs) {
            $typeEmoji = match ($entity->type) {
                'peticion' => '📝',
                'queja' => '😞',
                'reclamo' => '⚠️',
                'sugerencia' => '💡',
                default => '📋'
            };
            $typeLabel = $this->getTypeLabel($entity->type);
            $typeLine = "{$typeEmoji} Tipo: {$typeLabel}\n";
        }

        $requesterName = $isPqrs ? $entity->requester_name : $entity->requester->name;

        $message = $header .
            "📋 {$type}: *{$number}*\n" .
            $typeLine .
            "📝 Asunto: {$entity->subject}\n" .
            "👤 Solicitante: {$requesterName}\n\n";

        if ($hasStatusChange) {
            $oldLabel = $this->getStatusLabel($oldStatus);
            $newLabel = $this->getStatusLabel($newStatus);
            $assigneeName = $entity->assignee ? $entity->assignee->name : 'Sin asignar';

            // Simple emoji mapping for status change
            $newStatusEmoji = match ($newStatus) {
                'resuelto' => '✅',
                'cerrado' => '🔒',
                'abierto' => '🔓',
                'en_proceso' => '⚙️',
                default => '🔄'
            };

            $message .= "🔄 *Cambio de Estado*\n" .
                "{$oldLabel} → {$newStatusEmoji} *{$newLabel}*\n" .
                "👨‍💼 Asignado a: {$assigneeName}\n\n";
        }

        $authorName = $comment->user ? $comment->user->name : 'Sistema';

        $message .= "💬 *Respuesta de {$authorName}:*\n" .
            "{$commentText}\n\n" .
            "📅 {$this->formatDate($comment->created)}\n\n" .
            $footer;

        return $message;
    }

    /**
     * Render WhatsApp message for new PQRS
     *
     * @param \App\Model\Entity\Pqr $pqrs PQRS entity
     * @return string Message text
     */
    public function renderWhatsappNewPqrs(\App\Model\Entity\Pqr $pqrs): string
    {
        $typeEmojis = [
            'peticion' => '📝',
            'queja' => '⚠️',
            'reclamo' => '❗',
            'sugerencia' => '💡',
        ];
        $typeEmoji = $typeEmojis[$pqrs->type] ?? '📋';
        $typeLabel = $this->getTypeLabel($pqrs->type);

        return "{$typeEmoji} *Nueva PQRS Creada*\n\n" .
            "📋 PQRS: *{$pqrs->pqrs_number}*\n" .
            "🔖 Tipo: {$typeLabel}\n" .
            "👤 Solicitante: {$pqrs->requester_name}\n" .
            "📧 Email: {$pqrs->requester_email}\n" .
            "📝 Asunto: {$pqrs->subject}\n" .
            "📅 Fecha: {$this->formatDate($pqrs->created)}\n\n" .
            "_Sistema de PQRS_";
    }

    /**
     * Render WhatsApp message for PQRS status change
     *
     * @param \App\Model\Entity\Pqr $pqrs PQRS entity
     * @param string $oldStatus Old status
     * @param string $newStatus New status
     * @return string Message text
     */
    public function renderWhatsappPqrsStatusChange(\App\Model\Entity\Pqr $pqrs, string $oldStatus, string $newStatus): string
    {
        $emojis = [
            'nuevo' => '🆕',
            'en_revision' => '👁️',
            'en_proceso' => '⚙️',
            'resuelto' => '✅',
            'cerrado' => '🔒',
        ];
        $emoji = $emojis[$newStatus] ?? '📌';
        $assigneeName = $pqrs->assignee ? $pqrs->assignee->name : 'Sin asignar';

        return "{$emoji} *Cambio de Estado de PQRS*\n\n" .
            "📋 PQRS: *{$pqrs->pqrs_number}*\n" .
            "📝 Asunto: {$pqrs->subject}\n" .
            "👤 Solicitante: {$pqrs->requester_name}\n" .
            "🔄 Estado anterior: {$oldStatus}\n" .
            "🔄 Estado nuevo: *{$newStatus}*\n" .
            "👨‍💼 Asignado a: {$assigneeName}\n\n" .
            "_Sistema de PQRS_";
    }

    /**
     * Render WhatsApp message for new Compra
     *
     * @param \App\Model\Entity\Compra $compra Compra entity
     * @return string Message text
     */
    public function renderWhatsappNewCompra(\App\Model\Entity\Compra $compra): string
    {
        $priorityEmojis = [
            'baja' => '🟢',
            'media' => '🟡',
            'alta' => '🟠',
            'urgente' => '🔴',
        ];
        $priorityEmoji = $priorityEmojis[$compra->priority] ?? '⚪';

        $slaDate = $compra->sla_due_date
            ? $this->formatDate($compra->sla_due_date)
            : 'No definido';

        $assigneeName = $compra->assignee ? $compra->assignee->name : 'Sin asignar';

        return "🛒 *Nueva Orden de Compra*\n\n" .
            "📋 Compra: *{$compra->compra_number}*\n" .
            "👤 Solicitante: {$compra->requester->name}\n" .
            "📧 Email: {$compra->requester->email}\n" .
            "📝 Asunto: {$compra->subject}\n" .
            "{$priorityEmoji} Prioridad: {$compra->priority}\n" .
            "👨‍💼 Asignado a: {$assigneeName}\n" .
            "⏰ SLA vence: {$slaDate}\n" .
            "📅 Creada: {$this->formatDate($compra->created)}\n\n" .
            "_Sistema de Compras_";
    }
}
