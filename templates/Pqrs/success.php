
<div>
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-center mb-5" style="background: #00A85E; min-height: 150px;">
        <div class="d-flex align-items-center gap-2 bg-white ps-2 pe-3 py-2" style="border-radius: 8px;">
            <img class="my-auto" src="<?= $this->Url->build('img/logo.png') ?>" alt="Logo" height="50">
            <div class="gap-0 d-flex flex-column">
                <h2 class="fs-4 m-0">
                    Mesa de Ayuda
                </h2>
                <small class="m-0" style="font-size: 0.875rem; opacity: 0.9;">Servicio al Cliente</small>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-5 text-center">
                        <!-- Success Icon -->
                        <div class="mb-4">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                        </div>

                        <!-- Success Message -->
                        <h2 class="mb-4">¡Su PQRS ha sido recibida exitosamente!</h2>

                        <?php if ($pqrs): ?>
                            <p class="lead mb-4">
                                Su solicitud ha sido registrada con el siguiente número de seguimiento:
                            </p>

                            <!-- PQRS Number -->
                            <div class="text-secondary mb-4 fs-2">
                                <i class="bi bi-ticket-detailed"></i>
                                <?= h($pqrs->pqrs_number) ?>
                            </div>

                            <!-- PQRS Details -->
                            <div class="alert alert-success text-start mb-4 shadow-sm" style="border-radius: 8px;">
                                <h5 class="alert-heading"><i class="bi bi-info-circle-fill"></i> Detalles de su solicitud</h5>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <strong>Tipo:</strong>
                                        <?php
                                        $typeLabels = [
                                            'peticion' => 'Petición',
                                            'queja' => 'Queja',
                                            'reclamo' => 'Reclamo',
                                            'sugerencia' => 'Sugerencia'
                                        ];
                                        echo h($typeLabels[$pqrs->type] ?? $pqrs->type);
                                        ?>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <strong>Estado:</strong>
                                        <span class="px-3 py-1 bg-warning text-white fw-bold rounded">Nuevo</span>
                                    </div>
                                    <div class="col-12 mb-2">
                                        <strong>Asunto:</strong> <?= h($pqrs->subject) ?>
                                    </div>
                                    <div class="col-12">
                                        <strong>Fecha:</strong> <?= $pqrs->created->format('d/m/Y H:i') ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Next Steps -->
                            <div class="text-start mb-4">
                                <h5><i class="bi bi-list-check"></i> ¿Qué sigue ahora?</h5>
                                <hr>
                                <ol class="mb-0">
                                    <li class="mb-2">
                                        Hemos enviado un correo de confirmación a <strong><?= h($pqrs->requester_email) ?></strong>
                                    </li>
                                    <li class="mb-2">
                                        Nuestro equipo revisará su solicitud y le responderá a la brevedad posible
                                    </li>
                                    <li class="mb-2">
                                        Recibirá notificaciones por correo electrónico sobre el estado de su PQRS
                                    </li>
                                    <li>
                                        Guarde su número de seguimiento: <strong><?= h($pqrs->pqrs_number) ?></strong>
                                    </li>
                                </ol>
                            </div>

                            <!-- Contact Info -->
                            <div class="mt-4 p-3 bg-light rounded">
                                <h6><i class="bi bi-telephone-fill"></i> ¿Necesita ayuda adicional?</h6>
                                <p class="mb-0">
                                    Horario de atención:<br>Lunes a Viernes, 8:00 AM - 5:00 PM<br>
                                    Correo electrónico: <a href="mailto:servicioalcliente@operadoracafetera.com">servicioalcliente@operadoracafetera.com</a>
                                </p>
                            </div>

                        <?php else: ?>
                            <p class="lead">
                                Gracias por contactarnos. Hemos recibido su solicitud y la procesaremos a la brevedad.
                            </p>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

</div>