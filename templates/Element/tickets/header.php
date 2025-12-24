<!-- Fixed Header -->
<div class="py-3 px-4 shadow-sm bg-white" style="border-radius: 8px;">
    <div class="d-flex justify-content-between gap-5 small">
        <div class="d-flex flex-column justify-content-between" style="min-width: 0; flex: 1;">
            <div class="marquee-container ticket-subject-container" style="max-width: 600px;">
                <h1 class="fs-5 fw-semibold m-0 ticket-subject-text"><?= h($ticket->subject) ?></h1>
            </div>
            <span><strong class="text-muted">Ticket:</strong> <?= h($ticket->ticket_number) ?></span>
        </div>
        <div class="d-flex flex-column justify-content-between">
            <span class="text-muted lh-1"><strong class="text-muted">Creado:</strong>
                <?= $this->TimeHuman->long($ticket->created) ?></span>
            <?php if ($ticket->resolved_at && $ticket->status === 'resuelto'): ?>
                <span class="text-success lh-1"><strong>Resuelto:</strong>
                    <?= $this->TimeHuman->long($ticket->resolved_at) ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar marquee en el título del ticket
        if (typeof MarqueeText !== 'undefined') {
            MarqueeText.init('.ticket-subject-container', '.ticket-subject-text', {
                speed: 60,
                minDuration: 10,
                hoverDelay: 0,
                resetOnLeave: true
            });
        }
    });
</script>