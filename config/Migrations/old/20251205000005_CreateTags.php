<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateTags extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('tags', ['signed' => false]);

        $table
            ->addColumn('name', 'string', [
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('color', 'string', [
                'limit' => 7,
                'default' => '#3498db',
                'null' => false,
                'comment' => 'Hex color code',
            ])
            ->addColumn('is_active', 'boolean', [
                'default' => true,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'null' => true,
            ])
            ->addIndex(['name'], ['unique' => true])
            ->addIndex(['is_active'])
            ->create();
    }
}
