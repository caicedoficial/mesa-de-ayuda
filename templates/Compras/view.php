<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Compra $compra
 * @var string $entityType Injected by controller trait
 * @var array $entityMetadata Injected by controller trait
 * @var array $statuses Injected by controller trait
 * @var array $resolvedStatuses Injected by controller trait
 */
$this->assign('title', $compra->compra_number);
$user = $this->getRequest()->getAttribute('identity');
?>

<div class="<?= $entityMetadata['containerClass'] ?>">
    <?= $this->element('compras/left_sidebar', [
        'compra' => $compra,
        'comprasUsers' => $comprasUsers,
        'user' => $user
    ]) ?>

    <!-- Main Content Area -->
    <div class="main-content d-flex flex-column p-3 gap-2">
        <?= $this->element('shared/entity_header', [
            'entity' => $compra,
            'entityType' => $entityType,
            'entityMetadata' => $entityMetadata,
            'resolvedStatuses' => $resolvedStatuses
        ]) ?>

        <?= $this->element('shared/comments_list', [
            'entity' => $compra,
            'entityType' => $entityType,
            'comments' => $compra->compras_comments ?? [],
            'description' => $compra->description ?? '',
            'attachments' => $compra->compras_attachments ?? []
        ]) ?>

        <?= $this->element('shared/reply_editor', [
            'entity' => $compra,
            'entityType' => $entityType,
            'statuses' => $statuses,
            'currentUser' => $user
        ]) ?>
    </div>

    <?= $this->element('compras/right_sidebar', [
        'compra' => $compra,
        'agents' => $agents ?? [],
        'user' => $user
    ]) ?>
</div>

<?= $this->element('shared/entity_styles_and_scripts', [
    'entityType' => $entityType,
    'entity' => $compra,
    'entityMetadata' => $entityMetadata,
    'statuses' => $statuses
]) ?>
