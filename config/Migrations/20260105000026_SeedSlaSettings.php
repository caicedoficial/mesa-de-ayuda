<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * SeedSlaSettings Migration
 *
 * Seeds SLA (Service Level Agreement) configuration settings for:
 * - PQRS: Type-specific SLA targets (peticion, queja, reclamo, sugerencia)
 * - Compras: Uniform SLA targets for all purchase requests
 *
 * SLA Metrics:
 * - First Response: Time to acknowledge and provide initial response
 * - Resolution: Time to resolve/complete the request
 *
 * PQRS SLA Targets (by type):
 * - Petición: 2 days first response, 5 days resolution
 * - Queja: 1 day first response, 3 days resolution
 * - Reclamo: 1 day first response, 3 days resolution
 * - Sugerencia: 3 days first response, 7 days resolution
 *
 * Compras SLA Targets:
 * - First Response: 1 day
 * - Resolution: 3 days
 *
 * @version 1.0.0 - Initial SLA settings (2026-01-05)
 */
class SeedSlaSettings extends AbstractMigration
{
    /**
     * Seed SLA configuration settings
     *
     * @return void
     */
    public function up(): void
    {
        $timestamp = date('Y-m-d H:i:s');

        $settings = [
            // PQRS - Petición (Request)
            [
                'setting_key' => 'sla_pqrs_peticion_first_response_days',
                'setting_value' => '2',
                'setting_type' => 'integer',
                'description' => 'SLA para primera respuesta en PQRS tipo Petición (días)',
                'created' => $timestamp,
                'modified' => $timestamp,
            ],
            [
                'setting_key' => 'sla_pqrs_peticion_resolution_days',
                'setting_value' => '5',
                'setting_type' => 'integer',
                'description' => 'SLA para resolución en PQRS tipo Petición (días)',
                'created' => $timestamp,
                'modified' => $timestamp,
            ],

            // PQRS - Queja (Complaint)
            [
                'setting_key' => 'sla_pqrs_queja_first_response_days',
                'setting_value' => '1',
                'setting_type' => 'integer',
                'description' => 'SLA para primera respuesta en PQRS tipo Queja (días)',
                'created' => $timestamp,
                'modified' => $timestamp,
            ],
            [
                'setting_key' => 'sla_pqrs_queja_resolution_days',
                'setting_value' => '3',
                'setting_type' => 'integer',
                'description' => 'SLA para resolución en PQRS tipo Queja (días)',
                'created' => $timestamp,
                'modified' => $timestamp,
            ],

            // PQRS - Reclamo (Claim)
            [
                'setting_key' => 'sla_pqrs_reclamo_first_response_days',
                'setting_value' => '1',
                'setting_type' => 'integer',
                'description' => 'SLA para primera respuesta en PQRS tipo Reclamo (días)',
                'created' => $timestamp,
                'modified' => $timestamp,
            ],
            [
                'setting_key' => 'sla_pqrs_reclamo_resolution_days',
                'setting_value' => '3',
                'setting_type' => 'integer',
                'description' => 'SLA para resolución en PQRS tipo Reclamo (días)',
                'created' => $timestamp,
                'modified' => $timestamp,
            ],

            // PQRS - Sugerencia (Suggestion)
            [
                'setting_key' => 'sla_pqrs_sugerencia_first_response_days',
                'setting_value' => '3',
                'setting_type' => 'integer',
                'description' => 'SLA para primera respuesta en PQRS tipo Sugerencia (días)',
                'created' => $timestamp,
                'modified' => $timestamp,
            ],
            [
                'setting_key' => 'sla_pqrs_sugerencia_resolution_days',
                'setting_value' => '7',
                'setting_type' => 'integer',
                'description' => 'SLA para resolución en PQRS tipo Sugerencia (días)',
                'created' => $timestamp,
                'modified' => $timestamp,
            ],

            // Compras (Purchase Requests)
            [
                'setting_key' => 'sla_compras_first_response_days',
                'setting_value' => '1',
                'setting_type' => 'integer',
                'description' => 'SLA para primera respuesta en Compras (días)',
                'created' => $timestamp,
                'modified' => $timestamp,
            ],
            [
                'setting_key' => 'sla_compras_resolution_days',
                'setting_value' => '3',
                'setting_type' => 'integer',
                'description' => 'SLA para resolución en Compras (días)',
                'created' => $timestamp,
                'modified' => $timestamp,
            ],
        ];

        // Insert settings
        $table = $this->table('system_settings');
        $table->insert($settings)->save();
    }

    /**
     * Remove SLA settings
     *
     * @return void
     */
    public function down(): void
    {
        $this->execute("
            DELETE FROM system_settings
            WHERE setting_key LIKE 'sla_pqrs_%'
            OR setting_key LIKE 'sla_compras_%'
        ");
    }
}
