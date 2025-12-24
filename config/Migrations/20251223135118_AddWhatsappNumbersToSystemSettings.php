<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Add WhatsApp Numbers to System Settings Migration
 *
 * Adds three new settings to support different WhatsApp numbers for each module:
 * - whatsapp_tickets_number: Number for ticket notifications
 * - whatsapp_pqrs_number: Number for PQRS notifications
 * - whatsapp_compras_number: Number for purchase order notifications
 *
 * Also removes the deprecated whatsapp_default_number setting.
 */
class AddWhatsappNumbersToSystemSettings extends BaseMigration
{
    /**
     * Up Method.
     *
     * @return void
     */
    public function up(): void
    {
        // Insert new WhatsApp number settings
        $this->table('system_settings')
            ->insert([
                [
                    'setting_key' => 'whatsapp_tickets_number',
                    'setting_value' => '',
                    'description' => 'Número de WhatsApp para notificaciones de Tickets (formato: 5511999999999@s.whatsapp.net o groupid@g.us)',
                    'created' => date('Y-m-d H:i:s'),
                    'modified' => date('Y-m-d H:i:s'),
                ],
                [
                    'setting_key' => 'whatsapp_pqrs_number',
                    'setting_value' => '',
                    'description' => 'Número de WhatsApp para notificaciones de PQRS (formato: 5511999999999@s.whatsapp.net o groupid@g.us)',
                    'created' => date('Y-m-d H:i:s'),
                    'modified' => date('Y-m-d H:i:s'),
                ],
                [
                    'setting_key' => 'whatsapp_compras_number',
                    'setting_value' => '',
                    'description' => 'Número de WhatsApp para notificaciones de Compras (formato: 5511999999999@s.whatsapp.net o groupid@g.us)',
                    'created' => date('Y-m-d H:i:s'),
                    'modified' => date('Y-m-d H:i:s'),
                ],
            ])
            ->save();
    }

    /**
     * Down Method.
     *
     * @return void
     */
    public function down(): void
    {
        // Remove the WhatsApp number settings
        $this->execute("DELETE FROM system_settings WHERE setting_key IN ('whatsapp_tickets_number', 'whatsapp_pqrs_number', 'whatsapp_compras_number')");
    }
}
