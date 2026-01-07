<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * SeedAdminUser Migration
 *
 * Creates the default administrator user for initial system access.
 *
 * Default Credentials:
 * - Email: admin@operadoracafetera.com
 * - Password: ca1ced0.DEV
 *
 * IMPORTANT: Change the password immediately after first login!
 *
 * @version 1.0.0 - Initial admin user (2026-01-05)
 */
class SeedAdminUser extends AbstractMigration
{
    /**
     * Create default admin user
     *
     * @return void
     */
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
                    'organization_id' => null,
                    'profile_image' => null,
                    'is_active' => true,
                    'created' => date('Y-m-d H:i:s'),
                    'modified' => date('Y-m-d H:i:s'),
                ],
            ];

            $table = $this->table('users');
            $table->insert($data)->save();
        }
    }

    /**
     * Remove admin user
     *
     * @return void
     */
    public function down(): void
    {
        $this->execute("DELETE FROM users WHERE email = 'admin@operadoracafetera.com'");
    }
}
