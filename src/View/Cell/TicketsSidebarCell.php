<?php
declare(strict_types=1);

namespace App\View\Cell;

use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;
use Cake\View\Cell;

class TicketsSidebarCell extends Cell
{
    /**
     * Display method
     *
     * @param string $currentView Current active view
     * @param string|null $userRole User role (admin, agent, requester)
     * @param int|null $userId Current user ID
     * @return void
     */
    public function display(string $currentView = 'todos_sin_resolver', ?string $userRole = null, ?int $userId = null): void
    {
        $ticketsTable = TableRegistry::getTableLocator()->get('Tickets');
        $usersTable = TableRegistry::getTableLocator()->get('Users');

        // Get current user object for profile image
        $currentUser = null;
        if ($userId) {
            $currentUser = $usersTable->get($userId);
        }

        // Build base query - filter by user role
        $baseQuery = $ticketsTable->find();
        if ($userRole === 'requester' && $userId) {
            $baseQuery->where(['requester_id' => $userId]);
        }

        // If user is compras role, filter to only their assigned tickets
        if ($userRole === 'compras' && $userId) {
            $baseQuery->where(['assignee_id' => $userId]);
        }

        // If user is agent, exclude tickets assigned to compras users
        if ($userRole === 'agent') {
            $comprasUserIds = $usersTable
                ->find()
                ->select(['id'])
                ->where(['role' => 'compras'])
                ->all()
                ->extract('id')
                ->toArray();

            if (!empty($comprasUserIds)) {
                $baseQuery->where([
                    'OR' => [
                        'Tickets.assignee_id IS' => null,
                        'Tickets.assignee_id NOT IN' => $comprasUserIds
                    ]
                ]);
            }
        }

        // Calculate counts for each view
        // For agents: filter nuevos, abiertos, pendientes by assigned tickets only
        // For admins: show all tickets
        $isAgent = $userRole === 'agent';
        $isAdmin = $userRole === 'admin';

        $counts = [
            'sin_asignar' => (clone $baseQuery)
                ->where(['assignee_id IS' => null, 'status !=' => 'resuelto'])
                ->count(),
            'todos_sin_resolver' => (clone $baseQuery)
                ->where(['status !=' => 'resuelto'])
                ->count(),
            'pendientes' => (clone $baseQuery)
                ->where([
                    'status' => 'pendiente',
                    // Agents see only their assigned tickets, admins see all
                    ($isAgent && $userId) ? ['assignee_id' => $userId] : []
                ])
                ->count(),
            'nuevos' => (clone $baseQuery)
                ->where([
                    'status' => 'nuevo',
                    // Agents see only their assigned tickets, admins see all
                    ($isAgent && $userId) ? ['assignee_id' => $userId] : []
                ])
                ->count(),
            'abiertos' => (clone $baseQuery)
                ->where([
                    'status' => 'abierto',
                    // Agents see only their assigned tickets, admins see all
                    ($isAgent && $userId) ? ['assignee_id' => $userId] : []
                ])
                ->count(),
            'resueltos' => (clone $baseQuery)
                ->where(['status' => 'resuelto'])
                ->count(),
        ];

        // Add "mis_tickets" count for agents and compras
        if (($userRole === 'agent' || $userRole === 'compras') && $userId) {
            $counts['mis_tickets'] = $ticketsTable->find()
                ->where(['assignee_id' => $userId, 'status !=' => 'resuelto'])
                ->count();
        }

        $this->set('counts', $counts);
        $this->set('view', $currentView);
        $this->set('userRole', $userRole);
        $this->set('currentUser', $currentUser);
    }
}
