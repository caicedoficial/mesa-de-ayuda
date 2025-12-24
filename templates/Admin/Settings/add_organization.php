<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Organization $organization
 */
$this->assign('title', 'Nueva Organización');
?>

<div class="p-5" style="max-width: 600px; margin: 0 auto; width: 100%;">
    <div class="mb-4">
        <h3><i class="bi bi-building-add"></i> Nueva Organización</h3>
    </div>

    <div class="bg-white p-4 rounded shadow-sm">
        <?= $this->Form->create($organization) ?>
        
        <div class="mb-4">
            <?= $this->Form->control('name', [
                'label' => 'Nombre de la Organización',
                'class' => 'form-control',
                'placeholder' => 'Ej: Acme Corp'
            ]) ?>
        </div>

        <div class="d-flex justify-content-between">
            <?= $this->Html->link('Cancelar', ['action' => 'organizations'], ['class' => 'btn btn-outline-secondary']) ?>
            <?= $this->Form->button('Guardar Organización', ['class' => 'btn btn-primary']) ?>
        </div>
        
        <?= $this->Form->end() ?>
    </div>
</div>