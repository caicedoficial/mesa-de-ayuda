<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * SeedTags Migration
 *
 * Seeds initial tags for categorizing tickets, PQRS, and other entities.
 * Tags can be manually assigned or automatically suggested via n8n AI integration.
 *
 * Categories included:
 * - Priority tags (Urgente)
 * - Issue types (Bug, Feature, Mejora)
 * - Support categories (Soporte, Consulta, Documentación)
 * - Technical categories (Hardware, Software, Red, Seguridad, Acceso)
 *
 * @version 1.0.0 - Initial tags (2026-01-05)
 */
class SeedTags extends AbstractMigration
{
    /**
     * Seed initial tags
     *
     * @return void
     */
    public function up(): void
    {
        $timestamp = date('Y-m-d H:i:s');

        $data = [
            // Priority
            [
                'name' => 'Urgente',
                'color' => '#f44336',
                'is_active' => true,
                'created' => $timestamp,
                'modified' => $timestamp,
            ],

            // Issue Types
            [
                'name' => 'Bug',
                'color' => '#e91e63',
                'is_active' => true,
                'created' => $timestamp,
                'modified' => $timestamp,
            ],
            [
                'name' => 'Feature',
                'color' => '#9c27b0',
                'is_active' => true,
                'created' => $timestamp,
                'modified' => $timestamp,
            ],
            [
                'name' => 'Mejora',
                'color' => '#00bcd4',
                'is_active' => true,
                'created' => $timestamp,
                'modified' => $timestamp,
            ],

            // Support Categories
            [
                'name' => 'Soporte',
                'color' => '#3f51b5',
                'is_active' => true,
                'created' => $timestamp,
                'modified' => $timestamp,
            ],
            [
                'name' => 'Consulta',
                'color' => '#2196f3',
                'is_active' => true,
                'created' => $timestamp,
                'modified' => $timestamp,
            ],
            [
                'name' => 'Documentación',
                'color' => '#03a9f4',
                'is_active' => true,
                'created' => $timestamp,
                'modified' => $timestamp,
            ],

            // Technical Categories
            [
                'name' => 'Hardware',
                'color' => '#009688',
                'is_active' => true,
                'created' => $timestamp,
                'modified' => $timestamp,
            ],
            [
                'name' => 'Software',
                'color' => '#4caf50',
                'is_active' => true,
                'created' => $timestamp,
                'modified' => $timestamp,
            ],
            [
                'name' => 'Red',
                'color' => '#8bc34a',
                'is_active' => true,
                'created' => $timestamp,
                'modified' => $timestamp,
            ],
            [
                'name' => 'Seguridad',
                'color' => '#ff5722',
                'is_active' => true,
                'created' => $timestamp,
                'modified' => $timestamp,
            ],
            [
                'name' => 'Acceso',
                'color' => '#795548',
                'is_active' => true,
                'created' => $timestamp,
                'modified' => $timestamp,
            ],
        ];

        $table = $this->table('tags');
        $table->insert($data)->save();
    }

    /**
     * Remove seeded tags
     *
     * @return void
     */
    public function down(): void
    {
        $this->execute('DELETE FROM tags');
    }
}
