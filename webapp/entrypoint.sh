#!/bin/sh
set -e

echo "Starting entrypoint script..."

# Check if the .env file exists
if [ ! -f .env ]; then
    echo ".env file not found. Copying from .env.example..."
    cp .env.example .env
else
    echo ".env file already exists."
fi

# Print the current environment
echo "Current environment: $APP_ENV"

# Install dependencies
composer install --no-interaction --no-dev --optimize-autoloader
npm install --no-audit --no-fund --prefer-dist

# Clear configurations to avoid caching issues in development
echo "Clearing configurations..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Run migrations
php artisan migrate --force

# Export language files
php artisan lang:export

# Build vite assets
if [ "$APP_ENV" = "production" ]; then
    echo "Building Vite assets for production..."
    npm run build
fi

# Start Supervisord to manage processes
echo "Supervisord started."
exec /usr/bin/supervisord -c /etc/supervisord.conf