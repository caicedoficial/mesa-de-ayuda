<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Health Check Controller
 *
 * Provides health check endpoint for Docker monitoring.
 * Verifies that Nginx, PHP-FPM, and MySQL are all functioning.
 */
class HealthController extends AppController
{
    /**
     * Disable authentication for health check endpoint
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        // Allow unauthenticated access to health check
        $this->Authentication->allowUnauthenticated(['check']);
    }

    /**
     * Health check endpoint
     *
     * Returns 200 OK if all systems are operational:
     * - Nginx is serving requests
     * - PHP-FPM is processing PHP code
     * - MySQL database is accessible
     *
     * Returns 503 Service Unavailable if any component fails.
     *
     * @return \Cake\Http\Response
     */
    public function check()
    {
        $status = [
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s'),
            'checks' => []
        ];

        try {
            // Check 1: PHP is running (implicit - we're here)
            $status['checks']['php'] = 'ok';

            // Check 2: Database connection
            $usersTable = $this->fetchTable('Users');
            $userCount = $usersTable->find()->count();
            $status['checks']['database'] = 'ok';
            $status['checks']['database_users'] = $userCount;

            // Check 3: System settings table (core configuration)
            $settingsTable = $this->fetchTable('SystemSettings');
            $settingsCount = $settingsTable->find()->count();
            $status['checks']['system_settings'] = 'ok';
            $status['checks']['system_settings_count'] = $settingsCount;

            // All checks passed
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode($status, JSON_PRETTY_PRINT));

        } catch (\Exception $e) {
            // Something failed
            $status['status'] = 'unhealthy';
            $status['error'] = $e->getMessage();
            $status['checks']['error_type'] = get_class($e);

            return $this->response
                ->withStatus(503) // Service Unavailable
                ->withType('application/json')
                ->withStringBody(json_encode($status, JSON_PRETTY_PRINT));
        }
    }
}
