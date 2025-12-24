<?php
/**
 * @var \App\View\AppView $this
 * @var array $settings
 */
$this->assign('title', 'Usuarios');
?>
<div class="p-5" style="margin: 0 auto; max-width: 800px; width: 100%">
    <div class="">
        <div class="d-flex justify-content-between mb-2">
            <div>
                <h3><i class="bi bi-people"></i> Gestión de Usuarios</h3>
                <p class="fw-light">Administra los usuarios del sistema</p>
            </div>
            <div>
                <?= $this->Html->link(
                    '<i class="bi bi-person-add"></i> Nuevo Usuario',
                    ['action' => 'addUser'],
                    ['class' => 'btn btn-success', 'escapeTitle' => false]
                ) ?>
            </div>
        </div>
    </div>

    <?= $this->Flash->render() ?>

    <div class="overflow-auto scroll">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Organización</th>
                    <th>Estado</th>
                    <th>Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="align-middle">
                                <div class="d-flex gap-2 align-items-center">
                                    <?= $this->User->profileImageTag($user, ['width' => '45', 'height' => '45', 'class' => 'rounded-circle object-fit-cover shadow']) ?>
                                    <strong class="lh-1"><?= h($user->name) ?></strong>
                                </div>
                            </td>
                            <td class="align-middle"><?= h($user->email) ?></td>
                            <td class="align-middle">
                                <span class="">
                                    <?php
                                    $roles = [
                                        'admin' => 'Administrador',
                                        'agent' => 'Agente',
                                        'servicio_cliente' => 'Servicio al Cliente',
                                        'compras' => 'Compras',
                                        'requester' => 'Solicitante'
                                    ];
                                    echo $roles[$user->role] ?? $user->role;
                                    ?>
                                </span>
                            </td>
                            <td class="align-middle">
                                <?= $user->organization ? h($user->organization->name) : '<em>Sin organización</em>' ?>
                            </td>
                            <td class="align-middle">
                                <span class="status-badge <?= $user->is_active ? 'active' : 'inactive' ?>">
                                    <?= $user->is_active ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td class="align-middle"><?= $user->created->format('d/m/Y') ?></td>
                            <td class="align-middle">
                                <div class="action-buttons d-flex gap-2">
                                    <?= $this->Html->link(
                                        '<i class="bi bi-pencil"></i>',
                                        ['action' => 'editUser', $user->id],
                                        ['class' => 'btn btn-outline-warning p-2', 'title' => 'Editar', 'escape' => false]
                                    ) ?>
                                    <?php if ($user->is_active): ?>
                                        <?= $this->Form->postLink(
                                            '<i class="bi bi-person-x"></i>',
                                            ['action' => 'deactivateUser', $user->id],
                                            [
                                                'class' => 'btn btn-outline-danger p-2',
                                                'title' => 'Desactivar',
                                                'confirm' => '¿Desactivar a ' . $user->name . '?',
                                                'escape' => false
                                            ]
                                        ) ?>
                                    <?php else: ?>
                                        <?= $this->Form->postLink(
                                            '✓',
                                            ['action' => 'activateUser', $user->id],
                                            [
                                                'class' => 'btn p-1',
                                                'title' => 'Activar'
                                            ]
                                        ) ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="empty-state">
                            No hay usuarios registrados.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($users->count() > 0): ?>
        <div class="pagination-wrapper">
            <?= $this->element('pagination') ?>
        </div>
    <?php endif; ?>
</div>