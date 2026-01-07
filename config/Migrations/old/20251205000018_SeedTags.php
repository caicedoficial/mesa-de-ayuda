<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class SeedTags extends AbstractMigration
{
    public function up(): void
    {
        $data = [
            [
                'name' => 'Urgente',
                'color' => '#f44336',
                'is_active' => true,
                'created' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Bug',
                'color' => '#e91e63',
                'is_active' => true,
                'created' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Feature',
                'color' => '#9c27b0',
                'is_active' => true,
                'created' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Soporte',
                'color' => '#3f51b5',
                'is_active' => true,
                'created' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Consulta',
                'color' => '#2196f3',
                'is_active' => true,
                'created' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Documentación',
                'color' => '#03a9f4',
                'is_active' => true,
                'created' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Mejora',
                'color' => '#00bcd4',
                'is_active' => true,
                'created' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Hardware',
                'color' => '#009688',
                'is_active' => true,
                'created' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Software',
                'color' => '#4caf50',
                'is_active' => true,
                'created' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Red',
                'color' => '#8bc34a',
                'is_active' => true,
                'created' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Seguridad',
                'color' => '#ff5722',
                'is_active' => true,
                'created' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Acceso',
                'color' => '#795548',
                'is_active' => true,
                'created' => date('Y-m-d H:i:s'),
            ],
        ];

        $table = $this->table('tags');
        $table->insert($data)->save();
    }

    public function down(): void
    {
        $this->execute('DELETE FROM tags');
    }
}
