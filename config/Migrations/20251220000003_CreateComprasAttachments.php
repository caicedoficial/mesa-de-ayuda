<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateComprasAttachments extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('compras_attachments', ['signed' => false]);

        $table
            ->addColumn('compra_id', 'integer', [
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('compras_comment_id', 'integer', [
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('filename', 'string', [
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('original_filename', 'string', [
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('file_path', 'string', [
                'limit' => 500,
                'null' => false,
            ])
            ->addColumn('mime_type', 'string', [
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('file_size', 'integer', [
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('is_inline', 'boolean', [
                'default' => false,
                'null' => false,
            ])
            ->addColumn('content_id', 'string', [
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('uploaded_by_user_id', 'integer', [
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'null' => true,
            ])

            ->addIndex(['compra_id'])
            ->addIndex(['compras_comment_id'])

            ->addForeignKey('compra_id', 'compras', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->addForeignKey('compras_comment_id', 'compras_comments', 'id', [
                'delete' => 'SET_NULL',
                'update' => 'CASCADE',
            ])
            ->addForeignKey('uploaded_by_user_id', 'users', 'id', [
                'delete' => 'SET_NULL',
                'update' => 'CASCADE',
            ])
            ->create();
    }
}
