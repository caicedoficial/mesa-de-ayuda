<?php
/**
 * @var \App\View\AppView $this
 * @var array $settings
 */
$this->assign('title', 'Editar Plantilla');
?>
<div class="p-5" style="max-width: 800px; margin: 0 auto; width: 100%;">
    <div>
        <h3>Editar Plantilla de Email</h3>
        <p class="fw-light">Modifica la plantilla: <strong><?= h($template->template_key) ?></strong></p>
    </div>

    <?= $this->Flash->render() ?>

    <?= $this->Form->create($template) ?>
    <div class="p-4 border rounded shadow-sm" style="background: #fff;">
        <div class="mb-4">
            <h4>Información General</h4>

            <div class="mb-2">
                <?= $this->Form->label('template_key', 'Clave de la Plantilla', ['class' => 'form-label']) ?>
                <?= $this->Form->text('template_key', [
                    'class' => 'form-control form-control-plaintext px-3',
                    'disabled' => true,
                    'title' => 'La clave no se puede modificar'
                ]) ?>
                <small class="text-muted fw-light">La clave identifica la plantilla y no se puede cambiar</small>
            </div>

            <div class="mb-2">
                <?= $this->Form->label('subject', 'Asunto del Email', ['class' => 'form-label']) ?>
                <?= $this->Form->text('subject', [
                    'class' => 'form-control shadow-none',
                    'placeholder' => 'Ej: [Ticket #{{ticket_number}}] {{subject}}'
                ]) ?>
                <small class="text-muted fw-light">Puedes usar variables como {{ticket_number}}, {{subject}}, etc.</small>
            </div>

            <div class="mb-2">
                <label class="fw-bold">
                    <?= $this->Form->checkbox('is_active', ['id' => 'is_active']) ?>
                    Plantilla activa
                </label>
                <small class="text-muted fw-light">(Si está desactivada, no se enviará este tipo de notificación)</small>
            </div>
        </div>

        <div class="mb-4">
            <h4>Contenido HTML</h4>

            <code class="form-group">
                <?= $this->Form->label('body_html', 'Cuerpo del email (HTML)', ['class' => 'form-label']) ?>
                <?= $this->Form->textarea('body_html', [
                    'class' => 'form-control code-editor scroll',
                    'rows' => 20,
                    'style' => 'font-family: monospace; font-size: 13px;'
                ]) ?>
            </code>

            <div class="variables-help ">
                <h4>Variables Disponibles:</h4>
                <div class="variables-grid">
                    <?php
                    $vars = json_decode($template->available_variables, true);
                    if ($vars):
                        foreach ($vars as $var):
                    ?>
                        <code>{{<?= h($var) ?>}}</code>
                    <?php
                        endforeach;
                    endif;
                    ?>
                </div>
            </div>
        </div>

        <div class="mb-3 d-flex gap-3 justify-content-end">
            <?= $this->Form->button('<i class="bi bi-arrow-up-circle"></i> Guardar', [
                'class' => 'btn btn-success',
                'escapeTitle' => false
            ]) ?>
            <?= $this->Html->link('<i class="bi bi-x-circle"></i> Cancelar', ['action' => 'emailTemplates'], [
                'class' => 'btn btn-danger',
                'escape' => false
            ]) ?>
            <?= $this->Html->link('<i class="bi bi-eye"></i> Vista Previa', [
                'action' => 'previewTemplate',
                $template->id
            ], [
                'class' => 'btn btn-secondary',
                'target' => '_blank',
                'escape' => false
            ]) ?>
        </div>
    </div>
    <?= $this->Form->end() ?>
</div>

<style>
.code-editor {
    font-family: 'Courier New', monospace;
    background-color: #333;
    color: #fff;
}

.code-editor:focus {
    background-color: #333;
    color: #fff;
}

.variables-help {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 6px;
    margin-top: 20px;
}

.variables-help h4 {
    margin: 0 0 15px 0;
    color: #333;
    font-size: 14px;
    font-weight: 600;
}

.variables-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.variables-grid code {
    background: white;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 12px;
    color: #d63384;
    border: 1px solid #e0e0e0;
}

</style>
