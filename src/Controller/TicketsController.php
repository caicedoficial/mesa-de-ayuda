<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\TicketService;
use App\Service\AttachmentService;

/**
 * Tickets Controller
 *
 * @property \App\Model\Table\TicketsTable $Tickets
 */
class TicketsController extends AppController
{
    private TicketService $ticketService;
    private AttachmentService $attachmentService;

    /**
     * Initialize
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->ticketService = new TicketService();
        $this->attachmentService = new AttachmentService();
    }

    /**
     * Index method - List tickets with filters
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        // Handle Gmail OAuth callback redirect
        $code = $this->request->getQuery('code');
        if ($code) {
            return $this->redirect([
                'controller' => 'Settings',
                'action' => 'gmailAuth',
                'prefix' => 'Admin',
                '?' => ['code' => $code]
            ]);
        }

        $user = $this->Authentication->getIdentity();
        $userRole = $user ? $user->get('role') : null;

        // Redirect servicio_cliente users to PQRS
        if ($userRole === 'servicio_cliente') {
            return $this->redirect(['controller' => 'Pqrs', 'action' => 'index']);
        }
        $view = $this->request->getQuery('view', 'todos_sin_resolver');

        // Get search and filter parameters
        $search = $this->request->getQuery('search');
        $filterStatus = $this->request->getQuery('filter_status');
        $filterPriority = $this->request->getQuery('filter_priority');
        $filterAssignee = $this->request->getQuery('filter_assignee');
        $filterOrganization = $this->request->getQuery('filter_organization');
        $filterDateFrom = $this->request->getQuery('filter_date_from');
        $filterDateTo = $this->request->getQuery('filter_date_to');
        $sortField = $this->request->getQuery('sort', 'created');
        $sortDirection = $this->request->getQuery('direction', 'desc');

        // Build query based on view
        $query = $this->Tickets->find()
            ->contain(['Requesters', 'Assignees', 'Organizations']);

        // Apply sorting
        $validSortFields = ['created', 'modified', 'ticket_number', 'status', 'priority', 'subject'];
        if (in_array($sortField, $validSortFields)) {
            $query->orderBy(['Tickets.' . $sortField => strtoupper($sortDirection)]);
        } else {
            $query->orderBy(['Tickets.created' => 'DESC']);
        }

        // If user is a requester, only show their own tickets
        if ($userRole === 'requester') {
            $query->where(['Tickets.requester_id' => $user->get('id')]);
        }

        // If user is compras role, only show their assigned tickets or resolved tickets
        if ($userRole === 'compras') {
            $query->where([
                'OR' => [
                    'Tickets.assignee_id' => $user->get('id'),
                    'AND' => [
                        'Tickets.assignee_id' => $user->get('id'),
                        'Tickets.status' => 'resuelto'
                    ]
                ]
            ]);
        }

        // If user is agent, exclude tickets assigned to compras users
        if ($userRole === 'agent') {
            $comprasUserIds = $this->fetchTable('Users')
                ->find()
                ->select(['id'])
                ->where(['role' => 'compras'])
                ->all()
                ->extract('id')
                ->toArray();

            if (!empty($comprasUserIds)) {
                $query->where([
                    'OR' => [
                        'Tickets.assignee_id IS' => null,
                        'Tickets.assignee_id NOT IN' => $comprasUserIds
                    ]
                ]);
            }
        }

        // Apply search filter - if search is active, ignore view filters and search ALL tickets
        if (!empty($search)) {
            $query->where([
                'OR' => [
                    'Tickets.ticket_number LIKE' => '%' . $search . '%',
                    'Tickets.subject LIKE' => '%' . $search . '%',
                    'Tickets.description LIKE' => '%' . $search . '%',
                    'Tickets.source_email LIKE' => '%' . $search . '%',
                    'Requesters.name LIKE' => '%' . $search . '%',
                    'Requesters.email LIKE' => '%' . $search . '%',
                ]
            ]);
        } else {
            // Apply view-based filters only when NOT searching
            switch ($view) {
                case 'sin_asignar':
                    $query->where(['Tickets.assignee_id IS' => null, 'Tickets.status !=' => 'resuelto']);
                    break;
                case 'todos_sin_resolver':
                    $query->where(['Tickets.status !=' => 'resuelto']);
                    break;
                case 'pendientes':
                    $query->where(['Tickets.status' => 'pendiente']);
                    break;
                case 'nuevos':
                    $query->where(['Tickets.status' => 'nuevo']);
                    break;
                case 'abiertos':
                    $query->where(['Tickets.status' => 'abierto']);
                    break;
                case 'resueltos':
                    $query->where(['Tickets.status' => 'resuelto']);
                    break;
                case 'mis_tickets':
                    // Filter by current user's assigned tickets
                    if ($user) {
                        $query->where(['Tickets.assignee_id' => $user->get('id'), 'Tickets.status !=' => 'resuelto']);
                    }
                    break;
            }
        }

        // Apply status filter
        if (!empty($filterStatus)) {
            $query->where(['Tickets.status' => $filterStatus]);
        }

        // Apply priority filter
        if (!empty($filterPriority)) {
            $query->where(['Tickets.priority' => $filterPriority]);
        }

        // Apply assignee filter
        if (!empty($filterAssignee)) {
            if ($filterAssignee === 'unassigned') {
                $query->where(['Tickets.assignee_id IS' => null]);
            } else {
                $query->where(['Tickets.assignee_id' => $filterAssignee]);
            }
        }

        // Apply organization filter
        if (!empty($filterOrganization)) {
            $query->where(['Tickets.organization_id' => $filterOrganization]);
        }

        // Apply date range filter
        if (!empty($filterDateFrom)) {
            $query->where(['Tickets.created >=' => $filterDateFrom . ' 00:00:00']);
        }
        if (!empty($filterDateTo)) {
            $query->where(['Tickets.created <=' => $filterDateTo . ' 23:59:59']);
        }

        $tickets = $this->paginate($query, [
            'limit' => 10,
        ]);

        // Get all agents for assignment dropdown and filters
        $agents = $this->fetchTable('Users')->find('list')
            ->where(['role IN' => ['admin', 'agent', 'compras'], 'is_active' => true])
            ->toArray();

        // Get all organizations for filter
        $organizations = $this->fetchTable('Organizations')->find('list')
            ->toArray();

        // Get all tags for bulk actions
        $tags = $this->fetchTable('Tags')->find()->toArray();

        // Store filters in variables for the view
        $filters = compact(
            'search',
            'filterStatus',
            'filterPriority',
            'filterAssignee',
            'filterOrganization',
            'filterDateFrom',
            'filterDateTo',
            'sortField',
            'sortDirection'
        );

        $this->set(compact('tickets', 'view', 'agents', 'organizations', 'filters', 'tags'));
    }

    /**
     * View method - Show ticket detail
     *
     * @param string|null $id Ticket id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $ticket = $this->Tickets->get($id, contain: [
            'Requesters',
            'Assignees',
            'Organizations',
            'TicketComments' => ['Users'],
            'Attachments',
            'Tags',
            'TicketFollowers' => ['Users'],
            'TicketHistory' => ['Users'],
        ]);

        // Check if requester is trying to view ticket that's not theirs
        $user = $this->Authentication->getIdentity();
        if ($user && $user->get('role') === 'requester') {
            if ($ticket->requester_id !== $user->get('id')) {
                $this->Flash->error('No tienes permiso para ver este ticket.');
                return $this->redirect(['action' => 'index']);
            }
        }

        // Check if compras user is trying to view ticket that's not assigned to them
        if ($user && $user->get('role') === 'compras') {
            if ($ticket->assignee_id !== $user->get('id')) {
                $this->Flash->error('No tienes permiso para ver este ticket.');
                return $this->redirect(['action' => 'index']);
            }
        }

        // Check if agent is trying to view a ticket assigned to a compras user
        if ($user && $user->get('role') === 'agent') {
            if ($ticket->assignee_id !== null) {
                $assignee = $this->fetchTable('Users')->get($ticket->assignee_id);
                if ($assignee->role === 'compras') {
                    $this->Flash->error('No tienes permiso para ver este ticket.');
                    return $this->redirect(['action' => 'index']);
                }
            }
        }

        // Get all agents for assignment dropdown
        $agents = $this->fetchTable('Users')->find('list')
            ->where(['role IN' => ['admin', 'agent', 'compras'], 'is_active' => true])
            ->toArray();

        // Get all tags for selection
        $tags = $this->fetchTable('Tags')->find('list')->toArray();

        $this->set(compact('ticket', 'agents', 'tags'));
    }

    /**
     * View method - Show ticket detail
     *
     * @param string|null $id Ticket id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function viewCompras($id = null)
    {
        $ticket = $this->Tickets->get($id, contain: [
            'Requesters',
            'Assignees',
            'Organizations',
            'TicketComments' => ['Users'],
            'Attachments',
            'Tags',
            'TicketFollowers' => ['Users'],
        ]);

        // Check if requester is trying to view ticket that's not theirs
        $user = $this->Authentication->getIdentity();
        if ($user && $user->get('role') === 'requester') {
            if ($ticket->requester_id !== $user->get('id')) {
                $this->Flash->error('No tienes permiso para ver este ticket.');
                return $this->redirect(['action' => 'index']);
            }
        }

        // Check if compras user is trying to view ticket that's not assigned to them
        if ($user && $user->get('role') === 'compras') {
            if ($ticket->assignee_id !== $user->get('id')) {
                $this->Flash->error('No tienes permiso para ver este ticket.');
                return $this->redirect(['action' => 'index']);
            }
        }

        // Get all agents for assignment dropdown
        $agents = $this->fetchTable('Users')->find('list')
            ->where(['role IN' => ['admin', 'agent', 'compras'], 'is_active' => true])
            ->toArray();

        // Get all tags for selection
        $tags = $this->fetchTable('Tags')->find('list')->toArray();

        $this->set(compact('ticket', 'agents', 'tags'));
    }

    /**
     * Add comment to ticket
     *
     * @param string|null $id Ticket id
     * @return \Cake\Http\Response|null|void Redirects back to ticket view
     */
    public function addComment($id = null)
    {
        $this->request->allowMethod(['post']);

        $ticket = $this->Tickets->get($id);
        $user = $this->Authentication->getIdentity();
        $userId = $user->get('id');

        $commentBody = $this->request->getData('comment_body');
        $commentType = $this->request->getData('comment_type', 'public');
        $newStatus = $this->request->getData('status', $ticket->status);

        if (empty($commentBody)) {
            $this->Flash->error('El comentario no puede estar vacío.');
            return $this->redirect(['action' => 'view', $id]);
        }

        // Add comment (without sending notifications yet)
        $comment = $this->ticketService->addComment(
            (int)$id,
            $userId,
            $commentBody,
            $commentType,
            false,
            false  // Don't send notifications yet
        );

        if (!$comment) {
            $this->Flash->error('Error al agregar el comentario.');
            return $this->redirect(['action' => 'view', $id]);
        }

        // Handle file uploads BEFORE sending notifications
        $files = $this->request->getUploadedFiles();
        $uploadedCount = 0;
        if (!empty($files['attachments'])) {
            foreach ($files['attachments'] as $file) {
                if ($file->getError() === UPLOAD_ERR_OK) {
                    $result = $this->attachmentService->saveUploadedFile(
                        (int)$id,
                        $comment->id,
                        $file,
                        $userId
                    );
                    if ($result) {
                        $uploadedCount++;
                    }
                }
            }
        }

        // NOW send notifications (after attachments are saved)
        $this->ticketService->sendCommentNotifications((int)$id, $comment->id);

        // Change status if different
        if ($newStatus !== $ticket->status) {
            $this->ticketService->changeStatus($ticket, $newStatus, $userId);
        }

        $successMessage = 'Comentario agregado exitosamente.';
        if ($uploadedCount > 0) {
            $successMessage .= " ({$uploadedCount} archivo(s) adjunto(s))";
        }
        $this->Flash->success($successMessage);
        return $this->redirect(['action' => 'index']);
    }

    /**
     * Assign ticket to agent
     *
     * @param string|null $id Ticket id
     * @return \Cake\Http\Response|null|void Redirects back
     */
    public function assign($id = null)
    {
        $this->request->allowMethod(['post', 'put']);

        $ticket = $this->Tickets->get($id);
        $agentId = (int)$this->request->getData('agent_id');
        $userId = $this->request->getAttribute('identity')['id'] ?? 1;

        if ($this->ticketService->assignTicket($ticket, $agentId, $userId)) {
            $this->Flash->success('Ticket asignado exitosamente.');
        } else {
            $this->Flash->error('Error al asignar el ticket.');
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Change ticket status
     *
     * @param string|null $id Ticket id
     * @return \Cake\Http\Response|null|void Redirects back
     */
    public function changeStatus($id = null)
    {
        $this->request->allowMethod(['post', 'put']);

        $ticket = $this->Tickets->get($id);
        $newStatus = $this->request->getData('status');
        $userId = $this->request->getAttribute('identity')['id'] ?? 1;

        if ($this->ticketService->changeStatus($ticket, $newStatus, $userId)) {
            $this->Flash->success('Estado del ticket actualizado.');
        } else {
            $this->Flash->error('Error al cambiar el estado.');
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Change ticket priority
     *
     * @param string|null $id Ticket id
     * @return \Cake\Http\Response|null|void Redirects back
     */
    public function changePriority($id = null)
    {
        $this->request->allowMethod(['post', 'put']);

        $ticket = $this->Tickets->get($id);
        $newPriority = $this->request->getData('priority');

        $ticket->priority = $newPriority;

        if ($this->Tickets->save($ticket)) {
            $this->Flash->success('Prioridad actualizada.');
        } else {
            $this->Flash->error('Error al cambiar la prioridad.');
        }

        return $this->redirect(['action' => 'view', $id]);
    }

    /**
     * Add tag to ticket
     *
     * @param string|null $id Ticket id
     * @return \Cake\Http\Response|null|void Redirects back
     */
    public function addTag($id = null)
    {
        $this->request->allowMethod(['post']);

        // Verify ticket exists
        $this->Tickets->get($id);
        $tagId = (int)$this->request->getData('tag_id');

        $ticketTagsTable = $this->fetchTable('TicketTags');

        // Check if already exists
        $exists = $ticketTagsTable->find()
            ->where(['ticket_id' => $id, 'tag_id' => $tagId])
            ->count();

        if ($exists) {
            $this->Flash->warning('Esta etiqueta ya está agregada.');
            return $this->redirect(['action' => 'view', $id]);
        }

        $ticketTag = $ticketTagsTable->newEntity([
            'ticket_id' => $id,
            'tag_id' => $tagId,
        ]);

        if ($ticketTagsTable->save($ticketTag)) {
            $this->Flash->success('Etiqueta agregada.');
        } else {
            $this->Flash->error('Error al agregar la etiqueta.');
        }

        return $this->redirect(['action' => 'view', $id]);
    }

    /**
     * Remove tag from ticket
     *
     * @param string|null $id Ticket id
     * @param string|null $tagId Tag id
     * @return \Cake\Http\Response|null|void Redirects back
     */
    public function removeTag($id = null, $tagId = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $ticketTagsTable = $this->fetchTable('TicketTags');

        $ticketTag = $ticketTagsTable->find()
            ->where(['ticket_id' => $id, 'tag_id' => $tagId])
            ->first();

        if ($ticketTag && $ticketTagsTable->delete($ticketTag)) {
            $this->Flash->success('Etiqueta eliminada.');
        } else {
            $this->Flash->error('Error al eliminar la etiqueta.');
        }

        return $this->redirect(['action' => 'view', $id]);
    }

    /**
     * Add follower to ticket
     *
     * @param string|null $id Ticket id
     * @return \Cake\Http\Response|null|void Redirects back
     */
    public function addFollower($id = null)
    {
        $this->request->allowMethod(['post']);

        $userId = (int)$this->request->getData('user_id');

        $followersTable = $this->fetchTable('TicketFollowers');

        // Check if already following
        $exists = $followersTable->find()
            ->where(['ticket_id' => $id, 'user_id' => $userId])
            ->count();

        if ($exists) {
            $this->Flash->warning('Este usuario ya está siguiendo el ticket.');
            return $this->redirect(['action' => 'view', $id]);
        }

        $follower = $followersTable->newEntity([
            'ticket_id' => $id,
            'user_id' => $userId,
        ]);

        if ($followersTable->save($follower)) {
            $this->Flash->success('Seguidor agregado.');
        } else {
            $this->Flash->error('Error al agregar seguidor.');
        }

        return $this->redirect(['action' => 'view', $id]);
    }

    /**
     * Download attachment
     *
     * @param string|null $id Attachment id
     * @return \Cake\Http\Response File download response
     */
    /**
     * Dashboard - Statistics and metrics
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function dashboard()
    {
        $ticketsTable = $this->fetchTable('Tickets');
        $usersTable = $this->fetchTable('Users');
        $commentsTable = $this->fetchTable('TicketComments');

        // Get current user
        $user = $this->Authentication->getIdentity();
        $userRole = $user ? $user->get('role') : null;

        // Get date range from query params
        $startDate = $this->request->getQuery('start_date');
        $endDate = $this->request->getQuery('end_date');
        $dateRange = $this->request->getQuery('range', 'all'); // all, today, week, month, custom

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
                'agent_name' => 'Assignees.name',
                'count' => $ticketsTable->find()->func()->count('*')
            ])
            ->group('assignee_id')
            ->order(['count' => 'DESC'])
            ->limit(5)
            ->all();

        // Average response time (hours)
        $avgResponseTime = $ticketsTable->find()
            ->where(['first_response_at IS NOT' => null])
            ->select([
                'avg_hours' => $ticketsTable->find()->func()->avg(
                    'TIMESTAMPDIFF(HOUR, created, first_response_at)'
                )
            ])
            ->first();

        // Average resolution time (hours)
        $avgResolutionTime = $ticketsTable->find()
            ->where(['resolved_at IS NOT' => null])
            ->select([
                'avg_hours' => $ticketsTable->find()->func()->avg(
                    'TIMESTAMPDIFF(HOUR, created, resolved_at)'
                )
            ])
            ->first();

        // Tickets created per day (last 30 days)
        $ticketsPerDay = $ticketsTable->find()
            ->where(['created >=' => new \DateTime('-30 days')])
            ->select([
                'date' => 'DATE(created)',
                'count' => $ticketsTable->find()->func()->count('*')
            ])
            ->group('DATE(created)')
            ->order(['date' => 'ASC'])
            ->all()
            ->combine('date', 'count')
            ->toArray();

        // Most active requesters (top 5)
        $topRequesters = $ticketsTable->find()
            ->contain(['Requesters'])
            ->select([
                'requester_id',
                'requester_name' => 'Requesters.name',
                'requester_email' => 'Requesters.email',
                'count' => $ticketsTable->find()->func()->count('*')
            ])
            ->group('requester_id')
            ->order(['count' => 'DESC'])
            ->limit(5)
            ->all();

        // Comments stats
        $totalComments = $commentsTable->find()->where(['is_system_comment' => 0])->count();
        $publicComments = $commentsTable->find()->where(['comment_type' => 'public'])->count();
        $internalComments = $commentsTable->find()->where(['comment_type' => 'internal', 'is_system_comment' => 0])->count();

        // Response rate (tickets with at least one response)
        $ticketsWithResponse = $ticketsTable->find()
            ->where(['first_response_at IS NOT' => null])
            ->count();
        $responseRate = $totalTickets > 0 ? round(($ticketsWithResponse / $totalTickets) * 100, 1) : 0;

        // Resolution rate (tickets resolved / total tickets)
        $resolvedCount = $ticketsTable->find()->where(['status' => 'resuelto'])->count();
        $resolutionRate = $totalTickets > 0 ? round(($resolvedCount / $totalTickets) * 100, 1) : 0;

        $this->set(compact(
            'totalTickets',
            'ticketsByStatus',
            'ticketsByPriority',
            'recentTickets',
            'recentResolved',
            'unassignedTickets',
            'activeAgents',
            'ticketsByAgent',
            'avgResponseTime',
            'avgResolutionTime',
            'ticketsPerDay',
            'topRequesters',
            'totalComments',
            'publicComments',
            'internalComments',
            'responseRate',
            'resolutionRate',
            'dateRange',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Bulk assign tickets to an agent
     *
     * @return \Cake\Http\Response|null|void Redirects on success
     */
    public function bulkAssign()
    {
        $this->request->allowMethod(['post']);

        $ticketIds = array_map('intval', explode(',', $this->request->getData('ticket_ids')));
        $agentId = (int)$this->request->getData('agent_id');
        $user = $this->Authentication->getIdentity();

        $successCount = 0;
        $errorCount = 0;

        foreach ($ticketIds as $ticketId) {
            try {
                $ticket = $this->Tickets->get($ticketId);
                $this->ticketService->assignTicket($ticket, $agentId, $user->get('id'));
                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
            }
        }

        if ($successCount > 0) {
            $this->Flash->success(__("{$successCount} ticket(s) asignado(s) correctamente."));
        }
        if ($errorCount > 0) {
            $this->Flash->error(__("{$errorCount} ticket(s) no pudieron ser asignados."));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Bulk change priority of tickets
     *
     * @return \Cake\Http\Response|null|void Redirects on success
     */
    public function bulkChangePriority()
    {
        $this->request->allowMethod(['post']);

        $ticketIds = array_map('intval', explode(',', $this->request->getData('ticket_ids')));
        $newPriority = $this->request->getData('priority');
        $user = $this->Authentication->getIdentity();

        $successCount = 0;
        $errorCount = 0;

        foreach ($ticketIds as $ticketId) {
            try {
                $ticket = $this->Tickets->get($ticketId);
                $ticket->priority = $newPriority;

                if ($this->Tickets->save($ticket)) {
                    // Log the change in ticket history
                    $ticketHistoryTable = $this->fetchTable('TicketHistory');
                    $ticketHistoryTable->logChange(
                        $ticket->id,
                        'priority',
                        $ticket->getOriginal('priority'),
                        $newPriority,
                        $user->get('id'),
                        "Prioridad cambiada de {$ticket->getOriginal('priority')} a {$newPriority}"
                    );

                    $successCount++;
                } else {
                    $errorCount++;
                }
            } catch (\Exception $e) {
                $errorCount++;
            }
        }

        if ($successCount > 0) {
            $this->Flash->success(__("{$successCount} ticket(s) actualizado(s) correctamente."));
        }
        if ($errorCount > 0) {
            $this->Flash->error(__("{$errorCount} ticket(s) no pudieron ser actualizados."));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Bulk add tag to tickets
     *
     * @return \Cake\Http\Response|null|void Redirects on success
     */
    public function bulkAddTag()
    {
        $this->request->allowMethod(['post']);

        $ticketIds = array_map('intval', explode(',', $this->request->getData('ticket_ids')));
        $tagId = (int)$this->request->getData('tag_id');

        $successCount = 0;
        $errorCount = 0;

        $ticketTagsTable = $this->fetchTable('TicketTags');

        foreach ($ticketIds as $ticketId) {
            try {
                // Check if tag is already added to this ticket
                $exists = $ticketTagsTable->exists([
                    'ticket_id' => $ticketId,
                    'tag_id' => $tagId
                ]);

                if (!$exists) {
                    $ticketTag = $ticketTagsTable->newEntity([
                        'ticket_id' => $ticketId,
                        'tag_id' => $tagId
                    ]);

                    if ($ticketTagsTable->save($ticketTag)) {
                        $successCount++;
                    } else {
                        $errorCount++;
                    }
                } else {
                    $successCount++; // Already has the tag
                }
            } catch (\Exception $e) {
                $errorCount++;
            }
        }

        if ($successCount > 0) {
            $this->Flash->success(__("Etiqueta agregada a {$successCount} ticket(s)."));
        }
        if ($errorCount > 0) {
            $this->Flash->error(__("{$errorCount} ticket(s) no pudieron ser etiquetados."));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Bulk delete tickets
     *
     * @return \Cake\Http\Response|null|void Redirects on success
     */
    public function bulkDelete()
    {
        $this->request->allowMethod(['post']);

        $ticketIds = array_map('intval', explode(',', $this->request->getData('ticket_ids')));

        $successCount = 0;
        $errorCount = 0;

        foreach ($ticketIds as $ticketId) {
            try {
                $ticket = $this->Tickets->get($ticketId);

                if ($this->Tickets->delete($ticket)) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
            } catch (\Exception $e) {
                $errorCount++;
            }
        }

        if ($successCount > 0) {
            $this->Flash->success(__("{$successCount} ticket(s) eliminado(s) correctamente."));
        }
        if ($errorCount > 0) {
            $this->Flash->error(__("{$errorCount} ticket(s) no pudieron ser eliminados."));
        }

        return $this->redirect(['action' => 'index']);
    }

    public function downloadAttachment($id = null)
    {
        $attachmentsTable = $this->fetchTable('Attachments');
        $attachment = $attachmentsTable->get($id);

        $filePath = $this->attachmentService->getFullPath($attachment);

        if (!file_exists($filePath)) {
            throw new \Cake\Http\Exception\NotFoundException('Archivo no encontrado.');
        }

        return $this->response
            ->withFile($filePath, ['download' => true, 'name' => $attachment->original_filename])
            ->withType($attachment->mime_type);
    }
}
