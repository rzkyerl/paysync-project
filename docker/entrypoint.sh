#!/bin/sh
set -e

cd /var/www/html

# Generate APP_KEY if not set
if [ -z "$APP_KEY" ]; then
    echo "⚠️  APP_KEY is not set. Generating..."
    php artisan key:generate --force
fi

# Write environment variables to .env
# Render injects env vars directly, so we sync them to .env file
php -r "
\$env = file_get_contents('.env');
\$vars = [
    'APP_KEY', 'APP_URL', 'APP_DEBUG',
    'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD',
    'MAIL_PASSWORD',
    'SESSION_DRIVER', 'CACHE_STORE', 'QUEUE_CONNECTION',
];
foreach (\$vars as \$key) {
    \$value = getenv(\$key);
    if (\$value !== false) {
        \$env = preg_replace('/^'.\$key.'=.*/m', \$key.'='.\$value, \$env);
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
