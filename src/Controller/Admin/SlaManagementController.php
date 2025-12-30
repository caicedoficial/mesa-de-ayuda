<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Service\SlaManagementService;
use Cake\Log\Log;

/**
 * SLA Management Controller
 *
 * Handles SLA (Service Level Agreement) configuration for:
 * - PQRS (by type: Petición, Queja, Reclamo, Sugerencia)
 * - Compras
 * - Tickets (future)
 */
class SlaManagementController extends AppController
{
    private SlaManagementService $slaService;

    /**
     * Initialize
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->slaService = new SlaManagementService();
    }

    /**
     * Before filter
     *
     * @param \Cake\Event\EventInterface $event Event
     * @return \Cake\Http\Response|null|void
     */
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);

        // Only admins can access SLA management
        $user = $this->Authentication->getIdentity();
        if ($user && $user->get('role') !== 'admin') {
            $this->Flash->error('Solo los administradores pueden acceder a la gestión de SLA.');
            return $this->redirect(['controller' => 'Tickets', 'action' => 'index']);
        }
    }

    /**
     * Index - View and edit all SLA configurations
     *
     * @return \Cake\Http\Response|null|void
     */
    public function index()
    {
        if ($this->request->is('post')) {
            return $this->save();
        }

        // Load all SLA configurations (always fresh from DB - no cache)
        $slaConfigurations = $this->slaService->getAllSlaConfigurations();

        $this->set(compact('slaConfigurations'));
        $this->viewBuilder()->setLayout('admin');
    }

    /**
     * Save SLA configuration
     *
     * @return \Cake\Http\Response|null
     */
    public function save()
    {
        if (!$this->request->is('post')) {
            return $this->redirect(['action' => 'index']);
        }

        $data = $this->request->getData();
        $errors = [];
        $successCount = 0;

        try {
            // Save PQRS SLA settings
            $pqrsTypes = ['peticion', 'queja', 'reclamo', 'sugerencia'];
            foreach ($pqrsTypes as $type) {
                // First response
                $firstResponseKey = "sla_pqrs_{$type}_first_response_days";
                if (isset($data[$firstResponseKey])) {
                    $value = (int)$data[$firstResponseKey];
                    if ($value > 0) {
                        if ($this->slaService->updateSetting($firstResponseKey, $value)) {
                            $successCount++;
                        } else {
                            $errors[] = "Error al guardar {$firstResponseKey}";
                        }
                    }
                }

                // Resolution
                $resolutionKey = "sla_pqrs_{$type}_resolution_days";
                if (isset($data[$resolutionKey])) {
                    $value = (int)$data[$resolutionKey];
                    if ($value > 0) {
                        if ($this->slaService->updateSetting($resolutionKey, $value)) {
                            $successCount++;
                        } else {
                            $errors[] = "Error al guardar {$resolutionKey}";
                        }
                    }
                }
            }

            // Save Compras SLA settings
            if (isset($data['sla_compras_first_response_days'])) {
                $value = (int)$data['sla_compras_first_response_days'];
                if ($value > 0) {
                    if ($this->slaService->updateSetting('sla_compras_first_response_days', $value)) {
                        $successCount++;
                    } else {
                        $errors[] = "Error al guardar sla_compras_first_response_days";
                    }
                }
            }

            if (isset($data['sla_compras_resolution_days'])) {
                $value = (int)$data['sla_compras_resolution_days'];
                if ($value > 0) {
                    if ($this->slaService->updateSetting('sla_compras_resolution_days', $value)) {
                        $successCount++;
                    } else {
                        $errors[] = "Error al guardar sla_compras_resolution_days";
                    }
                }
            }

            if (empty($errors)) {
                $this->Flash->success("Se guardaron {$successCount} configuraciones de SLA correctamente. Los cambios se aplicarán inmediatamente a nuevas solicitudes.");
                Log::info('SLA settings updated successfully', ['count' => $successCount, 'user' => $this->Authentication->getIdentity()?->get('email')]);
            } else {
                $this->Flash->warning("Se guardaron {$successCount} configuraciones, pero hubo algunos errores: " . implode(', ', $errors));
                Log::warning('Some SLA settings failed to save', ['errors' => $errors]);
            }

        } catch (\Exception $e) {
            $this->Flash->error('Error al guardar la configuración de SLA: ' . $e->getMessage());
            Log::error('Error saving SLA settings', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Preview SLA calculations
     *
     * @return void
     */
    public function preview()
    {
        $now = new \Cake\I18n\DateTime();

        // Preview PQRS SLA calculations
        $pqrsTypes = ['peticion', 'queja', 'reclamo', 'sugerencia'];
        $pqrsPreview = [];

        foreach ($pqrsTypes as $type) {
            $deadlines = $this->slaService->calculatePqrsSlaDeadlines($type, $now);
            $pqrsPreview[$type] = [
                'first_response' => $deadlines['first_response_sla_due']->i18nFormat('yyyy-MM-dd HH:mm:ss'),
                'resolution' => $deadlines['resolution_sla_due']->i18nFormat('yyyy-MM-dd HH:mm:ss'),
            ];
        }

        // Preview Compras SLA calculations
        $comprasDeadlines = $this->slaService->calculateComprasSlaDeadlines($now);
        $comprasPreview = [
            'first_response' => $comprasDeadlines['first_response_sla_due']->i18nFormat('yyyy-MM-dd HH:mm:ss'),
            'resolution' => $comprasDeadlines['resolution_sla_due']->i18nFormat('yyyy-MM-dd HH:mm:ss'),
        ];

        $this->set(compact('pqrsPreview', 'comprasPreview', 'now'));
        $this->viewBuilder()->setLayout('admin');
    }
}
