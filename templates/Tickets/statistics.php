<?php
/**
 * Tickets Statistics Template
 *
 * @var \App\View\AppView $this
 * @var int $total
 * @var int $recentCount
 * @var int $unassignedCount
 * @var int $activeAgentsCount
 * @var array $statusDistribution
 * @var array $priorityDistribution
 * @var array $chartLabels
 * @var array $chartData
 * @var object|null $avgResponseTime
 * @var object|null $avgResolutionTime
 * @var float $responseRate
 * @var float $resolutionRate
 * @var \Cake\ORM\ResultSet $topAgents
 * @var \Cake\ORM\ResultSet $topRequesters
 * @var int $totalComments
 * @var int $publicComments
 * @var int $internalComments
 * @var array $filters
 */

$this->assign('title', 'Estadísticas de Tickets');
?>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>

<div class="py-4 px-5" style="max-width: 1100px; margin: 0 auto; width: 100%;">
    <!-- Header -->
    <div class="mb-5">
        <h2 class="fw-normal"><i class="bi bi-bar-chart me-2 text-success"></i>Estadísticas</h2>
        <p class="text-muted fw-light">Vista general del sistema de tickets</p>
    </div>

    <!-- Date Range Filter (commented out for now - can be enabled later) -->
    <!-- <?= $this->element('shared/statistics/date_range_filter', [
        'filters' => $filters,
        'action' => 'statistics'
    ]) ?> -->

    <!-- KPI Cards -->
    <?= $this->element('shared/statistics/kpi_cards', [
        'total' => $total,
        'recentCount' => $recentCount,
        'unassignedCount' => $unassignedCount,
        'activeAgentsCount' => $activeAgentsCount,
        'entityType' => 'ticket',
        'slaMetrics' => null
    ]) ?>

    <!-- Charts Row -->
    <div class="row mb-5">
        <!-- Status Chart -->
        <div class="col-md-4">
            <?= $this->element('shared/statistics/status_chart', [
                'statusDistribution' => $statusDistribution,
                'entityType' => 'ticket'
            ]) ?>
        </div>

        <!-- Priority Chart -->
        <div class="col-md-4">
            <?= $this->element('shared/statistics/priority_chart', [
                'priorityDistribution' => $priorityDistribution,
                'entityType' => 'ticket'
            ]) ?>
        </div>

        <!-- Response Metrics (Tickets-specific) -->
        <div class="col-md-4">
            <?= $this->element('Tickets/response_metrics', [
                'responseRate' => $responseRate,
                'resolutionRate' => $resolutionRate,
                'avgResponseTime' => $avgResponseTime,
                'avgResolutionTime' => $avgResolutionTime
            ]) ?>
        </div>
    </div>

    <!-- Trend Chart -->
    <?= $this->element('shared/statistics/trend_chart', [
        'chartLabels' => $chartLabels,
        'chartData' => $chartData,
        'entityType' => 'ticket'
    ]) ?>

    <!-- Tables Row -->
    <div class="row mb-5">
        <!-- Top Agents -->
        <div class="col-md-6">
            <?= $this->element('shared/statistics/agent_performance_table', [
                'topAgents' => $topAgents,
                'entityType' => 'ticket'
            ]) ?>
        </div>

        <!-- Top Requesters (Tickets-specific) -->
        <div class="col-md-6">
            <?= $this->element('Tickets/requester_stats', [
                'topRequesters' => $topRequesters
            ]) ?>
        </div>
    </div>

    <!-- Comments Statistics -->
    <div class="row">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-chat-dots text-primary" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2 mb-0"><?= number_format($totalComments) ?></h3>
                    <p class="text-muted mb-0 fw-light">Total Comentarios</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-eye text-success" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2 mb-0"><?= number_format($publicComments) ?></h3>
                    <p class="text-muted mb-0 fw-light">Comentarios Públicos</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-eye-slash text-warning" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2 mb-0"><?= number_format($internalComments) ?></h3>
                    <p class="text-muted mb-0 fw-light">Comentarios Internos</p>
                </div>
            </div>
        </div>
    </div>
</div>
