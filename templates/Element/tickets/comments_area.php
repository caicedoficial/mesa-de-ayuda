<?php
/**
 * Comments Area Element for Tickets
 * Now uses shared element for consistency
 */
?>

<?= $this->element('shared/comments_list', [
    'entity' => $ticket,
    'entityType' => 'ticket',
    'comments' => $ticket->ticket_comments ?? [],
    'description' => $ticket->description ?? '',
    'attachments' => $ticket->attachments ?? []
]) ?>
