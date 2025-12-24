<?php
/**
 * @var \App\View\AppView $this
 * @var int $totalTickets
 * @var array $ticketsByStatus
 * @var array $ticketsByPriority
 * @var int $recentTickets
 * @var int $recentResolved
 * @var int $unassignedTickets
 * @var int $activeAgents
 * @var \Cake\ORM\ResultSet $ticketsByAgent
 * @var object $avgResponseTime
 * @var object $avgResolutionTime
 * @var array $ticketsPerDay
 * @var \Cake\ORM\ResultSet $topRequesters
 * @var int $totalComments
 * @var int $publicComments
 * @var int $internalComments
 * @var float $responseRate
 * @var float $resolutionRate
 */
$this->assign('title', 'Estadísticas de Tickets');
?>

<div class="py-4 px-5" style="max-width: 1100px; margin: 0 auto; width: 100%;">
    <div class="mb-5">
        <h2 class="fw-normal"><i class="bi bi-bar-chart me-2 text-success"></i>Estadísticas</h2>
        <p class="text-muted fw-light">Vista general del sistema de tickets</p>
    </div>

    <!-- Date Range Filter
    <div class="card rounded-0 border-0 mb-4">
        <div class="card-body p-0">
            <form method="get" action="<?= $this->Url->build(['action' => 'dashboard']) ?>" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Período</label>
                    <select name="range" class="form-select rounded-0" id="date-range-select">
                        <option value="all" <?= $dateRange === 'all' ? 'selected' : '' ?>>Todo el tiempo</option>
                        <option value="today" <?= $dateRange === 'today' ? 'selected' : '' ?>>Hoy</option>
                        <option value="week" <?= $dateRange === 'week' ? 'selected' : '' ?>>Últimos 7 días</option>
                        <option value="month" <?= $dateRange === 'month' ? 'selected' : '' ?>>Últimos 30 días</option>
                        <option value="custom" <?= $dateRange === 'custom' ? 'selected' : '' ?>>Rango personalizado</option>
                    </select>
                </div>
                <div class="col-md-3" id="start-date-field" style="display: <?= $dateRange === 'custom' ? 'block' : 'none' ?>;">
                    <label class="form-label fw-bold">Desde</label>
                    <input type="date" name="start_date" class="form-control rounded-0" value="<?= h($startDate ?? '') ?>">
                </div>
                <div class="col-md-3" id="end-date-field" style="display: <?= $dateRange === 'custom' ? 'block' : 'none' ?>;">
                    <label class="form-label fw-bold">Hasta</label>
                    <input type="date" name="end_date" class="form-control rounded-0" value="<?= h($endDate ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary rounded-0 w-100">
                        <i class="bi bi-funnel me-1"></i> Aplicar Filtro
                    </button>
                </div>
            </form>
        </div>
    </div>
    -->

    <!-- Summary Cards -->
    <div class="row mb-5">
        <!-- Total Tickets -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-ticket-perforated text-primary" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2 mb-0"><?= number_format($totalTickets) ?></h3>
                    <p class="text-muted mb-0 fw-light">Total Tickets</p>
                </div>
            </div>
        </div>

        <!-- Recent Tickets (7 days) -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-clock-history text-info" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2 mb-0"><?= number_format($recentTickets) ?></h3>
                    <p class="text-muted mb-0 fw-light">Últimos 7 días</p>
                </div>
            </div>
        </div>

        <!-- Unassigned Tickets -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-person-x-fill text-danger" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2 mb-0"><?= number_format($unassignedTickets) ?></h3>
                    <p class="text-muted mb-0 fw-light">Sin Asignar</p>
                </div>
            </div>
        </div>

        <!-- Active Agents -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-people text-success" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2 mb-0"><?= number_format($activeAgents) ?></h3>
                    <p class="text-muted mb-0 fw-light">Agentes Activos</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="row mb-5 bg-light rounded-0 p-3">
        <!-- Response Rate -->
        <div class="col-md-3">
            <div class="card rounded-0 border-0 bg-transparent">
                <div class="card-body text-center">
                    <div class="progress mb-2" style="height: 10px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $responseRate ?>%"></div>
                    </div>
                    <h4 class="mb-0 fw-normal"><?= $responseRate ?>%</h4>
                    <p class="text-muted mb-0 small fw-light">Tasa de Respuesta</p>
                </div>
            </div>
        </div>

        <!-- Resolution Rate -->
        <div class="col-md-3">
            <div class="card rounded-0 border-0 bg-transparent">
                <div class="card-body text-center">
                    <div class="progress mb-2" style="height: 10px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $resolutionRate ?>%"></div>
                    </div>
                    <h4 class="mb-0 fw-normal"><?= $resolutionRate ?>%</h4>
                    <p class="text-muted mb-0 small fw-light">Tasa de Resolución</p>
                </div>
            </div>
        </div>

        <!-- Avg Response Time -->
        <div class="col-md-3">
            <div class="card rounded-0 border-0 bg-transparent">
                <div class="card-body text-center">
                    <i class="bi bi-hourglass-split text-info"></i>
                    <h4 class="mb-0 fw-normal">
                        <?php if ($avgResponseTime && $avgResponseTime->avg_hours): ?>
                            <?= round($avgResponseTime->avg_hours, 1) ?>h
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </h4>
                    <p class="text-muted mb-0 small fw-light">Tiempo Prom. Respuesta</p>
                </div>
            </div>
        </div>

        <!-- Avg Resolution Time -->
        <div class="col-md-3">
            <div class="card rounded-0 border-0 bg-transparent">
                <div class="card-body text-center">
                    <i class="bi bi-check2-circle text-success"></i>
                    <h4 class="mb-0 fw-normal">
                        <?php if ($avgResolutionTime && $avgResolutionTime->avg_hours): ?>
                            <?= round($avgResolutionTime->avg_hours, 1) ?>h
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </h4>
                    <p class="text-muted mb-0 small fw-light">Tiempo Prom. Resolución</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-5 gap-3">
        <!-- Tickets by Status - Donut Chart -->
        <div class="col-md-5 mx-auto">
            <div class="card rounded-3 overflow-hidden shadow border-0">
                <div class="card-header border-0" style="background: #00A85E;">
                    <h5 class="mb-0 text-center text-white fw-light"><i class="bi bi-pie-chart me-2"></i>Tickets por Estado</h5>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <canvas id="statusChart" style="max-height: 250px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Tickets by Priority - Donut Chart -->
        <div class="col-md-5 mx-auto">
            <div class="card rounded-3 overflow-hidden shadow border-0">
                <div class="card-header border-0" style="background: #273244;">
                    <h5 class="mb-0 text-center text-white fw-light"><i class="bi bi-pie-chart-fill me-2"></i>Tickets por Prioridad</h5>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <canvas id="priorityChart" style="max-height: 250px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-5">
        <!-- Top Agents -->
        <div class="col-md-6">
            <div class="card rounded border-0 p-3 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0 fw-light"><i class="bi bi-trophy me-2"></i>Top Agentes (Tickets Asignados)</h5>
                </div>
                <div class="card-body">
                    <?php if ($ticketsByAgent->count() > 0): ?>
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Agente</th>
                                    <th class="text-end">Tickets</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $rank = 1; ?>
                                <?php foreach ($ticketsByAgent as $agent): ?>
                                    <tr>
                                        <td>
                                            <?php if ($rank === 1): ?>
                                                <i class="bi bi-trophy-fill text-warning"></i>
                                            <?php elseif ($rank === 2): ?>
                                                <i class="bi bi-trophy-fill text-secondary"></i>
                                            <?php elseif ($rank === 3): ?>
                                                <i class="bi bi-trophy-fill" style="color: #cd7f32;"></i>
                                            <?php else: ?>
                                                <?= $rank ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= h($agent->agent_name) ?></td>
                                        <td class="text-end">
                                            <span class="badge bg-primary"><?= h($agent->count) ?></span>
                                        </td>
                                    </tr>
                                    <?php $rank++; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-muted text-center py-3">No hay tickets asignados aún</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Top Requesters -->
        <div class="col-md-6">
            <div class="card rounded border-0 p-3 shadow-sm h-100">
                <div class="card-header border-0 bg-white">
                    <h5 class="mb-0 fw-light"><i class="bi bi-person-lines-fill me-2"></i>Solicitantes Más Activos</h5>
                </div>
                <div class="card-body border-0">
                    <?php if ($topRequesters->count() > 0): ?>
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th class="fw-bold">No.</th>
                                    <th class="fw-bold">Solicitante</th>
                                    <th class="text-end fw-bold">Tickets</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $rank = 1; ?>
                                <?php foreach ($topRequesters as $requester): ?>
                                    <tr>
                                        <td ><?= $rank ?></td>
                                        <td class="d-flex flex-column">
                                            <?= h($requester->requester_name) ?>
                                            <small class="text-muted fw-light"><?= h($requester->requester_email) ?></small>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-info"><?= h($requester->count) ?></span>
                                        </td>
                                    </tr>
                                    <?php $rank++; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-muted text-center py-3">No hay datos disponibles</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Chart -->
    <div class="row g-3 mb-5">
        <div class="col-12">
            <div class="card rounded border-0 p-3 shadow">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0 fw-light"><i class="bi bi-graph-up me-2"></i>Actividad (Últimos 30 días)</h5>
                </div>
                <div class="card-body">
                    <canvas id="activityChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Comments Stats -->
    <div class="row g-3">
        <div class="col-md-4 mb-3">
            <div class="card border-0">
                <div class="card-body text-center">
                    <i class="bi bi-chat-dots text-primary" style="font-size: 2rem;"></i>
                    <h4 class="mt-2 mb-0"><?= number_format($totalComments) ?></h4>
                    <p class="text-muted mb-0">Total Comentarios</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0">
                <div class="card-body text-center">
                    <i class="bi bi-eye text-success" style="font-size: 2rem;"></i>
                    <h4 class="mt-2 mb-0"><?= number_format($publicComments) ?></h4>
                    <p class="text-muted mb-0">Comentarios Públicos</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0">
                <div class="card-body text-center">
                    <i class="bi bi-eye-slash text-warning" style="font-size: 2rem;"></i>
                    <h4 class="mt-2 mb-0"><?= number_format($internalComments) ?></h4>
                    <p class="text-muted mb-0">Comentarios Internos</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<!-- Chart.js Datalabels Plugin -->
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
<script>
// Activity Chart
const ctx = document.getElementById('activityChart').getContext('2d');
const ticketsData = <?= json_encode($ticketsPerDay) ?>;

// Prepare data for last 30 days
const dates = [];
const counts = [];
const today = new Date();

for (let i = 29; i >= 0; i--) {
    const date = new Date(today);
    date.setDate(date.getDate() - i);
    const dateStr = date.toISOString().split('T')[0];
    dates.push(dateStr);
    counts.push(ticketsData[dateStr] || 0);
}

new Chart(ctx, {
    type: 'line',
    data: {
        labels: dates.map(d => {
            const date = new Date(d);
            return date.toLocaleDateString('es-ES', { month: 'short', day: 'numeric' });
        }),
        datasets: [{
            label: 'Tickets Creados',
            data: counts,
            borderColor: 'rgba(205, 106, 21, 1)',
            backgroundColor: 'rgba(205, 107, 21, 0.2)',
            tension: 0.3,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            },
            datalabels: {
                display: false // Disable datalabels for line chart
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Status Donut Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusData = <?= json_encode($ticketsByStatus) ?>;
const statusLabels = ['Nuevo', 'Abierto', 'Pendiente', 'Resuelto'];
const statusValues = [
    statusData['nuevo'] || 0,
    statusData['abierto'] || 0,
    statusData['pendiente'] || 0,
    statusData['resuelto'] || 0
];
const statusColors = ['#ffc107', '#dc3545', '#0d6efd', '#198754'];

new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: statusLabels,
        datasets: [{
            data: statusValues,
            backgroundColor: statusColors,
            borderWidth: 3,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 14,
                    font: {
                        size: 14,
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((context.parsed / total) * 100).toFixed(1);
                        return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                    }
                }
            },
            datalabels: {
                color: '#fff',
                font: {
                    weight: 'bold',
                    size: 14
                },
                formatter: function(value, context) {
                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                    const percentage = ((value / total) * 100).toFixed(1);
                    // Only show label if percentage is greater than 5%
                    return percentage > 5 ? percentage + '%' : '';
                }
            }
        }
    }
});

// Priority Donut Chart
const priorityCtx = document.getElementById('priorityChart').getContext('2d');
const priorityData = <?= json_encode($ticketsByPriority) ?>;
const priorityLabels = ['Urgente', 'Alta', 'Media', 'Baja'];
const priorityValues = [
    priorityData['urgente'] || 0,
    priorityData['alta'] || 0,
    priorityData['media'] || 0,
    priorityData['baja'] || 0
];
const priorityColors = ['#dc3545', '#ffc107', '#0dcaf0', '#6c757d'];

new Chart(priorityCtx, {
    type: 'doughnut',
    data: {
        labels: priorityLabels,
        datasets: [{
            data: priorityValues,
            backgroundColor: priorityColors,
            borderWidth: 3,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 14,
                    font: {
                        size: 14
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((context.parsed / total) * 100).toFixed(1);
                        return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                    }
                }
            },
            datalabels: {
                color: '#fff',
                font: {
                    weight: 'bold',
                    size: 14
                },
                formatter: function(value, context) {
                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                    const percentage = ((value / total) * 100).toFixed(1);
                    // Only show label if percentage is greater than 5%
                    return percentage > 5 ? percentage + '%' : '';
                }
            }
        }
    }
});

// Date Range Filter Toggle
document.getElementById('date-range-select').addEventListener('change', function() {
    const startField = document.getElementById('start-date-field');
    const endField = document.getElementById('end-date-field');

    if (this.value === 'custom') {
        startField.style.display = 'block';
        endField.style.display = 'block';
    } else {
        startField.style.display = 'none';
        endField.style.display = 'none';
    }
});
</script>
