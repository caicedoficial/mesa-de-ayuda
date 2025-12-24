<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Compra $compra
 */
$this->assign('title', $compra->compra_number);
$user = $this->getRequest()->getAttribute('identity');
?>

<div class="pqrs-view-container">
    <?= $this->element('compras/left_sidebar', [
        'compra' => $compra,
        'comprasUsers' => $comprasUsers,
        'user' => $user
    ]) ?>

    <!-- Main Content Area -->
    <div class="main-content d-flex flex-column p-3 gap-2">
        <?= $this->element('compras/header', ['compra' => $compra]) ?>
        <?= $this->element('shared/comments_list', [
            'entity' => $compra,
            'comments' => $compra->compras_comments,
            'entityType' => 'compra'
        ]) ?>
        <?= $this->element('shared/reply_editor', [
            'entity' => $compra,
            'entityType' => 'compra',
            'statuses' => $statuses,
            'currentUser' => $user
        ]) ?>
    </div>

    <?= $this->element('compras/right_sidebar', ['compra' => $compra]) ?>
</div>

<?= $this->element('compras/styles_and_scripts') ?>
