<?php
/**
 * @var \App\View\AppView $this
 * @var array $settings
 */
$this->assign('title', 'Editar usuario');
?>
<div class="p-5" style="max-width: 800px; margin: 0 auto; width: 100%;">
    <div>
        <h3><i class="bi bi-person-gear"></i> Editar Usuario</h3>
        <p class="fw-light">Modificar información de: <strong><?= h($user->name) ?></strong></p>
    </div>

    <?= $this->Flash->render() ?>

    <?= $this->Form->create($user, ['type' => 'file']) ?>
    <div class="bg-white rounded shadow-sm p-5">
        <div class="mb-5">
            <h3 class="fw-normal">Foto de Perfil</h3>
            <div class="d-flex align-items-center justify-content-center gap-5">
                <div >
                    <?= $this->User->profileImageTag($user, ['width' => '100', 'height' => '100', 'class' => 'rounded-circle object-fit-cover shadow']) ?>
                </div>
                <div class="">
                    <?= $this->Form->label('profile_image_upload', 'Cambiar foto de perfil') ?>
                    <?= $this->Form->file('profile_image_upload', [
                        'accept' => 'image/jpeg,image/png,image/gif,image/webp',
                        'class' => 'form-control'
                    ]) ?>
                    <small class="fw-light small">Formatos permitidos: JPG, PNG, GIF, WEBP. Tamaño máximo: 2MB</small>
                </div>
            </div>
        </div>

        <div class="form-section mb-5">
            <h3 class="fw-normal">Información Personal</h3>

            <div class="d-flex align-items-center justify-content-between gap-4 mb-3">
                <div class="form-group w-100">
                    <?= $this->Form->label('first_name', 'Nombre *') ?>
                    <?= $this->Form->text('first_name', [
                        'class' => 'form-control',
                        'placeholder' => 'Ej: Juan'
                    ]) ?>
                </div>

                <div class="form-group flex-1 w-100">
                    <?= $this->Form->label('last_name', 'Apellido *') ?>
                    <?= $this->Form->text('last_name', [
                        'class' => 'form-control',
                        'placeholder' => 'Ej: Pérez'
                    ]) ?>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between gap-4 mb-3">
                <div class="form-group w-100">
                    <?= $this->Form->label('email', 'Correo Electrónico *') ?>
                    <?= $this->Form->email('email', [
                        'class' => 'form-control',
                        'placeholder' => 'ejemplo@correo.com'
                    ]) ?>
                </div>

                <div class="form-group w-100">
                    <?= $this->Form->label('organization_id', 'Organización') ?>
                    <?= $this->Form->select('organization_id', $organizations,[
                        'class' => 'form-control',
                        'empty' => '-- Sin organización --'
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="form-section mb-5">
            <h3 class="fw-normal">Configuración de Cuenta</h3>

            <div class="form-row d-flex align-items-center justify-content-between mb-3 gap-4">
                <div class="form-group w-100">
                    <?= $this->Form->label('role', 'Rol *') ?>
                    <?= $this->Form->select('role', [
                        'admin' => 'Administrador',
                        'agent' => 'Agente',
                        'servicio_cliente' => 'Servicio al Cliente',
                        'compras' => 'Compras',
                        'requester' => 'Solicitante'
                    ], [
                        'value' => h($user->role),
                        'class' => 'form-control'
                    ]) ?>
                    <small class="fw-light">Define los permisos del usuario</small>
                </div>

                <div class="form-group w-100">
                    <label>
                        <?= $this->Form->checkbox('is_active', ['id' => 'is_active']) ?>
                        Cuenta activa
                    </label>
                    <small class="fw-light">(Los usuarios inactivos no pueden iniciar sesión)</small>
                </div>
            </div>
        </div>

        <div class="form-section mb-5">
            <h3 class="fw-normal m-0">Cambiar Contraseña</h3>
            <p class="fw-light">Deja en blanco si no deseas cambiar la contraseña</p>

            <div class="d-flex align-items-center justify-content-between gap-4">
                <div class="form-group w-100">
                    <?= $this->Form->label('new_password', 'Nueva Contraseña') ?>
                    <?= $this->Form->password('new_password', [
                        'class' => 'form-control',
                        'value' => '',
                        'autocomplete' => 'new-password'
                    ]) ?>
                </div>

                <div class="form-group w-100">
                    <?= $this->Form->label('confirm_password', 'Confirmar Contraseña') ?>
                    <?= $this->Form->password('confirm_password', [
                        'class' => 'form-control',
                        'value' => '',
                        'autocomplete' => 'new-password'
                    ]) ?>
                </div>
            </div>
            <small>Mínimo 6 caracteres</small>
        </div>

        <div class="form-actions">
            <?= $this->Form->button('<i class="bi bi-arrow-up-circle"></i> Guardar Cambios', [
                'class' => 'btn btn-success', 'escapeTitle' => false
            ]) ?>
            <?= $this->Html->link('Cancelar', ['action' => 'users'], [
                'class' => 'btn btn-secondary'
            ]) ?>
        </div>
    </div>
    <?= $this->Form->end() ?>
</div>
