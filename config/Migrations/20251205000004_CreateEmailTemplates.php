<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateEmailTemplates extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('email_templates', ['signed' => false]);

        $table
            ->addColumn('template_key', 'string', [
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('subject', 'string', [
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('body_html', 'text', [
                'null' => true,
            ])
            ->addColumn('available_variables', 'text', [
                'null' => true,
                'comment' => 'JSON array of available template variables',
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
            ->addIndex(['template_key'], ['unique' => true])
            ->create();
    }
}
