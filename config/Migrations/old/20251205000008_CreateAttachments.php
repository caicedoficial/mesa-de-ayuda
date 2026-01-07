<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateAttachments extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('attachments', ['signed' => false]);

        $table
            ->addColumn('ticket_id', 'integer', [
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('comment_id', 'integer', [
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('filename', 'string', [
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('file_path', 'string', [
                'limit' => 500,
                'null' => false,
            ])
            ->addColumn('file_size', 'integer', [
                'null' => false,
            ])
            ->addColumn('mime_type', 'string', [
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('is_inline', 'boolean', [
                'default' => false,
                'null' => false,
            ])
            ->addColumn('content_id', 'string', [
                'limit' => 255,
                'null' => true,
                'comment' => 'For inline images (cid: references)',
            ])
            ->addColumn('uploaded_by', 'integer', [
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'null' => true,
            ])
            ->addIndex(['ticket_id'])
            ->addIndex(['comment_id'])
            ->addIndex(['content_id'])
            ->addForeignKey('ticket_id', 'tickets', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->addForeignKey('comment_id', 'ticket_comments', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->addForeignKey('uploaded_by', 'users', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->create();
    }
}
