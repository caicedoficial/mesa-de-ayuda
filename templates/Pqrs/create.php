<!DOCTYPE html>
<html lang="es">
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PQRS - Sistema de Peticiones, Quejas, Reclamos y Sugerencias</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Custom Styles -->
    <?= $this->Html->css(['styles']) ?>

    <style>
        .pqrs-header {
            background: linear-gradient(135deg, #ce4801ff 0%, #CD6A15 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }
        .form-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .required-label::after {
            content: " *";
            color: #dc3545;
        }
    </style>
</head>
<body class="overflow-auto">
    <!-- Header -->
    <div class="pqrs-header">
        <div class="container">
            <div class="text-center">
                <h1 class="mb-3">
                    <i class="bi bi-chat-square-text-fill"></i>
                    PQRS
                </h1>
                <h4 class="mb-0">Peticiones, Quejas, Reclamos y Sugerencias</h4>
                <p class="mt-3 mb-0">Estamos aquí para escucharte. Tu opinión es importante para nosotros.</p>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <?= $this->Flash->render() ?>

        <!-- Loading Spinner -->
        <?= $this->element('loading_spinner', ['message' => 'Enviando PQRS...']) ?>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Information Card -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-info-circle"></i> Información</h5>
                        <p class="card-text mb-0">
                            Complete el siguiente formulario para enviar su petición, queja, reclamo o sugerencia.
                            Recibirá un número de seguimiento para consultar el estado de su solicitud.
                        </p>
                    </div>
                </div>

                <!-- PQRS Form -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <?= $this->Form->create($pqrs, [
                            'type' => 'file',
                            'class' => 'needs-validation',
                            'novalidate' => true
                        ]) ?>

                        <!-- Tipo de PQRS -->
                        <div class="form-section">
                            <h5 class="mb-3"><i class="bi bi-bookmark-fill"></i> Tipo de Solicitud</h5>

                            <div class="mb-3">
                                <label class="form-label required-label">Tipo de PQRS</label>
                                <div class="row g-3">
                                    <?php foreach ($types as $key => $label): ?>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <?= $this->Form->radio('type', [
                                                    ['value' => $key, 'text' => $label, 'class' => 'form-check-input']
                                                ], [
                                                    'required' => true,
                                                    'hiddenField' => false
                                                ]) ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <small class="form-text text-muted">
                                    <strong>Petición:</strong> Solicitud de información o servicio<br>
                                    <strong>Queja:</strong> Insatisfacción con un servicio<br>
                                    <strong>Reclamo:</strong> Exigencia de un derecho o corrección<br>
                                    <strong>Sugerencia:</strong> Propuesta de mejora
                                </small>
                            </div>

                            <div class="mb-3">
                                <label for="priority" class="form-label">Prioridad</label>
                                <?= $this->Form->select('priority', $priorities, [
                                    'class' => 'form-select',
                                    'id' => 'priority',
                                    'default' => 'media'
                                ]) ?>
                            </div>
                        </div>

                        <!-- Datos Personales -->
                        <div class="form-section">
                            <h5 class="mb-3"><i class="bi bi-person-fill"></i> Datos Personales</h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="requester-name" class="form-label required-label">Nombre Completo</label>
                                    <?= $this->Form->text('requester_name', [
                                        'class' => 'form-control',
                                        'id' => 'requester-name',
                                        'placeholder' => 'Ingrese su nombre completo',
                                        'required' => true
                                    ]) ?>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="requester-id-number" class="form-label">Cédula/DNI/ID</label>
                                    <?= $this->Form->text('requester_id_number', [
                                        'class' => 'form-control',
                                        'id' => 'requester-id-number',
                                        'placeholder' => 'Número de identificación'
                                    ]) ?>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="requester-email" class="form-label required-label">Correo Electrónico</label>
                                    <?= $this->Form->email('requester_email', [
                                        'class' => 'form-control',
                                        'id' => 'requester-email',
                                        'placeholder' => 'ejemplo@correo.com',
                                        'required' => true
                                    ]) ?>
                                    <small class="form-text text-muted">Recibirá notificaciones en este correo</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="requester-phone" class="form-label">Teléfono</label>
                                    <?= $this->Form->text('requester_phone', [
                                        'class' => 'form-control',
                                        'id' => 'requester-phone',
                                        'placeholder' => '+57 300 123 4567'
                                    ]) ?>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="requester-address" class="form-label">Dirección</label>
                                    <?= $this->Form->text('requester_address', [
                                        'class' => 'form-control',
                                        'id' => 'requester-address',
                                        'placeholder' => 'Calle, número, barrio'
                                    ]) ?>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="requester-city" class="form-label">Ciudad</label>
                                    <?= $this->Form->text('requester_city', [
                                        'class' => 'form-control',
                                        'id' => 'requester-city',
                                        'placeholder' => 'Ciudad'
                                    ]) ?>
                                </div>
                            </div>
                        </div>

                        <!-- Detalles de la Solicitud -->
                        <div class="form-section">
                            <h5 class="mb-3"><i class="bi bi-file-text-fill"></i> Detalles de la Solicitud</h5>

                            <div class="mb-3">
                                <label for="subject" class="form-label required-label">Asunto</label>
                                <?= $this->Form->text('subject', [
                                    'class' => 'form-control',
                                    'id' => 'subject',
                                    'placeholder' => 'Resuma brevemente su solicitud',
                                    'required' => true,
                                    'maxlength' => 255
                                ]) ?>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label required-label">Descripción Detallada</label>
                                <?= $this->Form->textarea('description', [
                                    'class' => 'form-control',
                                    'id' => 'description',
                                    'rows' => 6,
                                    'placeholder' => 'Describa detalladamente su petición, queja, reclamo o sugerencia. Incluya toda la información relevante.',
                                    'required' => true
                                ]) ?>
                                <small class="form-text text-muted">Mínimo 20 caracteres</small>
                            </div>

                            <div class="mb-3">
                                <label for="attachments" class="form-label">
                                    <i class="bi bi-paperclip"></i> Archivos Adjuntos (Opcional)
                                </label>
                                <?= $this->Form->file('attachments[]', [
                                    'class' => 'form-control',
                                    'id' => 'attachments',
                                    'multiple' => true,
                                    'accept' => '.pdf,.doc,.docx,.jpg,.jpeg,.png,.zip'
                                ]) ?>
                                <small class="form-text text-muted">
                                    Formatos permitidos: PDF, DOC, DOCX, JPG, PNG, ZIP. Máximo 10MB por archivo.
                                </small>
                            </div>
                        </div>

                        <!-- Privacy Notice -->
                        <div class="alert alert-info">
                            <i class="bi bi-shield-check"></i> <strong>Protección de Datos:</strong>
                            Sus datos personales serán tratados de acuerdo con nuestra política de privacidad
                            y únicamente serán utilizados para dar respuesta a su solicitud.
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2 mt-4">
                            <?= $this->Form->button(
                                '<i class="bi bi-send-fill"></i> Enviar PQRS',
                                [
                                    'type' => 'submit',
                                    'class' => 'btn btn-success btn-lg',
                                    'escapeTitle' => false
                                ]
                            ) ?>
                        </div>

                        <?= $this->Form->end() ?>
                    </div>
                </div>

                <!-- Footer Info -->
                <div class="text-center mt-4 text-muted">
                    <p class="mb-1">
                        <i class="bi bi-clock"></i> Horario de atención: Lunes a Viernes, 8:00 AM - 5:00 PM
                    </p>
                    <p>
                        <i class="bi bi-envelope"></i> Para consultas: soporte@empresa.com
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Loading Spinner -->
    <?= $this->Html->script('loading-spinner') ?>

    <!-- Form Validation -->
    <script>
        // Bootstrap form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    } else {
                        // Mostrar spinner si el formulario es válido
                        LoadingSpinner.show('Enviando PQRS...');
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()

        // Character counter for description
        document.getElementById('description').addEventListener('input', function() {
            const minLength = 20;
            const currentLength = this.value.length;

            if (currentLength < minLength) {
                this.setCustomValidity('La descripción debe tener al menos ' + minLength + ' caracteres');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
