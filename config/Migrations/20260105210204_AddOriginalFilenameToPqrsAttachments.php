<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * AddOriginalFilenameToPqrsAttachments Migration
 *
 * Adds missing original_filename field to pqrs_attachments table.
 * This field stores the original filename uploaded by the user for display purposes.
 *
 * @version 1.0.0 - Fix missing field (2026-01-05)
 */
class AddOriginalFilenameToPqrsAttachments extends AbstractMigration
{
    /**
     * Add original_filename field
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('pqrs_attachments');

        $table->addColumn('original_filename', 'string', [
            'limit' => 255,
            'null' => false,
            'after' => 'filename',
            'comment' => 'Original filename uploaded by user (for display)',
        ])
        ->update();
    }
}
