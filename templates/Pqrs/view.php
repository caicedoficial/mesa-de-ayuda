<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Pqr $pqrs
 */
$this->assign('title', $pqrs->pqrs_number);
?>

<div class="pqrs-view-container">
    <?= $this->element('pqrs/left_sidebar', ['pqrs' => $pqrs, 'agents' => $agents]) ?>

    <!-- Main Content Area -->
    <div class="main-content d-flex flex-column p-3 gap-2">
        <?= $this->element('pqrs/header', ['pqrs' => $pqrs]) ?>
        <?= $this->element('pqrs/comments_area', ['pqrs' => $pqrs]) ?>
        <?= $this->element('pqrs/reply_editor', ['pqrs' => $pqrs]) ?>
    </div>

    <?= $this->element('pqrs/right_sidebar', ['pqrs' => $pqrs]) ?>
</div>

<?= $this->element('pqrs/styles_and_scripts') ?>
