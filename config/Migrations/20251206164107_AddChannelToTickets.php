<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddChannelToTickets extends BaseMigration
{
    /**
     * Add channel column to tickets table
     *
     * Tracks the channel through which the ticket was created:
     * - email: Created from Gmail import
     * - web: Created manually from web interface
     * - api: Created via API (future)
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('tickets');

        $table->addColumn('channel', 'string', [
            'default' => 'email',
            'limit' => 20,
            'null' => false,
            'after' => 'requester_id',
            'comment' => 'Channel: email, web, api',
        ]);

        $table->update();
    }
}
