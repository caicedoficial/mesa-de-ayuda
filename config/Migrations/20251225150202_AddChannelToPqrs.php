<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddChannelToPqrs extends BaseMigration
{
    /**
     * Add channel column to pqrs table
     *
     * Tracks the channel through which the PQRS was created:
     * - web: Created from public web form (default)
     * - whatsapp: Created from WhatsApp integration
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('pqrs');

        $table->addColumn('channel', 'string', [
            'default' => 'web',
            'limit' => 20,
            'null' => false,
            'after' => 'requester_email',
            'comment' => 'Channel: web, whatsapp',
        ]);

        $table->update();
    }
}
