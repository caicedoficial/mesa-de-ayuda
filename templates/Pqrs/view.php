<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Pqr $pqrs
 * @var string $entityType Injected by controller trait
 * @var array $entityMetadata Injected by controller trait
 * @var array $statuses Injected by controller trait
 * @var array $resolvedStatuses Injected by controller trait
 */
$this->assign('title', $pqrs->pqrs_number);
$user = $this->getRequest()->getAttribute('identity');
?>

<div class="<?= $entityMetadata['containerClass'] ?>">
    <?= $this->element('pqrs/left_sidebar', [
        'pqrs' => $pqrs,
        'agents' => $agents
    ]) ?>

    <!-- Main Content Area -->
    <div class="main-content d-flex flex-column p-3 gap-2">
        <?= $this->element('shared/entity_header', [
            'entity' => $pqrs,
            'entityType' => $entityType,
            'entityMetadata' => $entityMetadata,
            'resolvedStatuses' => $resolvedStatuses
        ]) ?>

        <?= $this->element('shared/comments_list', [
            'entity' => $pqrs,
            'entityType' => $entityType,
            'comments' => $pqrs->pqrs_comments ?? [],
            'description' => $pqrs->description ?? '',
            'attachments' => $pqrs->pqrs_attachments ?? []
        ]) ?>

        <?= $this->element('shared/reply_editor', [
            'entity' => $pqrs,
            'entityType' => $entityType,
            'statuses' => $statuses,
            'currentUser' => $user
        ]) ?>
    </div>

    <?= $this->element('pqrs/right_sidebar', ['pqrs' => $pqrs]) ?>
</div>

<?= $this->element('shared/entity_styles_and_scripts', [
    'entityType' => $entityType,
    'entity' => $pqrs,
    'entityMetadata' => $entityMetadata,
    'statuses' => $statuses
]) ?>
