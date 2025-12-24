<?php
declare(strict_types=1);

namespace App\Controller\Traits;

use Cake\Http\Response;
use App\Service\Traits\GenericAttachmentTrait;

/**
 * TicketSystemControllerTrait
 *
 * Shared controller logic for Tickets and PQRS management.
 * Eliminates ~135 lines of duplicated code across TicketsController and PqrsController.
 *
 * Methods:
 * - assignEntity() - Assign ticket/PQRS to agent (17 LOC → 3 LOC)
 * - changeEntityStatus() - Change status (14 LOC → 3 LOC)
 * - changeEntityPriority() - Change priority (15 LOC → 3 LOC)
 * - addEntityComment() - Add comment via ResponseService (24 LOC → 3 LOC)
 * - downloadEntityAttachment() - Download file (12 LOC → 3 LOC)
 *
 * Usage:
 * ```php
 * class TicketsController extends AppController {
 *     use TicketSystemControllerTrait;
 *
 *     public function assign($id = null) {
 *         return $this->assignEntity('ticket', (int)$id, $this->request->getData('agent_id'));
 *     }
 * }
 * ```
 *
 * @package App\Controller\Traits
 */
trait TicketSystemControllerTrait
{
    use GenericAttachmentTrait;
    /**
     * Assign entity (ticket or PQRS) to agent
     *
     * @param string $entityType 'ticket' or 'pqrs'
     * @param int $entityId Entity ID
     * @param mixed $assigneeId Agent ID (can be empty string, 0, or int)
     * @param string $redirectAction Action to redirect to (default: 'index')
     * @return Response Redirect response
     */
    protected function assignEntity(
        string $entityType,
        int $entityId,
        $assigneeId,
        string $redirectAction = 'index'
    ): Response {
        $this->request->allowMethod(['post', 'put']);

        // Normalize assignee ID (convert empty string or 0 to null for unassignment)
        $assigneeId = $this->normalizeAssigneeId($assigneeId);

        // Get current user
        $userId = $this->getCurrentUserId();

        // Get entity and service based on type
        if ($entityType === 'ticket') {
            $entity = $this->Tickets->get($entityId);
            $service = $this->ticketService;
            $entityName = 'Ticket';
        } elseif ($entityType === 'compra') {
            $entity = $this->Compras->get($entityId);
            $service = $this->comprasService;
            $entityName = 'Compra';
        } else {
            $entity = $this->Pqrs->get($entityId);
            $service = $this->pqrsService;
            $entityName = 'PQRS';
        }

        // Perform assignment
        $result = $service->assign($entity, $assigneeId, $userId);

        // Flash message
        if ($result) {
            $this->Flash->success(__("{$entityName} asignada correctamente."));
        } else {
            $this->Flash->error(__("No se pudo asignar la {$entityName}."));
        }

        return $this->redirect(['action' => $redirectAction]);
    }

    /**
     * Change entity (ticket or PQRS) status
     *
     * @param string $entityType 'ticket' or 'pqrs'
     * @param int $entityId Entity ID
     * @param string $newStatus New status value
     * @param string $redirectAction Action to redirect to (default: 'index')
     * @return Response Redirect response
     */
    protected function changeEntityStatus(
        string $entityType,
        int $entityId,
        string $newStatus,
        string $redirectAction = 'index'
    ): Response {
        $this->request->allowMethod(['post', 'put']);

        // Get current user
        $userId = $this->getCurrentUserId();

        // Get entity and service based on type
        if ($entityType === 'ticket') {
            $entity = $this->Tickets->get($entityId);
            $service = $this->ticketService;
        } elseif ($entityType === 'compra') {
            $entity = $this->Compras->get($entityId);
            $service = $this->comprasService;
        } else {
            $entity = $this->Pqrs->get($entityId);
            $service = $this->pqrsService;
        }

        // Perform status change
        $result = $service->changeStatus($entity, $newStatus, $userId);

        // Flash message
        if ($result) {
            $this->Flash->success(__('Estado actualizado correctamente.'));
        } else {
            $this->Flash->error(__('No se pudo actualizar el estado.'));
        }

        return $this->redirect(['action' => $redirectAction, $entityId]);
    }

    /**
     * Change entity (ticket or PQRS) priority
     *
     * @param string $entityType 'ticket' or 'pqrs'
     * @param int $entityId Entity ID
     * @param string $newPriority New priority value
     * @param string $redirectAction Action to redirect to (default: 'view')
     * @return Response Redirect response
     */
    protected function changeEntityPriority(
        string $entityType,
        int $entityId,
        string $newPriority,
        string $redirectAction = 'view'
    ): Response {
        $this->request->allowMethod(['post', 'put']);

        // Get current user
        $userId = $this->getCurrentUserId();

        // Get entity and service based on type
        if ($entityType === 'ticket') {
            $entity = $this->Tickets->get($entityId);
            $service = $this->ticketService;
        } elseif ($entityType === 'compra') {
            $entity = $this->Compras->get($entityId);
            $service = $this->comprasService;
        } else {
            $entity = $this->Pqrs->get($entityId);
            $service = $this->pqrsService;
        }

        // Perform priority change
        $result = $service->changePriority($entity, $newPriority, $userId);

        // Flash message
        if ($result) {
            $this->Flash->success(__('Prioridad actualizada correctamente.'));
        } else {
            $this->Flash->error(__('No se pudo actualizar la prioridad.'));
        }

        return $this->redirect(['action' => $redirectAction, $entityId]);
    }

    /**
     * Add comment to entity (ticket or PQRS)
     *
     * Uses ResponseService for unified processing (comment + files + status + notifications)
     *
     * @param string $entityType 'ticket' or 'pqrs'
     * @param int $entityId Entity ID
     * @return Response Redirect response
     */
    protected function addEntityComment(string $entityType, int $entityId): Response
    {
        $this->request->allowMethod(['post']);

        // Get current user
        $user = $this->Authentication->getIdentity();
        $userId = $user ? $user->get('id') : null;

        // Get form data and files
        $data = $this->request->getData();
        $files = $this->request->getUploadedFiles();

        // Process response via ResponseService (unified workflow)
        $result = $this->responseService->processResponse(
            $entityType,
            $entityId,
            (int) $userId,
            $data,
            $files
        );

        // Flash message
        if ($result['success']) {
            $this->Flash->success($result['message']);
        } else {
            $this->Flash->error($result['message']);
        }

        return $this->redirect(['action' => 'view', $entityId]);
    }

    /**
     * Download attachment for entity (ticket or PQRS)
     *
     * @param string $entityType 'ticket' or 'pqrs'
     * @param int $attachmentId Attachment ID
     * @return Response File download response
     * @throws \Cake\Http\Exception\NotFoundException If file not found
     */
    protected function downloadEntityAttachment(string $entityType, int $attachmentId): Response
    {
        // Get attachment table based on entity type
        if ($entityType === 'ticket') {
            $attachmentsTable = $this->fetchTable('Attachments');
            $attachment = $attachmentsTable->get($attachmentId);
            $filePath = $this->getFullPath($attachment);
        } elseif ($entityType === 'compra') {
            $attachmentsTable = $this->fetchTable('ComprasAttachments');
            $attachment = $attachmentsTable->get($attachmentId);
            $filePath = $this->getFullPath($attachment);
        } else {
            $attachmentsTable = $this->fetchTable('PqrsAttachments');
            $attachment = $attachmentsTable->get($attachmentId);
            $filePath = $this->getFullPath($attachment);
        }

        if (!file_exists($filePath)) {
            throw new \Cake\Http\Exception\NotFoundException('Archivo no encontrado.');
        }

        return $this->response
            ->withFile($filePath, ['download' => true, 'name' => $attachment->original_filename])
            ->withType($attachment->mime_type ?? 'application/octet-stream');
    }

    /**
     * Get current user ID from authentication
     *
     * @return int User ID (defaults to 1 if not authenticated)
     */
    protected function getCurrentUserId(): int
    {
        $user = $this->Authentication->getIdentity();
        return $user ? (int) $user->get('id') : 1;
    }

    /**
     * Normalize assignee ID value
     *
     * Converts empty string, '0', or 0 to null for unassignment
     *
     * @param mixed $value Raw assignee ID value
     * @return int|null Normalized assignee ID
     */
    protected function normalizeAssigneeId($value): ?int
    {
        return ($value === '' || $value === '0' || $value === 0) ? null : (int) $value;
    }

    /**
     * Handle service result with flash message and redirect
     *
     * Generic helper for services that return boolean results
     *
     * @param array<string, mixed> $result Service result array ['success' => bool, 'message' => string]
     * @param string $redirectUrl URL to redirect to
     * @return Response Redirect response
     */
    protected function handleServiceResult(array $result, string $redirectUrl): Response
    {
        if ($result['success']) {
            $this->Flash->success($result['message']);
        } else {
            $this->Flash->error($result['message']);
        }

        return $this->redirect($redirectUrl);
    }

    /**
     * Bulk assign entities to an agent
     *
     * Processes multiple entity IDs from comma-separated string
     *
     * @param string $entityType 'ticket', 'pqrs', or 'compra'
     * @return Response Redirect response
     */
    protected function bulkAssignEntity(string $entityType): Response
    {
        $this->request->allowMethod(['post']);

        $entityIds = array_map('intval', explode(',', $this->request->getData('entity_ids') ?? $this->request->getData('ticket_ids') ?? ''));
        $agentId = $this->request->getData('agent_id') ?? $this->request->getData('assignee_id');

        // Convert empty string or 0 to null for unassignment
        $agentId = $this->normalizeAssigneeId($agentId);

        $user = $this->Authentication->getIdentity();
        $userId = $user ? $user->get('id') : 1;

        // Get service and table based on entity type
        [$table, $service, $entityName] = $this->getEntityComponents($entityType);

        $successCount = 0;
        $errorCount = 0;

        foreach ($entityIds as $entityId) {
            try {
                $entity = $table->get($entityId);
                $service->assign($entity, $agentId, $userId);
                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
                \Cake\Log\Log::error("Error in bulk assign {$entityType} {$entityId}: " . $e->getMessage());
            }
        }

        if ($successCount > 0) {
            $this->Flash->success(__("{$successCount} {$entityName}(s) asignado(s) correctamente."));
        }
        if ($errorCount > 0) {
            $this->Flash->error(__("{$errorCount} {$entityName}(s) no pudieron ser asignados."));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Bulk change priority of entities
     *
     * @param string $entityType 'ticket', 'pqrs', or 'compra'
     * @return Response Redirect response
     */
    protected function bulkChangeEntityPriority(string $entityType): Response
    {
        $this->request->allowMethod(['post']);

        $entityIds = array_map('intval', explode(',', $this->request->getData('entity_ids') ?? $this->request->getData('ticket_ids') ?? ''));
        $newPriority = $this->request->getData('priority');

        $user = $this->Authentication->getIdentity();
        $userId = $user ? $user->get('id') : 1;

        // Get table and history table based on entity type
        [$table, $service, $entityName] = $this->getEntityComponents($entityType);
        $historyTable = $this->getHistoryTable($entityType);

        $successCount = 0;
        $errorCount = 0;

        foreach ($entityIds as $entityId) {
            try {
                $entity = $table->get($entityId);
                $oldPriority = $entity->priority;
                $entity->priority = $newPriority;

                if ($table->save($entity)) {
                    // Log the change in history
                    $historyTable->logChange(
                        $entity->id,
                        'priority',
                        $oldPriority,
                        $newPriority,
                        $userId,
                        "Prioridad cambiada de {$oldPriority} a {$newPriority}"
                    );

                    $successCount++;
                } else {
                    $errorCount++;
                }
            } catch (\Exception $e) {
                $errorCount++;
                \Cake\Log\Log::error("Error in bulk priority change {$entityType} {$entityId}: " . $e->getMessage());
            }
        }

        if ($successCount > 0) {
            $this->Flash->success(__("{$successCount} {$entityName}(s) actualizado(s) correctamente."));
        }
        if ($errorCount > 0) {
            $this->Flash->error(__("{$errorCount} {$entityName}(s) no pudieron ser actualizados."));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Bulk add tag to entities
     *
     * Only applicable to Tickets (PQRS and Compras would need tag tables first)
     *
     * @param string $entityType 'ticket', 'pqrs', or 'compra'
     * @return Response Redirect response
     */
    protected function bulkAddTagEntity(string $entityType): Response
    {
        $this->request->allowMethod(['post']);

        $entityIds = array_map('intval', explode(',', $this->request->getData('entity_ids') ?? $this->request->getData('ticket_ids') ?? ''));
        $tagId = (int) $this->request->getData('tag_id');

        [, , $entityName] = $this->getEntityComponents($entityType);

        // Get tags table based on entity type
        $tagsTableName = $this->getTagsTableName($entityType);
        $tagsTable = $this->fetchTable($tagsTableName);

        // Get the foreign key field name
        $foreignKey = $entityType . '_id';

        $successCount = 0;
        $errorCount = 0;

        foreach ($entityIds as $entityId) {
            try {
                // Check if tag is already added to this entity
                $exists = $tagsTable->exists([
                    $foreignKey => $entityId,
                    'tag_id' => $tagId
                ]);

                if (!$exists) {
                    $entityTag = $tagsTable->newEntity([
                        $foreignKey => $entityId,
                        'tag_id' => $tagId
                    ]);

                    if ($tagsTable->save($entityTag)) {
                        $successCount++;
                    } else {
                        $errorCount++;
                    }
                } else {
                    $successCount++; // Already has the tag
                }
            } catch (\Exception $e) {
                $errorCount++;
                \Cake\Log\Log::error("Error in bulk tag add {$entityType} {$entityId}: " . $e->getMessage());
            }
        }

        if ($successCount > 0) {
            $this->Flash->success(__("Etiqueta agregada a {$successCount} {$entityName}(s)."));
        }
        if ($errorCount > 0) {
            $this->Flash->error(__("{$errorCount} {$entityName}(s) no pudieron ser etiquetados."));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Bulk delete entities
     *
     * @param string $entityType 'ticket', 'pqrs', or 'compra'
     * @return Response Redirect response
     */
    protected function bulkDeleteEntity(string $entityType): Response
    {
        $this->request->allowMethod(['post']);

        $entityIds = array_map('intval', explode(',', $this->request->getData('entity_ids') ?? $this->request->getData('ticket_ids') ?? ''));

        [$table, , $entityName] = $this->getEntityComponents($entityType);

        $successCount = 0;
        $errorCount = 0;

        foreach ($entityIds as $entityId) {
            try {
                $entity = $table->get($entityId);

                if ($table->delete($entity)) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
            } catch (\Exception $e) {
                $errorCount++;
                \Cake\Log\Log::error("Error in bulk delete {$entityType} {$entityId}: " . $e->getMessage());
            }
        }

        if ($successCount > 0) {
            $this->Flash->success(__("{$successCount} {$entityName}(s) eliminado(s) correctamente."));
        }
        if ($errorCount > 0) {
            $this->Flash->error(__("{$errorCount} {$entityName}(s) no pudieron ser eliminados."));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Get entity components (table, service, display name) based on type
     *
     * @param string $entityType 'ticket', 'pqrs', or 'compra'
     * @return array Associative array with keys: table, service, displayName, tableName, foreignKey
     */
    private function getEntityComponents(string $entityType): array
    {
        $components = match ($entityType) {
            'ticket' => [
                'table' => $this->Tickets ?? $this->fetchTable('Tickets'),
                'service' => $this->ticketService ?? null,
                'displayName' => 'Ticket',
                'tableName' => 'Tickets',
                'foreignKey' => 'ticket_id',
            ],
            'pqrs' => [
                'table' => $this->Pqrs ?? $this->fetchTable('Pqrs'),
                'service' => $this->pqrsService ?? null,
                'displayName' => 'PQRS',
                'tableName' => 'Pqrs',
                'foreignKey' => 'pqrs_id',
            ],
            'compra' => [
                'table' => $this->Compras ?? $this->fetchTable('Compras'),
                'service' => $this->comprasService ?? null,
                'displayName' => 'Compra',
                'tableName' => 'Compras',
                'foreignKey' => 'compra_id',
            ],
            default => throw new \InvalidArgumentException("Invalid entity type: {$entityType}"),
        };

        // For backward compatibility, also add numeric indices
        return array_merge($components, [
            0 => $components['table'],
            1 => $components['service'],
            2 => $components['displayName'],
        ]);
    }

    /**
     * Get history table based on entity type
     *
     * @param string $entityType 'ticket', 'pqrs', or 'compra'
     * @return \Cake\ORM\Table History table instance
     */
    private function getHistoryTable(string $entityType): \Cake\ORM\Table
    {
        return match ($entityType) {
            'ticket' => $this->fetchTable('TicketHistory'),
            'pqrs' => $this->fetchTable('PqrsHistory'),
            'compra' => $this->fetchTable('ComprasHistory'),
            default => throw new \InvalidArgumentException("Invalid entity type: {$entityType}"),
        };
    }

    /**
     * Get tags table name based on entity type
     *
     * @param string $entityType 'ticket', 'pqrs', or 'compra'
     * @return string Tags table name
     */
    private function getTagsTableName(string $entityType): string
    {
        return match ($entityType) {
            'ticket' => 'TicketTags',
            'pqrs' => 'PqrsTags',    // TODO: Create this table
            'compra' => 'ComprasTags', // TODO: Create this table
            default => throw new \InvalidArgumentException("Invalid entity type: {$entityType}"),
        };
    }

    /**
     * Generic index method for listing entities with filters, sorting, and pagination
     *
     * Eliminates ~300 lines of duplicated code across TicketsController, PqrsController, ComprasController
     *
     * @param string $entityType 'ticket', 'pqrs', or 'compra'
     * @param array $config Configuration options:
     *   - defaultView: Default view filter (default: 'todos_sin_resolver')
     *   - defaultSort: Default sort field (default: 'created')
     *   - defaultDirection: Default sort direction (default: 'desc')
     *   - paginationLimit: Items per page (default: 10)
     *   - contain: Associations to contain (default: auto-detected)
     *   - validSortFields: Valid fields for sorting (default: auto-detected)
     *   - filterParams: Additional filter parameters specific to entity (default: [])
     *   - usersRoleFilter: Role filter for users dropdown (default: auto-detected)
     *   - additionalViewVars: Additional variables to pass to view (default: [])
     *   - beforeQuery: Callback to modify query before pagination (default: null)
     * @return void Sets view variables
     */
    protected function indexEntity(string $entityType, array $config = []): void
    {
        // Default configuration
        $defaults = [
            'defaultView' => 'todos_sin_resolver',
            'defaultSort' => 'created',
            'defaultDirection' => 'desc',
            'paginationLimit' => 10,
            'contain' => null,
            'validSortFields' => null,
            'filterParams' => [],
            'usersRoleFilter' => null,
            'additionalViewVars' => [],
            'beforeQuery' => null,
            'specialRedirects' => null,
        ];
        $config = array_merge($defaults, $config);

        // Get current user
        $user = $this->Authentication->getIdentity();
        $userRole = $user ? $user->get('role') : null;

        // Handle special redirects (e.g., Gmail OAuth, role-based redirects)
        if (is_callable($config['specialRedirects'])) {
            $redirect = $config['specialRedirects']($this->request, $user, $userRole);
            if ($redirect !== null) {
                return; // Redirect handled by callback
            }
        }

        // Get view and filter parameters
        $view = $this->request->getQuery('view', $config['defaultView']);
        $search = $this->request->getQuery('search');
        $filterStatus = $this->request->getQuery('filter_status');
        $filterPriority = $this->request->getQuery('filter_priority');
        $filterAssignee = $this->request->getQuery('filter_assignee');
        $filterDateFrom = $this->request->getQuery('filter_date_from');
        $filterDateTo = $this->request->getQuery('filter_date_to');
        $sortField = $this->request->getQuery('sort', $config['defaultSort']);
        $sortDirection = $this->request->getQuery('direction', $config['defaultDirection']);

        // Get entity-specific filter params (e.g., type for PQRS, organization for Tickets)
        $additionalFilters = [];
        foreach ($config['filterParams'] as $paramName => $queryKey) {
            $additionalFilters[$paramName] = $this->request->getQuery($queryKey);
        }

        // Get table and metadata
        [$table, , $entityName] = $this->getEntityComponents($entityType);
        $tableAlias = $table->getAlias();
        $entityVariable = $this->getEntityVariable($entityType);

        // Build query options
        $queryOptions = [
            'view' => $view,
            'filters' => array_merge([
                'search' => $search,
                'status' => $filterStatus,
                'priority' => $filterPriority,
                'assignee_id' => $filterAssignee,
                'date_from' => $filterDateFrom,
                'date_to' => $filterDateTo,
            ], $additionalFilters),
            'user' => $user
        ];

        // Build query using custom finder
        $query = $table->find('withFilters', $queryOptions);

        // Apply contain (associations)
        if ($config['contain'] !== null) {
            $query->contain($config['contain']);
        } else {
            // Auto-detect contain based on entity type
            $query->contain($this->getDefaultContain($entityType));
        }

        // Apply sorting
        $validSortFields = $config['validSortFields'] ?? $this->getValidSortFields($entityType);
        if (in_array($sortField, $validSortFields)) {
            $query->orderBy([$tableAlias . '.' . $sortField => strtoupper($sortDirection)]);
        } else {
            $query->orderBy([$tableAlias . '.' . $config['defaultSort'] => 'DESC']);
        }

        // Apply role-based permissions
        $this->applyRoleBasedFilters($query, $entityType, $user, $userRole, $tableAlias);

        // Allow custom modifications before pagination
        if (is_callable($config['beforeQuery'])) {
            $config['beforeQuery']($query, $user, $userRole);
        }

        // Paginate
        $entities = $this->paginate($query, [
            'limit' => $config['paginationLimit'],
        ]);

        // Get filter data for view
        $filterData = $this->getFilterDataForView($entityType, $config);

        // Build filters variable for view
        $filters = compact(
            'search',
            'filterStatus',
            'filterPriority',
            'filterAssignee',
            'filterDateFrom',
            'filterDateTo',
            'sortField',
            'sortDirection'
        );

        // Add entity-specific filters
        foreach ($config['filterParams'] as $paramName => $queryKey) {
            $filterVarName = 'filter' . ucfirst($paramName);
            $filters[$filterVarName] = $this->request->getQuery($queryKey);
        }

        // Set view variables
        $viewVars = [
            $entityVariable => $entities,
            'view' => $view,
            'filters' => $filters,
        ];

        // Add filter data (users, statuses, priorities, etc.)
        $viewVars = array_merge($viewVars, $filterData);

        // Add additional custom view variables
        $viewVars = array_merge($viewVars, $config['additionalViewVars']);

        $this->set($viewVars);
    }

    /**
     * Apply role-based filters to query
     *
     * @param \Cake\ORM\Query $query Query object
     * @param string $entityType Entity type
     * @param mixed $user Current user
     * @param string|null $userRole User role
     * @param string $tableAlias Table alias
     * @return void Modifies query by reference
     */
    private function applyRoleBasedFilters($query, string $entityType, $user, ?string $userRole, string $tableAlias): void
    {
        if (!$user || !$userRole) {
            return;
        }

        $userId = $user->get('id');

        // Common pattern: Requesters only see their own entities
        if ($userRole === 'requester' && $entityType === 'ticket') {
            $query->where([$tableAlias . '.requester_id' => $userId]);
        }

        // Compras role specific filters (for tickets)
        if ($userRole === 'compras' && $entityType === 'ticket') {
            $query->where([
                'OR' => [
                    $tableAlias . '.assignee_id' => $userId,
                    'AND' => [
                        $tableAlias . '.assignee_id' => $userId,
                        $tableAlias . '.status' => 'resuelto'
                    ]
                ]
            ]);
        }

        // Agent role: exclude tickets assigned to compras users
        if ($userRole === 'agent' && $entityType === 'ticket') {
            // PERFORMANCE: Cache compras user IDs
            $cacheKey = 'compras_user_ids';
            $comprasUserIds = \Cake\Cache\Cache::remember($cacheKey, function () {
                return $this->fetchTable('Users')
                    ->find()
                    ->select(['id'])
                    ->where(['role' => 'compras'])
                    ->all()
                    ->extract('id')
                    ->toArray();
            }, '_cake_core_');

            if (!empty($comprasUserIds)) {
                $query->where([
                    'OR' => [
                        $tableAlias . '.assignee_id IS' => null,
                        $tableAlias . '.assignee_id NOT IN' => $comprasUserIds,
                    ],
                ]);
            }
        }
    }

    /**
     * Get default contain associations based on entity type
     *
     * @param string $entityType Entity type
     * @return array Contain array
     */
    private function getDefaultContain(string $entityType): array
    {
        return match ($entityType) {
            'ticket' => ['Requesters' => ['Organizations'], 'Assignees'],
            'pqrs' => ['Assignees'],
            'compra' => ['Requesters', 'Assignees'],
            default => [],
        };
    }

    /**
     * Get valid sort fields based on entity type
     *
     * @param string $entityType Entity type
     * @return array Valid sort fields
     */
    private function getValidSortFields(string $entityType): array
    {
        $common = ['created', 'modified', 'status', 'priority', 'subject'];

        return match ($entityType) {
            'ticket' => array_merge($common, ['ticket_number']),
            'pqrs' => array_merge($common, ['pqrs_number', 'type']),
            'compra' => array_merge($common, ['compra_number']),
            default => $common,
        };
    }

    /**
     * Get entity variable name for view (plural form for index)
     *
     * @param string $entityType Entity type
     * @return string Variable name
     */
    private function getEntityVariable(string $entityType): string
    {
        return match ($entityType) {
            'ticket' => 'tickets',
            'pqrs' => 'pqrs',
            'compra' => 'compras',
            default => $entityType . 's',
        };
    }

    /**
     * Get single entity variable name for view (singular form for view)
     *
     * @param string $entityType Entity type
     * @return string Variable name
     */
    private function getSingleEntityVariable(string $entityType): string
    {
        return match ($entityType) {
            'ticket' => 'ticket',
            'pqrs' => 'pqrs',
            'compra' => 'compra',
            default => $entityType,
        };
    }

    /**
     * Get filter data for view (users, statuses, priorities, etc.)
     *
     * @param string $entityType Entity type
     * @param array $config Configuration
     * @return array Filter data
     */
    private function getFilterDataForView(string $entityType, array $config): array
    {
        $data = [];

        // Get users for assignment dropdown (role-based)
        $usersRoleFilter = $config['usersRoleFilter'] ?? $this->getDefaultUsersRoleFilter($entityType);
        if ($usersRoleFilter !== null) {
            $usersVarName = $this->getUsersVariableName($entityType);
            $data[$usersVarName] = $this->fetchTable('Users')
                ->find('list')
                ->where(['role IN' => $usersRoleFilter, 'is_active' => true])
                ->toArray();
        }

        // Get priorities
        $data['priorities'] = [
            'baja' => 'Baja',
            'media' => 'Media',
            'alta' => 'Alta',
            'urgente' => 'Urgente',
        ];

        // Get statuses (entity-specific)
        $data['statuses'] = $this->getStatusesForEntity($entityType);

        // Get entity-specific data
        if ($entityType === 'ticket') {
            $data['organizations'] = $this->fetchTable('Organizations')->find('list')->toArray();
            $data['tags'] = $this->fetchTable('Tags')->find()->toArray();
        } elseif ($entityType === 'pqrs') {
            $data['types'] = [
                'peticion' => 'Petición',
                'queja' => 'Queja',
                'reclamo' => 'Reclamo',
                'sugerencia' => 'Sugerencia',
            ];
        }

        return $data;
    }

    /**
     * Get default users role filter based on entity type
     *
     * @param string $entityType Entity type
     * @return array|null Role filter
     */
    private function getDefaultUsersRoleFilter(string $entityType): ?array
    {
        return match ($entityType) {
            'ticket' => ['admin', 'agent', 'compras'],
            'pqrs' => ['servicio_cliente'],
            'compra' => ['compras'],
            default => null,
        };
    }

    /**
     * Get users variable name for view
     *
     * @param string $entityType Entity type
     * @return string Variable name
     */
    private function getUsersVariableName(string $entityType): string
    {
        return match ($entityType) {
            'ticket' => 'agents',
            'pqrs' => 'users',
            'compra' => 'comprasUsers',
            default => 'users',
        };
    }

    /**
     * Get statuses for entity
     *
     * @param string $entityType Entity type
     * @return array Statuses
     */
    private function getStatusesForEntity(string $entityType): array
    {
        return match ($entityType) {
            'ticket' => [
                'nuevo' => 'Nuevo',
                'abierto' => 'Abierto',
                'pendiente' => 'Pendiente',
                'resuelto' => 'Resuelto',
                'cerrado' => 'Cerrado',
            ],
            'pqrs' => [
                'nuevo' => 'Nuevo',
                'en_revision' => 'En Revisión',
                'en_proceso' => 'En Proceso',
                'resuelto' => 'Resuelto',
                'cerrado' => 'Cerrado',
            ],
            'compra' => [
                'nuevo' => 'Nuevo',
                'en_revision' => 'En Revisión',
                'aprobado' => 'Aprobado',
                'en_proceso' => 'En Proceso',
                'completado' => 'Completado',
                'rechazado' => 'Rechazado',
            ],
            default => [],
        };
    }

    /**
     * View single entity with all related data
     *
     * Generic method that works for all entity types (ticket, pqrs, compra)
     *
     * Configuration options:
     * - contain: array - Associations to load (auto-detected if not provided)
     * - lazyLoadHistory: bool - Whether to lazy load history via AJAX (default: false)
     * - permissionCheck: callable - Custom permission check callback
     * - agentsRoleFilter: array - Roles to filter for agents dropdown (auto-detected)
     * - additionalViewVars: array - Additional variables to pass to view
     * - beforeSet: callable - Callback to run before setting view vars
     *
     * @param string $entityType Entity type ('ticket', 'pqrs', 'compra')
     * @param int $id Entity id
     * @param array $config Configuration options
     * @return \Cake\Http\Response|null Redirect response if permission denied, null otherwise
     */
    protected function viewEntity(string $entityType, int $id, array $config = []): ?\Cake\Http\Response
    {
        // Get entity components
        $components = $this->getEntityComponents($entityType);
        $tableName = $components['tableName'];
        $variableName = $this->getSingleEntityVariable($entityType);

        // Get contain configuration
        $contain = $config['contain'] ?? $this->getDefaultViewContain($entityType, $config['lazyLoadHistory'] ?? false);

        // Load entity with associations
        $entity = $this->fetchTable($tableName)->get($id, compact('contain'));

        // Permission check (if provided)
        if (isset($config['permissionCheck']) && is_callable($config['permissionCheck'])) {
            $permissionResult = $config['permissionCheck']($entity);
            if ($permissionResult !== null) {
                return $permissionResult;
            }
        }

        // Get agents for assignment dropdown
        $agentsRoleFilter = $config['agentsRoleFilter'] ?? $this->getDefaultAgentsRoleFilter($entityType);
        $agents = $this->fetchTable('Users')
            ->find('list')
            ->where(['role IN' => $agentsRoleFilter, 'is_active' => true])
            ->toArray();

        // Prepare view variables
        $viewVars = [
            $variableName => $entity,
            'agents' => $agents,
        ];

        // Add additional view vars from config
        if (isset($config['additionalViewVars'])) {
            $viewVars = array_merge($viewVars, $config['additionalViewVars']);
        }

        // Run beforeSet callback if provided
        if (isset($config['beforeSet']) && is_callable($config['beforeSet'])) {
            $viewVars = $config['beforeSet']($entity, $viewVars);
        }

        $this->set($viewVars);

        return null;
    }

    /**
     * Get default contain for view method
     *
     * @param string $entityType Entity type
     * @param bool $lazyLoadHistory Whether to lazy load history
     * @return array Contain configuration
     */
    private function getDefaultViewContain(string $entityType, bool $lazyLoadHistory = false): array
    {
        $contain = match ($entityType) {
            'ticket' => [
                'Requesters' => ['Organizations'],
                'Assignees',
                'TicketComments' => ['Users'],
                'Attachments',
                'Tags',
                'TicketFollowers' => ['Users'],
            ],
            'pqrs' => [
                'Assignees',
                'PqrsComments' => [
                    'Users',
                    'PqrsAttachments',
                    'sort' => ['PqrsComments.created' => 'ASC']
                ],
                'PqrsAttachments',
            ],
            'compra' => [
                'Requesters',
                'Assignees',
                'ComprasComments' => ['Users'],
                'ComprasAttachments',
            ],
            default => [],
        };

        // Add history if not lazy loading
        if (!$lazyLoadHistory) {
            $historyAssoc = match ($entityType) {
                'ticket' => 'TicketHistory',
                'pqrs' => 'PqrsHistory',
                'compra' => 'ComprasHistory',
                default => null,
            };

            if ($historyAssoc) {
                $contain[$historyAssoc] = [
                    'Users',
                    'sort' => [$historyAssoc . '.created' => 'DESC']
                ];
            }
        }

        return $contain;
    }

    /**
     * Get default agents role filter for entity type
     *
     * @param string $entityType Entity type
     * @return array Role filters
     */
    private function getDefaultAgentsRoleFilter(string $entityType): array
    {
        return match ($entityType) {
            'ticket' => ['admin', 'agent', 'compras'],
            'pqrs' => ['servicio_cliente'],
            'compra' => ['compras'],
            default => ['admin', 'agent'],
        };
    }

    /**
     * AJAX endpoint for lazy loading entity history
     * PERFORMANCE FIX: Only loads when history tab is opened
     *
     * Generic method that works for all entity types (ticket, pqrs, compra)
     *
     * @param string $entityType Entity type ('ticket', 'pqrs', 'compra')
     * @param int $id Entity id
     * @return void Sets JSON response
     */
    protected function historyEntity(string $entityType, int $id): void
    {
        $this->request->allowMethod(['get']);
        $this->viewBuilder()->setClassName('Json');

        try {
            // Get current user for permission check
            $user = $this->Authentication->getIdentity();
            if (!$user) {
                $this->set('error', 'No autenticado');
                $this->viewBuilder()->setOption('serialize', ['error']);
                $this->response = $this->response->withStatus(401);
                return;
            }

            // Get entity components
            $components = $this->getEntityComponents($entityType);
            $tableName = $components['tableName'];
            $foreignKey = $components['foreignKey'];

            // Get entity to verify permissions
            $entity = $this->fetchTable($tableName)->get($id);

            // Simple permission check for AJAX (no redirects/flash)
            $userRole = $user->get('role');
            $userId = $user->get('id');

            // Requester can only view their own entities
            if ($userRole === 'requester' && $entity->requester_id !== $userId) {
                $this->set('error', 'No tienes permiso para ver este historial');
                $this->viewBuilder()->setOption('serialize', ['error']);
                $this->response = $this->response->withStatus(403);
                return;
            }

            // Compras can only view entities assigned to them
            if ($userRole === 'compras' && $entity->assignee_id !== $userId) {
                $this->set('error', 'No tienes permiso para ver este historial');
                $this->viewBuilder()->setOption('serialize', ['error']);
                $this->response = $this->response->withStatus(403);
                return;
            }

            // Get history table name
            $historyTable = $this->getHistoryTable($entityType);

            // Load history with users
            $history = $historyTable
                ->find()
                ->where([$foreignKey => $id])
                ->contain(['Users'])
                ->order([$historyTable->getAlias() . '.created' => 'DESC'])
                ->all();

            // Format for JSON response
            $formattedHistory = [];
            foreach ($history as $entry) {
                $userData = null;
                if ($entry->user) {
                    $userData = [
                        'id' => $entry->user->id,
                        'name' => $entry->user->name,
                    ];
                } else {
                    $userData = [
                        'id' => null,
                        'name' => 'Sistema',
                    ];
                }

                $formattedHistory[] = [
                    'id' => $entry->id,
                    'field_name' => $entry->field_name,
                    'old_value' => $entry->old_value,
                    'new_value' => $entry->new_value,
                    'description' => $entry->description,
                    'created' => $entry->created->format('Y-m-d H:i:s'),
                    'user' => $userData,
                ];
            }

            // Success response
            $this->set('history', $formattedHistory);
            $this->viewBuilder()->setOption('serialize', ['history']);
        } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
            // Entity not found
            \Cake\Log\Log::warning(ucfirst($entityType) . ' not found for history: ' . $id);
            $this->set('error', ucfirst($entityType) . ' no encontrado');
            $this->viewBuilder()->setOption('serialize', ['error']);
            $this->response = $this->response->withStatus(404);
        } catch (\Exception $e) {
            // Log error for debugging
            \Cake\Log\Log::error('Error loading ' . $entityType . ' history: ' . $e->getMessage(), [
                $entityType . '_id' => $id,
                'exception' => $e,
            ]);

            $this->set('error', 'Error al cargar el historial');
            $this->viewBuilder()->setOption('serialize', ['error']);
            $this->response = $this->response->withStatus(500);
        }
    }
}
