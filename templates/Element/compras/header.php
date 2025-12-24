<!-- Fixed Header -->
<div class="py-3 px-4 shadow-sm bg-white" style="border-radius: 8px;">
    <div class="d-flex justify-content-between gap-5 small">
        <div class="d-flex flex-column justify-content-between" style="min-width: 0; flex: 1;">
            <div class="marquee-container compra-subject-container" style="max-width: 600px;">
                <h1 class="fs-5 fw-semibold m-0 compra-subject-text"><?= h($compra->subject) ?></h1>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span><strong class="text-muted">Compra:</strong> <?= h($compra->compra_number) ?></span>
            </div>
        </div>
        <div class="d-flex flex-column justify-content-between">
            <span class="text-muted lh-1"><strong class="text-muted">Creado:</strong>
                <?= $this->TimeHuman->long($compra->created) ?></span>
            <?php if ($compra->resolved_at && in_array($compra->status, ['completado', 'rechazado'])): ?>
                <span class="text-success lh-1"><strong>Resuelto:</strong>
                    <?= $this->TimeHuman->long($compra->resolved_at) ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar marquee en el título de la compra
        if (typeof MarqueeText !== 'undefined') {
            MarqueeText.init('.compra-subject-container', '.compra-subject-text', {
                speed: 60,
                minDuration: 10,
                hoverDelay: 0,
                resetOnLeave: true
            });
        }
    });
</script>
