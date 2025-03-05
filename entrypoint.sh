#!/bin/sh

# Set the base directory for the app
BASEDIR=/var/www/webapp

# Remove Cache
rm -rf $BASEDIR/bootstrap/cache/*

# Flag to check if it's a fresh install
FIRST_INSTALL=false

# Check if the .env file exists
if [ ! -f "$BASEDIR/.env" ]; then
   FIRST_INSTALL=true
fi

# Use FIRST_INSTALL variable as needed in the rest of your script
if [ "$FIRST_INSTALL" = true ]; then
    echo "This is a FRESH INSTALL."

    # Determine the appropriate environment file based on the PRODUCTION variable
    if [ "$PRODUCTION" = "1" ]; then
        ENV_FILE=".env.production"
    else
        ENV_FILE=".env.example"
    fi

    # Copy the chosen environment file to create the .env file
    echo "Generating .env from a copy $ENV_FILE ..."
    cp $ENV_FILE .env
    echo "File .env generated."
fi

# Clear the old boostrap/cache
php artisan clear-compiled

# Install composer dependencies
if [ "$PRODUCTION" = "1" ]; then
    composer install --no-dev --no-interaction --no-scripts
else
    composer install --no-interaction --no-scripts
fi

# Run the migrations and seed the database
if [ "$FIRST_INSTALL" = true ]; then
    php artisan migrate --seed --force
else
    php artisan migrate --force
fi

echo "Generating app key..."

# Generate an application key
php artisan key:generate --force

echo "App key generated."

# Recreate database views
php artisan app:recreate-database-views

# Clear the optimization cache
php artisan optimize:clear

# Remove prior storage links that exist
rm -rf public/storage

# Build up a new storage link
php artisan storage:link

# Set the correct permissions
chmod -R ugo+rw storage

# Queue clear
php artisan queue:clear

# Optimize composer autoloader
composer dump-autoload --optimize

# Route cache
php artisan route:cache

# Install node dependencies
npm install

# Check if running in production or development mode
if [ "$PRODUCTION" = "1" ]; then
    npm run build
    echo "Running in production mode"
else
    echo "Running in development mode"
fi

# Start supervisord
if [ "$PRODUCTION" = "1" ]; then
    exec /usr/bin/supervisord -c /etc/supervisord.conf
    echo "Supervisord started."
else
    exec /usr/bin/supervisord -c /etc/supervisord-dev.conf
    echo "Supervisord-dev started."
fi
