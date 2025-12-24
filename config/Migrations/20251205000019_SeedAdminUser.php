<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class SeedAdminUser extends AbstractMigration
{
    public function up(): void
    {
        // Check if admin user already exists
        $builder = $this->getQueryBuilder('select');
        $exists = $builder
            ->select(['id'])
            ->from('users')
            ->where(['email' => 'admin@operadoracafetera.com'])
            ->execute()
            ->fetch();

        // Only insert if user doesn't exist
        if (!$exists) {
            // Hash the password using CakePHP's DefaultPasswordHasher
            $hasher = new \Authentication\PasswordHasher\DefaultPasswordHasher();
            $hashedPassword = $hasher->hash('ca1ced0.DEV');

            $data = [
                [
                    'email' => 'admin@operadoracafetera.com',
                    'password' => $hashedPassword,
                    'first_name' => 'Admin',
                    'last_name' => 'Sistema',
                    'role' => 'admin',
                    'is_active' => true,
                    'created' => date('Y-m-d H:i:s'),
                    'modified' => date('Y-m-d H:i:s'),
                ],
            ];

            $table = $this->table('users');
            $table->insert($data)->save();
        }
    }

    public function down(): void
    {
        $this->execute("DELETE FROM users WHERE email = 'admin@operadoracafetera.com'");
    }
}
