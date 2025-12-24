<?php
/**
 * @var \App\View\AppView $this
 * @var array $settings
 */
$this->assign('title', 'Gestión de Etiquetas');
?>
<div class="p-5" style="max-width: 800px; margin: 0 auto; width: 100%;">
    <div>
        <div class="d-flex justify-content-between mb-4 ">
            <div>
                <h3><i class="bi bi-tags"></i> Gestión de Etiquetas</h3>
                <p class="fw-light">Administra las etiquetas para organizar tickets</p>
            </div>
            <div>
                <?= $this->Html->link(
                    '<i class="bi bi-tag"></i> Nueva Etiqueta',
                    ['action' => 'addTag'],
                    ['class' => 'btn btn-primary', 'escape' => false]
                ) ?>
            </div>
        </div>
    </div>

    <?= $this->Flash->render() ?>

    <div class="container">
        <?php if (!empty($tags)): ?>
            <div class="row justify-content-center">
                <?php foreach ($tags as $tag): ?>
                    <div class="col-md-5 m-3 p-3 rounded d-flex align-items-start shadow-sm bg-white">
                        <div class="d-flex flex-column gap-4 p-2 w-100">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="m-0" style="font-size: 14px;">
                                    <span style="background-color: <?= h($tag->color) ?>; padding: 4px 8px; border-radius: 4px; color: #fff; ">
                                        <?= h($tag->name) ?>
                                    </span>
                                </h6>
                                <div>
                                    <span class="border rounded px-3 py-1">
                                        <?= $tag->ticket_count ?? 0 ?> tickets
                                    </span>
                                </div>
                            </div>

                            <div class="mx-auto">
                                <?= $this->Html->link(
                                    '<i class="bi bi-pencil"></i> Editar',
                                    ['action' => 'editTag', $tag->id],
                                    ['class' => 'btn btn-sm btn-primary', 'escape' => false]
                                ) ?>
                                <?= $this->Form->postLink(
                                    '<i class="bi bi-trash"></i> Eliminar',
                                    ['action' => 'deleteTag', $tag->id],
                                    [
                                        'class' => 'btn btn-sm btn-danger',
                                        'confirm' => '¿Estás seguro de eliminar la etiqueta "' . $tag->name . '"?',
                                        'escape' => false
                                    ]
                                ) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>No hay etiquetas creadas</h3>
                <p>Las etiquetas te ayudan a organizar y categorizar tus tickets.</p>
                <?= $this->Html->link(
                    'Crear primera etiqueta',
                    ['action' => 'addTag'],
                    ['class' => 'btn btn-primary']
                ) ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
