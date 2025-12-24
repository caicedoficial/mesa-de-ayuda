<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\Traits\TicketSystemControllerTrait;
use App\Service\ComprasService;
use App\Service\ResponseService;
use Cake\Event\EventInterface;

/**
 * Compras Controller
 *
 * Sistema de gestión de compras convertidas desde tickets
 * Solo accesible para roles: admin y compras
 *
 * @property \App\Model\Table\ComprasTable $Compras
 */
class ComprasController extends AppController
{
    use TicketSystemControllerTrait;

    private ComprasService $comprasService;
    private ResponseService $responseService;

    /**
     * beforeFilter callback - Restrict access to admin and compras roles only
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        $user = $this->Authentication->getIdentity();
        $allowedRoles = ['admin', 'compras'];

        if ($user && !in_array($user->get('role'), $allowedRoles)) {
            $this->Flash->error(__('No tienes permiso para acceder al módulo de compras.'));
            return $this->redirect(['controller' => 'Tickets', 'action' => 'index']);
        }
    }

    /**
     * Initialize
     */
    public function initialize(): void
    {
        parent::initialize();

        $systemConfig = $this->viewBuilder()->getVar('systemConfig');
        $this->comprasService = new ComprasService($systemConfig);
        $this->responseService = new ResponseService($systemConfig);
    }

    /**
     * Index method - List compras with filters
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->indexEntity('compra', [
            'paginationLimit' => 25,
        ]);
    }

    /**
     * View method - Display individual compra
     *
     * @param int|null $id Compra id
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function view($id = null)
    {
        return $this->viewEntity('compra', (int)$id, [
            'lazyLoadHistory' => true, // PERFORMANCE FIX: Load history via AJAX
            'beforeSet' => function($compra, $viewVars) {
                // Check SLA status
                $isSLABreached = $this->comprasService->isSLABreached($compra);

                // Get users for assignment with formatted names
                $comprasUsers = $this->fetchTable('Users')->find()
                    ->where(['role' => 'compras', 'is_active' => true])
                    ->orderBy(['first_name' => 'ASC', 'last_name' => 'ASC'])
                    ->all()
                    ->combine('id', function ($user) {
                        return $user->first_name . ' ' . $user->last_name;
                    })
                    ->toArray();

                $priorities = [
                    'baja' => 'Baja',
                    'media' => 'Media',
                    'alta' => 'Alta',
                    'urgente' => 'Urgente',
                ];

                // Status configuration for reply editor (with icon, color, label)
                $statuses = [
                    'nuevo' => ['icon' => 'bi-circle-fill', 'color' => 'info', 'label' => 'Nuevo'],
                    'en_revision' => ['icon' => 'bi-circle-fill', 'color' => 'warning', 'label' => 'En Revisión'],
                    'aprobado' => ['icon' => 'bi-circle-fill', 'color' => 'success', 'label' => 'Aprobado'],
                    'en_proceso' => ['icon' => 'bi-circle-fill', 'color' => 'primary', 'label' => 'En Proceso'],
                    'completado' => ['icon' => 'bi-circle-fill', 'color' => 'success', 'label' => 'Completado'],
                    'rechazado' => ['icon' => 'bi-circle-fill', 'color' => 'danger', 'label' => 'Rechazado'],
                ];

                // Merge with existing view vars
                return array_merge($viewVars, compact('comprasUsers', 'priorities', 'statuses', 'isSLABreached'));
            },
        ]);
    }

    /**
     * Assign compra to user (uses trait method)
     *
     * @param int|null $id Compra id
     * @return \Cake\Http\Response|null
     */
    public function assign($id = null)
    {
        return $this->assignEntity('compra', (int)$id, $this->request->getData('agent_id'));
    }

    /**
     * Change compra status (uses trait method)
     *
     * @param int|null $id Compra id
     * @return \Cake\Http\Response|null
     */
    public function changeStatus($id = null)
    {
        return $this->changeEntityStatus('compra', (int)$id, $this->request->getData('status'));
    }

    /**
     * Change compra priority (uses trait method)
     *
     * @param int|null $id Compra id
     * @return \Cake\Http\Response|null
     */
    public function changePriority($id = null)
    {
        return $this->changeEntityPriority('compra', (int)$id, $this->request->getData('priority'));
    }

    /**
     * Add comment to compra (uses trait method)
     *
     * @param int|null $id Compra id
     * @return \Cake\Http\Response|null
     */
    public function addComment($id = null)
    {
        return $this->addEntityComment('compra', (int)$id);
    }

    /**
     * Download attachment (uses trait method)
     *
     * @param int|null $id Attachment id
     * @return \Cake\Http\Response
     */
    public function download($id = null)
    {
        return $this->downloadEntityAttachment('compra', (int)$id);
    }

    /**
     * AJAX endpoint for lazy loading compra history
     * PERFORMANCE FIX: Only loads when history tab is opened
     *
     * @param string|null $id Compra id
     * @return void JSON response
     */
    public function history($id = null)
    {
        $this->historyEntity('compra', (int)$id);
    }

    /**
     * Bulk assign compras to agents
     *
     * @return \Cake\Http\Response|null|void Redirects on success
     */
    public function bulkAssign()
    {
        return $this->bulkAssignEntity('compra');
    }

    /**
     * Bulk change priority of compras
     *
     * @return \Cake\Http\Response|null|void Redirects on success
     */
    public function bulkChangePriority()
    {
        return $this->bulkChangeEntityPriority('compra');
    }

    /**
     * Bulk delete compras
     *
     * @return \Cake\Http\Response|null|void Redirects on success
     */
    public function bulkDelete()
    {
        return $this->bulkDeleteEntity('compra');
    }
}
