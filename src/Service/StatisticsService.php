<?php
declare(strict_types=1);

namespace App\Service;

use App\Service\Traits\StatisticsServiceTrait;
use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * Statistics Service
 *
 * Centralizes all dashboard and reporting queries for Tickets, PQRS, and Compras.
 * Uses StatisticsServiceTrait for shared logic across all modules.
 */
class StatisticsService
{
    use LocatorAwareTrait;
    use StatisticsServiceTrait;

    /**
     * Get ticket statistics
     *
     * @param array $filters Optional filters (date_range, start_date, end_date)
     * @return array Statistics data
     */
    public function getTicketStats(array $filters = []): array
    {
        $parsedFilters = $this->parseDateFilters($filters);
        $baseQuery = $this->buildBaseQuery('Tickets', $parsedFilters);

        // Use trait methods for common metrics
        $statusDistribution = $this->getStatusDistribution(
            'Tickets',
            ['nuevo', 'abierto', 'pendiente', 'resuelto', 'convertido'],
            $baseQuery
        );

        $priorityDistribution = $this->getPriorityDistribution('Tickets', $baseQuery);

        $avgResponseTime = $this->getAvgResponseTime('Tickets', $baseQuery);
        $avgResolutionTime = $this->getAvgResolutionTime('Tickets', $baseQuery);

        $responseRate = $this->calculateResponseRate('Tickets', $baseQuery);
        $resolutionRate = $this->calculateResolutionRate('Tickets', $baseQuery);

        $totalTickets = (clone $baseQuery)->count();
        $resolvedCount = $statusDistribution['resuelto'] ?? 0;

        // Recent tickets (last 7 days) - independent query
        $recentTickets = $this->getRecentActivityCount('Tickets');

        // Resolved tickets (last 7 days)
        $ticketsTable = $this->fetchTable('Tickets');
        $recentResolved = $ticketsTable->find()
            ->where([
                'status' => 'resuelto',
                'resolved_at >=' => new \DateTime('-7 days')
            ])
            ->count();

        $unassignedTickets = $this->getUnassignedCount('Tickets');

        return [
            'total_tickets' => $totalTickets,
            'tickets_by_status' => $statusDistribution,
            'tickets_by_priority' => $priorityDistribution,
            'recent_tickets' => $recentTickets,
            'recent_resolved' => $recentResolved,
            'unassigned_tickets' => $unassignedTickets,
            'avg_response_time' => $avgResponseTime,
            'avg_resolution_time' => $avgResolutionTime,
            'response_rate' => $responseRate,
            'resolution_rate' => $resolutionRate,
            'resolved_count' => $resolvedCount,
        ];
    }

    /**
     * Get agent performance metrics for Tickets
     *
     * @param array $filters Optional filters
     * @return array Agent performance data
     */
    public function getTicketAgentPerformance(array $filters = []): array
    {
        // Call trait method (no longer conflicts since this method has different name)
        $performanceData = $this->getAgentPerformance('Tickets', [], 5);

        return [
            'active_agents' => $performanceData['active_agents_count'],
            'tickets_by_agent' => $performanceData['top_agents'],
        ];
    }

    /**
     * Get ticket trend data for charts
     *
     * @param int $days Number of days to include
     * @return array Chart data
     */
    public function getTicketTrendData(int $days = 30): array
    {
        return $this->getTrendData('Tickets', $days);
    }

    /**
     * Get recent activity for Tickets dashboard
     *
     * @param int $limit Number of items to return
     * @return array Recent activity data
     */
    public function getRecentActivity(int $limit = 10): array
    {
        $ticketsTable = $this->fetchTable('Tickets');
        $commentsTable = $this->fetchTable('TicketComments');

        // Most active requesters (top 5)
        $topRequesters = $ticketsTable->find()
            ->contain(['Requesters'])
            ->select([
                'requester_id',
                'requester_name' => $ticketsTable->find()->func()->concat([
                    'Requesters.first_name' => 'identifier',
                    ' ',
                    'Requesters.last_name' => 'identifier'
                ]),
                'requester_email' => 'Requesters.email',
                'count' => $ticketsTable->find()->func()->count('*')
            ])
            ->group(['requester_id', 'Requesters.email'])
            ->order(['count' => 'DESC'])
            ->limit(5)
            ->all();

        // Comments stats - optimized with single query
        $commentStats = $commentsTable->find()
            ->select([
                'comment_type',
                'is_system_comment',
                'count' => $commentsTable->find()->func()->count('*')
            ])
            ->group(['comment_type', 'is_system_comment'])
            ->all()
            ->toArray();

        // Calculate from grouped results
        $totalComments = 0;
        $publicComments = 0;
        $internalComments = 0;

        foreach ($commentStats as $stat) {
            $count = $stat->count;
            if (!$stat->is_system_comment) {
                $totalComments += $count;
            }
            if ($stat->comment_type === 'public') {
                $publicComments += $count;
            }
            if ($stat->comment_type === 'internal' && !$stat->is_system_comment) {
                $internalComments += $count;
            }
        }

        return [
            'top_requesters' => $topRequesters,
            'total_comments' => $totalComments,
            'public_comments' => $publicComments,
            'internal_comments' => $internalComments,
        ];
    }


    /**
     * Get PQRS statistics
     *
     * @param array $filters Optional filters (date_from, date_to, date_range)
     * @return array PQRS statistics data
     */
    public function getPqrsStats(array $filters = []): array
    {
        // Handle both old format (date_from/date_to) and new format (date_range)
        if (!isset($filters['date_range']) && (isset($filters['date_from']) || isset($filters['date_to']))) {
            // Convert old format to new format
            $filters['date_range'] = 'custom';
            $filters['start_date'] = $filters['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
            $filters['end_date'] = $filters['date_to'] ?? date('Y-m-d');
        }

        $parsedFilters = $this->parseDateFilters($filters);

        // For PQRS, default to last 30 days if 'all' is selected
        if ($parsedFilters['date_range'] === 'all' || $parsedFilters['date_range'] === '30days') {
            $parsedFilters['start_date'] = date('Y-m-d', strtotime('-30 days'));
            $parsedFilters['end_date'] = date('Y-m-d');
        }

        $baseQuery = $this->buildBaseQuery('Pqrs', $parsedFilters);

        // Use trait methods
        $statusCounts = $this->getStatusDistribution(
            'Pqrs',
            ['nuevo', 'en_revision', 'en_proceso', 'resuelto', 'cerrado'],
            $baseQuery
        );

        $priorityCounts = $this->getPriorityDistribution('Pqrs', $baseQuery);

        // Get type distribution (PQRS-specific)
        $typeCounts = $this->getTypeDistribution();

        // Get channel distribution (now implemented in trait)
        $channelCounts = $this->getChannelDistribution('Pqrs', $baseQuery);

        // Calculate totals
        $totalPqrs = array_sum($statusCounts);
        $totalResolved = ($statusCounts['resuelto'] ?? 0) + ($statusCounts['cerrado'] ?? 0);
        $totalPending = $totalPqrs - $totalResolved;

        $totalUnassigned = $this->getUnassignedCount('Pqrs');
        $recentPqrs = $this->getRecentActivityCount('Pqrs');

        // Resolved in period
        $pqrsTable = $this->fetchTable('Pqrs');
        $resolvedInPeriod = $pqrsTable->find()
            ->where([
                'resolved_at IS NOT' => null,
                'resolved_at >=' => $parsedFilters['start_date'],
                'resolved_at <=' => $parsedFilters['end_date'] . ' 23:59:59'
            ])
            ->count();

        // Average resolution time
        $avgResolutionTime = $this->getAvgResolutionTime('Pqrs', $baseQuery);
        $avgResolutionHours = ($avgResolutionTime && $avgResolutionTime->avg_hours !== null)
            ? round((float) $avgResolutionTime->avg_hours, 1)
            : 0;
        $avgResolutionDays = $avgResolutionHours > 0 ? round($avgResolutionHours / 24, 1) : 0;

        // Top agents
        $agentPerformance = $this->getAgentPerformance('Pqrs', ['resuelto', 'cerrado'], 5);

        return [
            'total_pqrs' => $totalPqrs,
            'total_resolved' => $totalResolved,
            'total_pending' => $totalPending,
            'total_unassigned' => $totalUnassigned,
            'status_counts' => $statusCounts,
            'type_counts' => $typeCounts,
            'priority_counts' => $priorityCounts,
            'channel_counts' => $channelCounts,
            'recent_pqrs' => $recentPqrs,
            'resolved_in_period' => $resolvedInPeriod,
            'avg_resolution_days' => $avgResolutionDays,
            'avg_resolution_hours' => $avgResolutionHours,
            'top_agents' => $agentPerformance['top_agents'],
            'active_agents_count' => $agentPerformance['active_agents_count'],
            'date_from' => $parsedFilters['start_date'],
            'date_to' => $parsedFilters['end_date'],
        ];
    }

    /**
     * Get PQRS trend data for charts
     *
     * @param int $days Number of days to include
     * @return array Chart data
     */
    public function getPqrsTrendData(int $days = 30): array
    {
        return $this->getTrendData('Pqrs', $days);
    }

    /**
     * Get Compras statistics (NEW)
     *
     * @param array $filters Optional filters (date_range, start_date, end_date)
     * @return array Compras statistics data
     */
    public function getComprasStats(array $filters = []): array
    {
        $parsedFilters = $this->parseDateFilters($filters);

        // For Compras, default to last 30 days if 'all' is selected
        if ($parsedFilters['date_range'] === 'all' || $parsedFilters['date_range'] === '30days') {
            $parsedFilters['start_date'] = date('Y-m-d', strtotime('-30 days'));
            $parsedFilters['end_date'] = date('Y-m-d');
        }

        $baseQuery = $this->buildBaseQuery('Compras', $parsedFilters);

        // Use trait methods
        $statusCounts = $this->getStatusDistribution(
            'Compras',
            ['nuevo', 'en_revision', 'aprobado', 'en_proceso', 'completado', 'rechazado', 'convertido'],
            $baseQuery
        );

        $priorityCounts = $this->getPriorityDistribution('Compras', $baseQuery);
        $channelCounts = $this->getChannelDistribution('Compras', $baseQuery);

        $totalCompras = array_sum($statusCounts);
        $unassignedCompras = $this->getUnassignedCount('Compras');
        $recentCompras = $this->getRecentActivityCount('Compras');

        // Average resolution time
        $avgResolutionTime = $this->getAvgResolutionTime('Compras', $baseQuery);
        $avgResolutionHours = ($avgResolutionTime && $avgResolutionTime->avg_hours !== null)
            ? round((float) $avgResolutionTime->avg_hours, 1)
            : 0;
        $avgResolutionDays = $avgResolutionHours > 0 ? round($avgResolutionHours / 24, 1) : 0;

        // Agent performance (by completed compras)
        $agentPerformance = $this->getAgentPerformance('Compras', ['completado'], 5);

        // Compras-specific metrics
        $slaMetrics = $this->getSLAMetrics($baseQuery);
        $approvalMetrics = $this->getApprovalMetrics($baseQuery);

        return [
            'total_compras' => $totalCompras,
            'status_counts' => $statusCounts,
            'priority_counts' => $priorityCounts,
            'channel_counts' => $channelCounts,
            'unassigned_compras' => $unassignedCompras,
            'recent_compras' => $recentCompras,
            'avg_resolution_hours' => $avgResolutionHours,
            'avg_resolution_days' => $avgResolutionDays,
            'top_agents' => $agentPerformance['top_agents'],
            'active_agents_count' => $agentPerformance['active_agents_count'],
            'sla_metrics' => $slaMetrics,
            'approval_metrics' => $approvalMetrics,
            'date_from' => $parsedFilters['start_date'],
            'date_to' => $parsedFilters['end_date'],
        ];
    }

    /**
     * Get Compras trend data for charts (NEW)
     *
     * @param int $days Number of days to include
     * @return array Chart data
     */
    public function getComprasTrendData(int $days = 30): array
    {
        return $this->getTrendData('Compras', $days);
    }

    // ==================== PRIVATE MODULE-SPECIFIC METHODS ====================

    /**
     * Get type distribution (PQRS-specific)
     *
     * @return array Type => count mapping
     */
    private function getTypeDistribution(): array
    {
        $pqrsTable = $this->fetchTable('Pqrs');

        $typeCountsRaw = $pqrsTable->find()
            ->select(['type', 'count' => $pqrsTable->find()->func()->count('*')])
            ->group(['type'])
            ->all()
            ->combine('type', 'count')
            ->toArray();

        return [
            'peticion' => $typeCountsRaw['peticion'] ?? 0,
            'queja' => $typeCountsRaw['queja'] ?? 0,
            'reclamo' => $typeCountsRaw['reclamo'] ?? 0,
            'sugerencia' => $typeCountsRaw['sugerencia'] ?? 0,
        ];
    }

    /**
     * Get SLA metrics (Compras-specific)
     *
     * @param \Cake\ORM\Query $baseQuery Base query with filters applied
     * @return array SLA metrics
     */
    private function getSLAMetrics($baseQuery): array
    {
        $now = new \DateTime();

        // SLA breached count (past deadline and not completed/rejected/converted)
        $breachedQuery = clone $baseQuery;
        $breachedCount = $breachedQuery
            ->where([
                'sla_due_date <' => $now,
                'status NOT IN' => ['completado', 'rechazado', 'convertido']
            ])
            ->count();

        // SLA at risk (< 24 hours remaining)
        $atRiskQuery = clone $baseQuery;
        $tomorrow = (new \DateTime())->modify('+24 hours');
        $atRiskCount = $atRiskQuery
            ->where([
                'sla_due_date >=' => $now,
                'sla_due_date <' => $tomorrow,
                'status NOT IN' => ['completado', 'rechazado', 'convertido']
            ])
            ->count();

        // Total with active SLA
        $activeSLAQuery = clone $baseQuery;
        $activeSLACount = $activeSLAQuery
            ->where(['status NOT IN' => ['completado', 'rechazado', 'convertido']])
            ->count();

        // Compliance rate
        $complianceRate = $activeSLACount > 0
            ? round((($activeSLACount - $breachedCount) / $activeSLACount) * 100, 1)
            : 100.0;

        return [
            'breached_count' => $breachedCount,
            'at_risk_count' => $atRiskCount,
            'active_count' => $activeSLACount,
            'compliance_rate' => $complianceRate,
        ];
    }

    /**
     * Get approval metrics (Compras-specific)
     *
     * @param \Cake\ORM\Query $baseQuery Base query with filters applied
     * @return array Approval metrics
     */
    private function getApprovalMetrics($baseQuery): array
    {
        // Approved count (aprobado + en_proceso + completado)
        $approvedQuery = clone $baseQuery;
        $approvedCount = $approvedQuery
            ->where(['status IN' => ['aprobado', 'en_proceso', 'completado']])
            ->count();

        // Rejected count
        $rejectedQuery = clone $baseQuery;
        $rejectedCount = $rejectedQuery
            ->where(['status' => 'rechazado'])
            ->count();

        // Total decided (approved + rejected)
        $totalDecided = $approvedCount + $rejectedCount;

        // Approval rate
        $approvalRate = $totalDecided > 0
            ? round(($approvedCount / $totalDecided) * 100, 1)
            : 0.0;

        return [
            'approved_count' => $approvedCount,
            'rejected_count' => $rejectedCount,
            'total_decided' => $totalDecided,
            'approval_rate' => $approvalRate,
        ];
    }
}
