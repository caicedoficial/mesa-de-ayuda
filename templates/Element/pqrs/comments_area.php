<?php
/**
 * Comments Area Element for PQRS
 * Now uses shared element for consistency
 */
?>

<?= $this->element('shared/comments_list', [
    'entity' => $pqrs,
    'entityType' => 'pqrs',
    'comments' => $pqrs->pqrs_comments ?? [],
    'description' => $pqrs->description ?? '',
    'attachments' => $pqrs->pqrs_attachments ?? []
]) ?>
