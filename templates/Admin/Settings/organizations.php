<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Organization> $organizations
 */
$this->assign('title', 'Gestión de Organizaciones');
?>

<div class="p-5" style="max-width: 1000px; margin: 0 auto; width: 100%;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-building"></i> Organizaciones</h3>
        <?= $this->Html->link('<i class="bi bi-plus-lg"></i> Nueva Organización', ['action' => 'addOrganization'], ['class' => 'btn btn-primary shadow-sm', 'escape' => false]) ?>
    </div>

    <div class="bg-white p-4 rounded shadow-sm">
        <?php if (count($organizations) === 0): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-building" style="font-size: 3rem;"></i>
                <p class="mt-3">No hay organizaciones registradas.</p>
                <?= $this->Html->link('Crear la primera organización', ['action' => 'addOrganization'], ['class' => 'btn btn-primary mt-2']) ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th class="text-center">Usuarios</th>
                            <th>Creado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($organizations as $organization): ?>
                            <tr>
                                <td class="fw-bold"><?= h($organization->name) ?></td>
                                <td class="text-center">
                                    <span class="badge bg-secondary rounded-pill">
                                        <?= $organization->user_count ?>
                                    </span>
                                </td>
                                <td><?= h($organization->created->format('d/m/Y')) ?></td>
                                <td class="text-end">
                                    <?= $this->Html->link('<i class="bi bi-pencil"></i>', ['action' => 'editOrganization', $organization->id], ['class' => 'btn btn-sm btn-outline-primary', 'escape' => false, 'title' => 'Editar']) ?>
                                    <?= $this->Form->postLink('<i class="bi bi-trash"></i>', ['action' => 'deleteOrganization', $organization->id], ['confirm' => '¿Estás seguro de eliminar esta organización?', 'class' => 'btn btn-sm btn-outline-danger', 'escape' => false, 'title' => 'Eliminar']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="paginator mt-3">
                <ul class="pagination justify-content-center">
                    <?= $this->Paginator->prev('« Anterior') ?>
                    <?= $this->Paginator->numbers() ?>
                    <?= $this->Paginator->next('Siguiente »') ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="mt-3">
        <?= $this->Html->link('<i class="bi bi-arrow-left"></i> Volver a Configuración', ['action' => 'index'], ['class' => 'btn btn-outline-secondary', 'escape' => false]) ?>
    </div>
</div>