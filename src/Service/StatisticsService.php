<?php
declare(strict_types=1);

namespace App\Service;

use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * Statistics Service
 *
 * Centralizes all dashboard and reporting queries for Tickets and PQRS.
 * Provides methods for retrieving statistics, metrics, and analytics data.
 */
class StatisticsService
{
    use LocatorAwareTrait;

    /**
     * Get ticket statistics
     *
     * @param array $filters Optional filters (date_range, start_date, end_date)
     * @return array Statistics data
     */
    public function getTicketStats(array $filters = []): array
    {
        $ticketsTable = $this->fetchTable('Tickets');

        // Extract filters
        $dateRange = $filters['date_range'] ?? 'all';
        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;

        // Set default dates based on range
        $now = new \DateTime();
        switch ($dateRange) {
            case 'today':
                $startDate = $now->format('Y-m-d');
                $endDate = $now->format('Y-m-d');
                break;
            case 'week':
                $startDate = (new \DateTime('-7 days'))->format('Y-m-d');
                $endDate = $now->format('Y-m-d');
                break;
            case 'month':
                $startDate = (new \DateTime('-30 days'))->format('Y-m-d');
                $endDate = $now->format('Y-m-d');
                break;
            case 'custom':
                // Use provided dates
                break;
            default:
                // 'all' - no date filter
                $startDate = null;
                $endDate = null;
        }

        // Build base query with date filter
        $baseQuery = $ticketsTable->find();
        if ($startDate && $endDate) {
            $baseQuery->where([
                'Tickets.created >=' => $startDate . ' 00:00:00',
                'Tickets.created <=' => $endDate . ' 23:59:59'
            ]);
        }

        // Total tickets
        $totalTickets = (clone $baseQuery)->count();

        // Tickets by status
        $ticketsByStatus = (clone $baseQuery)
            ->select([
                'status',
                'count' => $baseQuery->func()->count('*')
            ])
            ->group('status')
            ->all()
            ->combine('status', 'count')
            ->toArray();

        // Tickets by priority
        $ticketsByPriority = (clone $baseQuery)
            ->select([
                'priority',
                'count' => $baseQuery->func()->count('*')
            ])
            ->group('priority')
            ->all()
            ->combine('priority', 'count')
            ->toArray();

        // Recent tickets (last 7 days)
        $recentTickets = $ticketsTable->find()
            ->where(['created >=' => new \DateTime('-7 days')])
            ->count();

        // Resolved tickets (last 7 days)
        $recentResolved = $ticketsTable->find()
            ->where([
                'status' => 'resuelto',
                'resolved_at >=' => new \DateTime('-7 days')
            ])
            ->count();

        // Tickets without assignment
        $unassignedTickets = $ticketsTable->find()
            ->where(['assignee_id IS' => null])
            ->count();

        // Average response time (hours) - MySQL compatible
        $avgResponseTime = $ticketsTable->find()
            ->where(['first_response_at IS NOT' => null])
            ->select([
                'avg_hours' => $ticketsTable->find()->func()->avg(
                    "TIMESTAMPDIFF(SECOND, created, first_response_at) / 3600"
                )
            ])
            ->first();

        // Average resolution time (hours) - MySQL compatible
        $avgResolutionTime = $ticketsTable->find()
            ->where(['resolved_at IS NOT' => null])
            ->select([
                'avg_hours' => $ticketsTable->find()->func()->avg(
                    "TIMESTAMPDIFF(SECOND, created, resolved_at) / 3600"
                )
            ])
            ->first();

        // Response rate (tickets with at least one response)
        $ticketsWithResponse = $ticketsTable->find()
            ->where(['first_response_at IS NOT' => null])
            ->count();
        $responseRate = $totalTickets > 0 ? round(($ticketsWithResponse / $totalTickets) * 100, 1) : 0;

        // Resolution rate (tickets resolved / total tickets) - calculated from existing data
        $resolvedCount = $ticketsByStatus['resuelto'] ?? 0;
        $resolutionRate = $totalTickets > 0 ? round(($resolvedCount / $totalTickets) * 100, 1) : 0;

        return [
            'total_tickets' => $totalTickets,
            'tickets_by_status' => $ticketsByStatus,
            'tickets_by_priority' => $ticketsByPriority,
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
     * Get agent performance metrics
     *
     * @param array $filters Optional filters
     * @return array Agent performance data
     */
    public function getAgentPerformance(array $filters = []): array
    {
        $ticketsTable = $this->fetchTable('Tickets');
        $usersTable = $this->fetchTable('Users');

        // Active agents
        $activeAgents = $usersTable->find()
            ->where([
                'role IN' => ['admin', 'agent', 'compras'],
                'is_active' => true
            ])
            ->count();

        // Tickets by agent (top 5)
        $ticketsByAgent = $ticketsTable->find()
            ->contain(['Assignees'])
            ->where(['assignee_id IS NOT' => null])
            ->select([
                'assignee_id',
                'agent_name' => $ticketsTable->find()->func()->concat([
                    'Assignees.first_name' => 'identifier',
                    ' ',
                    'Assignees.last_name' => 'identifier'
                ]),
                'count' => $ticketsTable->find()->func()->count('*')
            ])
            ->group(['assignee_id'])
            ->order(['count' => 'DESC'])
            ->limit(5)
            ->all();

        return [
            'active_agents' => $activeAgents,
            'tickets_by_agent' => $ticketsByAgent,
        ];
    }

    /**
     * Get recent activity for dashboard
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
     * Get trend data for charts
     *
     * @param string $period Period for trends (e.g., '30days')
     * @return array Trend data
     */
    public function getTrendData(string $period = '30days'): array
    {
        $ticketsTable = $this->fetchTable('Tickets');

        // Parse period
        $days = (int) filter_var($period, FILTER_SANITIZE_NUMBER_INT);
        if ($days <= 0) {
            $days = 30;
        }

        // Tickets created per day
        $ticketsPerDay = $ticketsTable->find()
            ->where(['created >=' => new \DateTime("-{$days} days")])
            ->select([
                'date' => 'DATE(created)',
                'count' => $ticketsTable->find()->func()->count('*')
            ])
            ->group('DATE(created)')
            ->order(['date' => 'ASC'])
            ->all()
            ->combine('date', 'count')
            ->toArray();

        return [
            'tickets_per_day' => $ticketsPerDay,
        ];
    }

    /**
     * Get PQRS statistics
     *
     * @param array $filters Optional filters (date_from, date_to)
     * @return array PQRS statistics data
     */
    public function getPqrsStats(array $filters = []): array
    {
        $pqrsTable = $this->fetchTable('Pqrs');

        // Get date range from filters (default: last 30 days)
        $dateFrom = $filters['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo = $filters['date_to'] ?? date('Y-m-d');

        // Optimize with GROUP BY queries
        // Get status counts in single query
        $statusCountsRaw = $pqrsTable->find()
            ->select(['status', 'count' => $pqrsTable->find()->func()->count('*')])
            ->group(['status'])
            ->all()
            ->combine('status', 'count')
            ->toArray();

        $statusCounts = [
            'nuevo' => $statusCountsRaw['nuevo'] ?? 0,
            'en_revision' => $statusCountsRaw['en_revision'] ?? 0,
            'en_proceso' => $statusCountsRaw['en_proceso'] ?? 0,
            'resuelto' => $statusCountsRaw['resuelto'] ?? 0,
            'cerrado' => $statusCountsRaw['cerrado'] ?? 0,
        ];

        // Get type counts in single query
        $typeCountsRaw = $pqrsTable->find()
            ->select(['type', 'count' => $pqrsTable->find()->func()->count('*')])
            ->group(['type'])
            ->all()
            ->combine('type', 'count')
            ->toArray();

        $typeCounts = [
            'peticion' => $typeCountsRaw['peticion'] ?? 0,
            'queja' => $typeCountsRaw['queja'] ?? 0,
            'reclamo' => $typeCountsRaw['reclamo'] ?? 0,
            'sugerencia' => $typeCountsRaw['sugerencia'] ?? 0,
        ];

        // Get priority counts in single query
        $priorityCountsRaw = $pqrsTable->find()
            ->select(['priority', 'count' => $pqrsTable->find()->func()->count('*')])
            ->group(['priority'])
            ->all()
            ->combine('priority', 'count')
            ->toArray();

        $priorityCounts = [
            'baja' => $priorityCountsRaw['baja'] ?? 0,
            'media' => $priorityCountsRaw['media'] ?? 0,
            'alta' => $priorityCountsRaw['alta'] ?? 0,
            'urgente' => $priorityCountsRaw['urgente'] ?? 0,
        ];

        // Calculate totals from grouped data
        $totalPqrs = array_sum($statusCounts);
        $totalResolved = ($statusCounts['resuelto'] ?? 0) + ($statusCounts['cerrado'] ?? 0);
        $totalPending = $totalPqrs - $totalResolved;
        $totalUnassigned = $pqrsTable->find()->where(['assignee_id IS' => null])->count();

        // Recent PQRS (last 7 days)
        $recentPqrs = $pqrsTable->find()
            ->where(['created >=' => date('Y-m-d', strtotime('-7 days'))])
            ->count();

        // Resolved in period
        $resolvedInPeriod = $pqrsTable->find()
            ->where([
                'resolved_at IS NOT' => null,
                'resolved_at >=' => $dateFrom,
                'resolved_at <=' => $dateTo . ' 23:59:59'
            ])
            ->count();

        // Average resolution time (in days) - MySQL compatible
        $resolvedWithTime = $pqrsTable->find()
            ->select([
                'avg_time' => "AVG(TIMESTAMPDIFF(SECOND, created, resolved_at) / 3600)"
            ])
            ->where(['resolved_at IS NOT' => null])
            ->first();

        $avgResolutionHours = ($resolvedWithTime && $resolvedWithTime->avg_time !== null) ? round((float) $resolvedWithTime->avg_time, 1) : 0;
        $avgResolutionDays = $avgResolutionHours > 0 ? round($avgResolutionHours / 24, 1) : 0;

        // Top 5 agents by resolved PQRS
        $topAgents = $pqrsTable->find()
            ->select([
                'Assignees.id',
                'Assignees.first_name',
                'Assignees.last_name',
                'count' => 'COUNT(*)'
            ])
            ->contain(['Assignees'])
            ->where([
                'assignee_id IS NOT' => null,
                'status IN' => ['resuelto', 'cerrado']
            ])
            ->group(['assignee_id', 'Assignees.id', 'Assignees.first_name', 'Assignees.last_name'])
            ->orderBy(['count' => 'DESC'])
            ->limit(5)
            ->toArray();

        return [
            'total_pqrs' => $totalPqrs,
            'total_resolved' => $totalResolved,
            'total_pending' => $totalPending,
            'total_unassigned' => $totalUnassigned,
            'status_counts' => $statusCounts,
            'type_counts' => $typeCounts,
            'priority_counts' => $priorityCounts,
            'recent_pqrs' => $recentPqrs,
            'resolved_in_period' => $resolvedInPeriod,
            'avg_resolution_days' => $avgResolutionDays,
            'avg_resolution_hours' => $avgResolutionHours,
            'top_agents' => $topAgents,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
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
        $pqrsTable = $this->fetchTable('Pqrs');

        // PQRS created per day for chart
        $dailyStats = $pqrsTable->find()
            ->select([
                'date' => 'DATE(created)',
                'count' => 'COUNT(*)'
            ])
            ->where(['created >=' => date('Y-m-d', strtotime("-{$days} days"))])
            ->group(['DATE(created)'])
            ->orderBy(['date' => 'ASC'])
            ->toArray();

        // Create a map of dates to counts
        $statsMap = [];
        foreach ($dailyStats as $stat) {
            $statsMap[$stat->date] = $stat->count;
        }

        // Generate complete range with zeros for missing days
        $chartLabels = [];
        $chartData = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $chartLabels[] = date('d/m', strtotime($date));
            $chartData[] = isset($statsMap[$date]) ? $statsMap[$date] : 0;
        }

        return [
            'chart_labels' => $chartLabels,
            'chart_data' => $chartData,
        ];
    }
}
