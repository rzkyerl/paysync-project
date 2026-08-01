#!/bin/sh
set -e

cd /var/www/html

# Generate APP_KEY if not set
if [ -z "$APP_KEY" ]; then
    echo "⚠️  APP_KEY is not set. Generating..."
    php artisan key:generate --force
fi

# Railway MySQL plugin injects MYSQLHOST, MYSQLPORT, etc.
# Map them to Laravel's DB_* variables if not already set
DB_HOST="${DB_HOST:-${MYSQLHOST:-}}"
DB_PORT="${DB_PORT:-${MYSQLPORT:-3306}}"
DB_DATABASE="${DB_DATABASE:-${MYSQLDATABASE:-}}"
DB_USERNAME="${DB_USERNAME:-${MYSQLUSER:-}}"
DB_PASSWORD="${DB_PASSWORD:-${MYSQLPASSWORD:-}}"

export DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD

# Write environment variables to .env
# Railway injects env vars directly, so we sync them to .env file
php -r "
\$env = file_get_contents('.env');
\$vars = [
    'APP_KEY', 'APP_URL', 'APP_ENV', 'APP_DEBUG',
    'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD',
    'MAIL_MAILER', 'MAIL_HOST', 'MAIL_PORT', 'MAIL_USERNAME', 'MAIL_PASSWORD',
    'MAIL_FROM_ADDRESS', 'MAIL_FROM_NAME',
    'SESSION_DRIVER', 'CACHE_STORE', 'QUEUE_CONNECTION',
];
foreach (\$vars as \$key) {
    \$value = getenv(\$key);
    if (\$value !== false && \$value !== '') {
        if (preg_match('/^'.\$key.'=/m', \$env)) {
            \$env = preg_replace('/^'.\$key.'=.*/m', \$key.'='.\$value, \$env);
        } else {
            \$env .= PHP_EOL.\$key.'='.\$value;
        }
    }
}
file_put_contents('.env', \$env);
"

# Cache config for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Run migrations automatically
php artisan migrate --force

# Fix storage permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Create storage symlink if not exists
php artisan storage:link || true

# Start supervisor (manages nginx + php-fpm + queue worker)
mkdir -p /var/log/supervisor
exec /usr/bin/supervisord -c /etc/supervisord.conf
