<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreatePqrsAttachments extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('pqrs_attachments', ['signed' => false]);

        $table
            ->addColumn('pqrs_id', 'integer', [
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
            ->addColumn('uploaded_by', 'integer', [
                'null' => true,
                'signed' => false,
                'comment' => 'NULL for public submissions',
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'null' => true,
            ])
            ->addIndex(['pqrs_id'])
            ->addIndex(['comment_id'])
            ->addForeignKey('pqrs_id', 'pqrs', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->addForeignKey('comment_id', 'pqrs_comments', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->addForeignKey('uploaded_by', 'users', 'id', [
                'delete' => 'SET_NULL',
                'update' => 'CASCADE',
            ])
            ->create();
    }
}
