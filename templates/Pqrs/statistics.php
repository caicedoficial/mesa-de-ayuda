<?php
/**
 * @var \App\View\AppView $this
 * @var int $totalPqrs
 * @var int $totalResolved
 * @var int $totalPending
 * @var int $totalUnassigned
 * @var array $statusCounts
 * @var array $typeCounts
 * @var array $priorityCounts
 * @var array $channelCounts
 * @var int $recentPqrs
 * @var int $resolvedInPeriod
 * @var float $avgResolutionDays
 * @var float $avgResolutionHours
 * @var array $topAgents
 * @var array $chartLabels
 * @var array $chartData
 * @var string $dateFrom
 * @var string $dateTo
 */
$this->assign('title', 'Estadísticas PQRS');
?>

<div class="py-4 px-5" style="max-width: 1100px; margin: auto; width: 100%;">
    <div class="mb-4">
        <h2 class="fw-normal"><i class="bi bi-bar-chart me-2 text-success"></i>Estadísticas</h2>
        <p class="text-muted fw-light">Vista general del sistema de tickets</p>
    </div>

    <!-- Date Range Filter
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <?= $this->Form->create(null, ['type' => 'get', 'class' => 'row g-3 align-items-end']) ?>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Fecha desde</label>
                    <?= $this->Form->date('date_from', [
                        'value' => $dateFrom,
                        'class' => 'form-control form-control-sm'
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Fecha hasta</label>
                    <?= $this->Form->date('date_to', [
                        'value' => $dateTo,
                        'class' => 'form-control form-control-sm'
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= $this->Form->button('<i class="bi bi-filter"></i> Filtrar', [
                        'class' => 'btn btn-primary btn-sm w-100',
                        'escapeTitle' => false
                    ]) ?>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
    -->

    <!-- KPI Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-inbox-fill text-primary" style="font-size: 2.5rem;"></i>
                    <h3 class="fw-bold mt-2 mb-0"><?= number_format($totalPqrs) ?></h3>
                    <p class="text-muted mb-0 fw-light">Total PQRS</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 2.5rem;"></i>
                    <h3 class="fw-bold mt-2 mb-0"><?= number_format($totalResolved) ?></h3>
                    <p class="text-muted mb-0 fw-light">Resueltas</p>
                    <?php if ($totalPqrs > 0): ?>
                        <small class="text-muted"><?= round(($totalResolved / $totalPqrs) * 100, 1) ?>%</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-hourglass-split text-warning" style="font-size: 2.5rem;"></i>
                    <h3 class="fw-bold mt-2 mb-0"><?= number_format($totalPending) ?></h3>
                    <p class="text-muted mb-0 fw-light">Pendientes</p>
                    <?php if ($totalPqrs > 0): ?>
                        <small class="text-muted"><?= round(($totalPending / $totalPqrs) * 100, 1) ?>%</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-person-x-fill text-danger" style="font-size: 2.5rem;"></i>
                    <h3 class="fw-bold mt-2 mb-0"><?= number_format($totalUnassigned) ?></h3>
                    <p class="text-muted mb-0 fw-light">Sin Asignar</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary KPIs -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-week text-info" style="font-size: 2rem;"></i>
                    <h4 class="fw-bold mt-2 mb-0"><?= number_format($recentPqrs) ?></h4>
                    <p class="text-muted mb-0 small">PQRS últimos 7 días</p>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-check text-success" style="font-size: 2rem;"></i>
                    <h4 class="fw-bold mt-2 mb-0"><?= number_format($resolvedInPeriod) ?></h4>
                    <p class="text-muted mb-0 small">Resueltas en período</p>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-clock-history text-primary" style="font-size: 2rem;"></i>
                    <h4 class="fw-bold mt-2 mb-0"><?= $avgResolutionDays ?> días</h4>
                    <p class="text-muted mb-0 small">Tiempo promedio resolución</p>
                    <small class="text-muted">(<?= $avgResolutionHours ?> horas)</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Daily PQRS Chart -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-graph-up"></i> PQRS Creadas (Últimos 30 días)</h5>
                </div>
                <div class="card-body">
                    <canvas id="dailyChart" height="80"></canvas>
                </div>
            </div>
        </div>

        <!-- Status Distribution -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-pie-chart"></i> Por Estado</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Distribution Charts -->
    <div class="row mb-4">
        <!-- Type Distribution -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-list-ul"></i> Distribución por Tipo</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($typeCounts as $type => $count): ?>
                        <?php
                        $typeLabels = [
                            'peticion' => 'Peticiones',
                            'queja' => 'Quejas',
                            'reclamo' => 'Reclamos',
                            'sugerencia' => 'Sugerencias'
                        ];
                        $typeColors = [
                            'peticion' => 'primary',
                            'queja' => 'warning',
                            'reclamo' => 'danger',
                            'sugerencia' => 'success'
                        ];
                        $label = $typeLabels[$type] ?? $type;
                        $color = $typeColors[$type] ?? 'secondary';
                        $percentage = $totalPqrs > 0 ? round(($count / $totalPqrs) * 100, 1) : 0;
                        ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-semibold"><?= h($label) ?></span>
                                <span class="text-muted"><?= number_format($count) ?> (<?= $percentage ?>%)</span>
                            </div>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-<?= $color ?>" role="progressbar"
                                     style="width: <?= $percentage ?>%;"
                                     aria-valuenow="<?= $percentage ?>"
                                     aria-valuemin="0"
                                     aria-valuemax="100">
                                    <?= $percentage ?>%
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Priority Distribution -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-exclamation-triangle"></i> Distribución por Prioridad</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($priorityCounts as $priority => $count): ?>
                        <?php
                        $priorityLabels = [
                            'baja' => '🟢 Baja',
                            'media' => '🟡 Media',
                            'alta' => '🟠 Alta',
                            'urgente' => '🔴 Urgente'
                        ];
                        $priorityColors = [
                            'baja' => 'success',
                            'media' => 'warning',
                            'alta' => 'danger',
                            'urgente' => 'dark'
                        ];
                        $label = $priorityLabels[$priority] ?? $priority;
                        $color = $priorityColors[$priority] ?? 'secondary';
                        $percentage = $totalPqrs > 0 ? round(($count / $totalPqrs) * 100, 1) : 0;
                        ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-semibold"><?= h($label) ?></span>
                                <span class="text-muted"><?= number_format($count) ?> (<?= $percentage ?>%)</span>
                            </div>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-<?= $color ?>" role="progressbar"
                                     style="width: <?= $percentage ?>%;"
                                     aria-valuenow="<?= $percentage ?>"
                                     aria-valuemin="0"
                                     aria-valuemax="100">
                                    <?= $percentage ?>%
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Tables Row -->
    <div class="row">
        <!-- Top Agents -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-trophy"></i> Top 5 Agentes</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0">Posición</th>
                                    <th class="border-0">Agente</th>
                                    <th class="border-0 text-end">Resueltas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($topAgents)): ?>
                                    <?php foreach ($topAgents as $index => $agent): ?>
                                        <tr>
                                            <td>
                                                <?php if ($index === 0): ?>
                                                    <i class="bi bi-trophy-fill text-warning"></i> #1
                                                <?php elseif ($index === 1): ?>
                                                    <i class="bi bi-trophy-fill text-secondary"></i> #2
                                                <?php elseif ($index === 2): ?>
                                                    <i class="bi bi-trophy-fill" style="color: #cd7f32;"></i> #3
                                                <?php else: ?>
                                                    #<?= $index + 1 ?>
                                                <?php endif; ?>
                                            </td>
                                            <td class="fw-semibold"><?= h($agent->assignee->name) ?></td>
                                            <td class="text-end">
                                                <span class="badge bg-success"><?= number_format($agent->count) ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">
                                            No hay datos disponibles
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Channel Distribution -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-signpost-split"></i> Por Canal</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0">Canal</th>
                                    <th class="border-0 text-end">Cantidad</th>
                                    <th class="border-0 text-end">Porcentaje</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($channelCounts)): ?>
                                    <?php foreach ($channelCounts as $channel): ?>
                                        <?php $percentage = $totalPqrs > 0 ? round(($channel->count / $totalPqrs) * 100, 1) : 0; ?>
                                        <tr>
                                            <td class="text-uppercase fw-semibold">
                                                <i class="bi bi-circle-fill text-primary" style="font-size: 8px;"></i>
                                                <?= h($channel->channel) ?>
                                            </td>
                                            <td class="text-end"><?= number_format($channel->count) ?></td>
                                            <td class="text-end">
                                                <span class="badge bg-primary"><?= $percentage ?>%</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">
                                            No hay datos disponibles
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Daily PQRS Chart
const dailyCtx = document.getElementById('dailyChart').getContext('2d');
new Chart(dailyCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [{
            label: 'PQRS Creadas',
            data: <?= json_encode($chartData) ?>,
            borderColor: 'rgb(13, 110, 253)',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});

// Status Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Nuevo', 'En Revisión', 'En Proceso', 'Resuelto', 'Cerrado'],
        datasets: [{
            data: [
                <?= $statusCounts['nuevo'] ?>,
                <?= $statusCounts['en_revision'] ?>,
                <?= $statusCounts['en_proceso'] ?>,
                <?= $statusCounts['resuelto'] ?>,
                <?= $statusCounts['cerrado'] ?>
            ],
            backgroundColor: [
                'rgb(255, 193, 7)',   // warning - nuevo
                'rgb(13, 202, 240)',  // info - en_revision
                'rgb(13, 110, 253)',  // primary - en_proceso
                'rgb(25, 135, 84)',   // success - resuelto
                'rgb(108, 117, 125)'  // secondary - cerrado
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>
