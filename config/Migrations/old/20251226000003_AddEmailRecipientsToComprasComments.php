<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * Add email_to and email_cc fields to compras_comments table
 *
 * These fields store the recipients when a comment is sent as an email response,
 * maintaining a complete audit trail of all email communications.
 */
class AddEmailRecipientsToComprasComments extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('compras_comments');

        $table
            ->addColumn('email_to', 'text', [
                'null' => true,
                'after' => 'comment_type',
                'comment' => 'JSON array of To recipients when sent as email',
            ])
            ->addColumn('email_cc', 'text', [
                'null' => true,
                'after' => 'email_to',
                'comment' => 'JSON array of CC recipients when sent as email',
            ])
            ->update();
    }
}
