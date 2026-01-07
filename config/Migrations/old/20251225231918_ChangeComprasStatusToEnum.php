<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class ChangeComprasStatusToEnum extends BaseMigration
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
        $table = $this->table('compras');

        // Change status from STRING to ENUM and include 'convertido'
        $table->changeColumn('status', 'enum', [
            'values' => ['nuevo', 'en_revision', 'aprobado', 'en_proceso', 'completado', 'rechazado', 'convertido'],
            'default' => 'nuevo',
            'null' => false,
        ]);

        $table->update();
    }
}
