<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddEmailFieldsToCompras extends BaseMigration
{
    /**
     * Add email_to and email_cc fields to compras table
     *
     * These fields store JSON arrays of email recipients for CC purposes.
     * Important for keeping managers/supervisors in the loop for internal
     * purchases and support coordination.
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('compras');

        $table->addColumn('email_to', 'json', [
            'default' => null,
            'null' => true,
            'after' => 'channel',
            'comment' => 'JSON array of primary email recipients',
        ]);

        $table->addColumn('email_cc', 'json', [
            'default' => null,
            'null' => true,
            'after' => 'email_to',
            'comment' => 'JSON array of CC email recipients (e.g., managers)',
        ]);

        $table->update();
    }
}
