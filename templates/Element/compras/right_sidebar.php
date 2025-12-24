<!-- Right Sidebar - User Info (with independent scroll) -->
<div class="sidebar-right d-flex flex-column p-3">
    <div class="bg-white p-3 text-start shadow-sm" style="border-radius: 8px;">
        <div class="avatar-large text-white rounded-circle d-flex align-items-center justify-content-center fw-bold mb-2"
            style="width: 60px; height: 60px; font-size: 28px; background-color: #CD6A15">
            <?= $this->User->initials($compra->requester->name ?? 'N/A') ?>
        </div>
        <div class="fw-semibold"><?= h($compra->requester->name ?? 'N/A') ?></div>
        <small class="text-muted"><?= h($compra->requester->email ?? 'N/A') ?></small>
    </div>

    <div class="sidebar-scroll flex-grow-1 overflow-auto p-3 my-3">
        <section class="mb-3">
            <h3 class="fs-6 mb-3">Información del Solicitante</h3>

            <?php if ($compra->requester->phone ?? null): ?>
                <div class="mb-2">
                    <label class="small text-uppercase text-muted fw-semibold mb-1">Teléfono</label>
                    <div class="small"><?= h($compra->requester->phone) ?></div>
                </div>
            <?php endif; ?>

            <?php if ($compra->requester->organization_id ?? null): ?>
                <div class="mb-2">
                    <label class="small text-uppercase text-muted fw-semibold mb-1">Organización</label>
                    <div class="small"><?= h($compra->requester->organization->name ?? 'N/A') ?></div>
                </div>
            <?php endif; ?>

            <div class="mb-2">
                <label class="small text-muted fw-semibold mb-1">Usuario desde:</label>
                <div class="small"><?= $this->TimeHuman->long($compra->requester->created) ?></div>
            </div>
        </section>

        <section class="mb-3">
            <h3 class="fs-6 fw-semibold mb-3">Historial de Cambios</h3>
            <!-- PERFORMANCE FIX: Lazy load history on scroll -->
            <div id="history-container" data-entity-type="compra" data-entity-id="<?= $compra->id ?>" data-loaded="false">
                <div id="history-loader" class="text-center py-3">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="small text-muted mt-2">Cargando historial...</p>
                </div>
                <div id="history-content" style="display: none;"></div>
            </div>
        </section>
    </div>
</div>
