<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * CreateSystemSettings Migration
 *
 * Creates the system_settings table for dynamic configuration management.
 * Stores key-value pairs for system-wide settings that can be modified
 * without code changes.
 *
 * Features:
 * - Type-aware settings (string, boolean, integer, json, encrypted)
 * - Encrypted storage for sensitive values (API keys, credentials)
 * - Cached for performance (1-hour TTL)
 *
 * Common settings:
 * - Gmail OAuth credentials
 * - WhatsApp Evolution API configuration
 * - n8n webhook URLs
 * - SLA time limits
 * - Email notification preferences
 *
 * @version 1.0.0 - Initial version (2026-01-05)
 */
class CreateSystemSettings extends AbstractMigration
{
    /**
     * Create system_settings table
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('system_settings', ['signed' => false]);

        $table
            // Setting identification
            ->addColumn('setting_key', 'string', [
                'limit' => 100,
                'null' => false,
                'comment' => 'Unique setting identifier (e.g., gmail_user_email, sla_pqrs_queja_first_response_days)',
            ])

            // Setting value and metadata
            ->addColumn('setting_value', 'text', [
                'null' => true,
                'comment' => 'Setting value (may be encrypted for sensitive data)',
            ])
            ->addColumn('setting_type', 'string', [
                'limit' => 50,
                'null' => true,
                'comment' => 'Data type: string, boolean, integer, json, encrypted',
            ])
            ->addColumn('description', 'string', [
                'limit' => 255,
                'null' => true,
                'comment' => 'Human-readable description of the setting',
            ])

            // Timestamps
            ->addColumn('created', 'datetime', [
                'default' => null,
                'null' => true,
                'comment' => 'Setting creation timestamp',
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'null' => true,
                'comment' => 'Last modification timestamp',
            ])

            // Indexes
            ->addIndex(['setting_key'], [
                'unique' => true,
                'name' => 'idx_setting_key_unique',
            ])
            ->addIndex(['setting_type'], [
                'name' => 'idx_setting_type',
            ])

            ->create();
    }
}
