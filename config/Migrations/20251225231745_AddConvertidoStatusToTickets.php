<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddConvertidoStatusToTickets extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('tickets');

        // Modify status ENUM to include 'convertido'
        $table->changeColumn('status', 'enum', [
            'values' => ['nuevo', 'abierto', 'pendiente', 'resuelto', 'convertido'],
            'default' => 'nuevo',
            'null' => false,
        ]);

        $table->update();
    }
}
