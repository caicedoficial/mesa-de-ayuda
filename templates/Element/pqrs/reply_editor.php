<?php
/**
 * Reply Editor Element for PQRS
 * Now uses shared element for consistency
 */

// Define status configuration for PQRS
$statuses = [
    'nuevo' => ['icon' => 'bi-circle-fill', 'color' => 'warning', 'label' => 'Nuevo'],
    'en_revision' => ['icon' => 'bi-circle-fill', 'color' => 'info', 'label' => 'En Revisión'],
    'en_proceso' => ['icon' => 'bi-circle-fill', 'color' => 'primary', 'label' => 'En Proceso'],
    'resuelto' => ['icon' => 'bi-circle-fill', 'color' => 'success', 'label' => 'Resuelto'],
    'cerrado' => ['icon' => 'bi-circle-fill', 'color' => 'secondary', 'label' => 'Cerrado']
];
?>

<?= $this->element('shared/reply_editor', [
    'entity' => $pqrs,
    'entityType' => 'pqrs',
    'statuses' => $statuses,
    'currentUser' => $currentUser
]) ?>
