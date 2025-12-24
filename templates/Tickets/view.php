<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Ticket $ticket
 */
$this->assign('title', $ticket->ticket_number);
$user = $this->getRequest()->getAttribute('identity');

$statuses = [
    'nuevo' => ['icon' => 'bi-circle-fill', 'color' => 'warning', 'label' => 'Nuevo'],
    'abierto' => ['icon' => 'bi-circle-fill', 'color' => 'danger', 'label' => 'Abierto'],
    'pendiente' => ['icon' => 'bi-circle-fill', 'color' => 'primary', 'label' => 'Pendiente'],
    'resuelto' => ['icon' => 'bi-circle-fill', 'color' => 'success', 'label' => 'Resuelto']
];
?>

<div class="ticket-view-container">
    <?= $this->element('tickets/left_sidebar', ['ticket' => $ticket, 'agents' => $agents, 'tags' => $tags, 'user' => $user]) ?>

    <!-- Main Content Area -->
    <div class="main-content d-flex flex-column p-3 gap-2">
        <?= $this->element('tickets/header', ['ticket' => $ticket]) ?>
        <?= $this->element('tickets/comments_area', ['ticket' => $ticket]) ?>
        <?= $this->element('shared/reply_editor', [
            'entity' => $ticket,
            'entityType' => 'ticket',
            'statuses' => $statuses,
            'currentUser' => $currentUser
        ]) ?>
    </div>

    <?= $this->element('tickets/right_sidebar', ['ticket' => $ticket]) ?>
</div>

<?= $this->element('tickets/styles_and_scripts') ?>
