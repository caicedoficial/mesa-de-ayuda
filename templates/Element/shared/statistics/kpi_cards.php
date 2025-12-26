<?php
/**
 * KPI Cards - Reusable statistics cards for all modules
 *
 * @var int $total Total entities
 * @var int $recentCount Recent count (7 days)
 * @var int $unassignedCount Unassigned count
 * @var int $activeAgentsCount Active agents
 * @var string $entityType 'ticket', 'pqrs', or 'compra'
 * @var array|null $slaMetrics SLA metrics (Compras only)
 */

$entityLabels = [
    'ticket' => 'Tickets',
    'pqrs' => 'PQRS',
    'compra' => 'Compras',
];
$label = $entityLabels[$entityType] ?? 'Entidades';

$entityIcons = [
    'ticket' => 'bi-ticket-perforated',
    'pqrs' => 'bi-chat-left-text',
    'compra' => 'bi-cart-check',
];
$icon = $entityIcons[$entityType] ?? 'bi-inbox';
?>

<div class="row mb-5">
    <!-- Total -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="bi <?= $icon ?> text-primary" style="font-size: 2.5rem;"></i>
                <h3 class="mt-2 mb-0"><?= number_format($total) ?></h3>
                <p class="text-muted mb-0 fw-light">Total <?= h($label) ?></p>
            </div>
        </div>
    </div>

    <!-- Recent (7 days) -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="bi bi-clock-history text-info" style="font-size: 2.5rem;"></i>
                <h3 class="mt-2 mb-0"><?= number_format($recentCount) ?></h3>
                <p class="text-muted mb-0 fw-light">Últimos 7 días</p>
            </div>
        </div>
    </div>

    <!-- Unassigned -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="bi bi-person-x-fill text-danger" style="font-size: 2.5rem;"></i>
                <h3 class="mt-2 mb-0"><?= number_format($unassignedCount) ?></h3>
                <p class="text-muted mb-0 fw-light">Sin Asignar</p>
            </div>
        </div>
    </div>

    <!-- Active Agents OR SLA Compliance (for Compras) -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <?php if ($entityType === 'compra' && isset($slaMetrics)): ?>
                    <i class="bi bi-speedometer2 text-success" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2 mb-0"><?= h($slaMetrics['compliance_rate']) ?>%</h3>
                    <p class="text-muted mb-0 fw-light">Cumplimiento SLA</p>
                <?php else: ?>
                    <i class="bi bi-people text-success" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2 mb-0"><?= number_format($activeAgentsCount) ?></h3>
                    <p class="text-muted mb-0 fw-light">Agentes Activos</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
