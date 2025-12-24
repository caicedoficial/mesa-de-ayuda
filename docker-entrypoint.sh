#!/bin/bash
# Docker entrypoint script for CakePHP application

set -e

echo "🚀 Starting CakePHP Helpdesk System..."

# Wait for database to be ready
echo "⏳ Waiting for database connection..."
until php -r "
\$host = getenv('DB_HOST');
\$port = getenv('DB_PORT') ?: 5432;
\$dbname = getenv('DB_NAME');
\$user = getenv('DB_USER');
\$password = getenv('DB_PASSWORD');

try {
    \$dsn = \"pgsql:host=\$host;port=\$port;dbname=\$dbname\";
    \$pdo = new PDO(\$dsn, \$user, \$password, [PDO::ATTR_TIMEOUT => 5]);
    echo 'Connected';
    exit(0);
} catch (PDOException \$e) {
    exit(1);
}
" 2>/dev/null; do
    echo "   Database not ready yet, retrying in 2s..."
    sleep 2
done

echo "✅ Database connection established"

# Create log directories
mkdir -p /var/log/php /var/log/php-fpm /var/log/supervisor
chown -R www-data:www-data /var/log/php /var/log/php-fpm

# Set correct permissions
echo "🔒 Setting permissions..."
chown -R www-data:www-data /var/www/html
chmod -R 775 /var/www/html/tmp /var/www/html/logs /var/www/html/webroot/uploads

# Run migrations if in development mode
if [ "$APP_ENV" = "development" ]; then
    echo "🔄 Running database migrations..."
    su-exec www-data bin/cake migrations migrate || echo "⚠️  Migrations failed (may be normal if already up to date)"
fi

# Clear cache
echo "🧹 Clearing cache..."
su-exec www-data bin/cake cache clear_all || echo "⚠️  Cache clear failed (may be normal)"

echo "✅ Initialization complete!"
echo "📡 Application available at http://localhost:8765"

# Execute CMD from Dockerfile
exec "$@"
