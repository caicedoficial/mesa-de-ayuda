<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddChannelToCompras extends BaseMigration
{
    /**
     * Add channel column to compras table
     *
     * Tracks the channel through which the compra was created (inherited from ticket):
     * - email: Ticket originated from email
     * - whatsapp: Ticket originated from WhatsApp
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('compras');

        $table->addColumn('channel', 'string', [
            'default' => 'email',
            'limit' => 20,
            'null' => false,
            'after' => 'assignee_id',
            'comment' => 'Channel: email, whatsapp (inherited from ticket)',
        ]);

        $table->update();
    }
}
