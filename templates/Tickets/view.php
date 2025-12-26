<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Ticket $ticket
 * @var string $entityType Injected by controller trait
 * @var array $entityMetadata Injected by controller trait
 * @var array $statuses Injected by controller trait
 * @var array $resolvedStatuses Injected by controller trait
 */
$this->assign('title', $ticket->ticket_number);
$user = $this->getRequest()->getAttribute('identity');
?>

<div class="<?= $entityMetadata['containerClass'] ?>">
    <?= $this->element('tickets/left_sidebar', [
        'ticket' => $ticket,
        'agents' => $agents,
        'tags' => $tags,
        'user' => $user
    ]) ?>

    <!-- Main Content Area -->
    <div class="main-content d-flex flex-column p-3 gap-2">
        <?= $this->element('shared/entity_header', [
            'entity' => $ticket,
            'entityType' => $entityType,
            'entityMetadata' => $entityMetadata,
            'resolvedStatuses' => $resolvedStatuses
        ]) ?>

        <?= $this->element('shared/comments_list', [
            'entity' => $ticket,
            'entityType' => $entityType,
            'comments' => $ticket->ticket_comments ?? [],
            'description' => $ticket->description ?? '',
            'attachments' => $ticket->attachments ?? []
        ]) ?>

        <?= $this->element('shared/reply_editor', [
            'entity' => $ticket,
            'entityType' => $entityType,
            'statuses' => $statuses,
            'currentUser' => $currentUser
        ]) ?>
    </div>

    <?= $this->element('tickets/right_sidebar', ['ticket' => $ticket]) ?>
</div>

<?= $this->element('shared/entity_styles_and_scripts', [
    'entityType' => $entityType,
    'entity' => $ticket,
    'entityMetadata' => $entityMetadata,
    'statuses' => $statuses
]) ?>
