#!/bin/bash
set -e

echo "--- Entrypoint Script Started ---"

echo "Ensuring writable directories exist..."
mkdir -p /var/www/html/writable/cache
mkdir -p /var/www/html/writable/logs
mkdir -p /var/www/html/writable/session
mkdir -p /var/www/html/writable/uploads
mkdir -p /var/www/html/writable/debugbar

# 2. Fix Permissions
echo "Fixing permissions..."
chown -R www-data:www-data /var/www/html/writable
chmod -R 775 /var/www/html/writable

# 3. Wait for Database
echo "Waiting for Database connection..."
for i in {1..30}; do
    if php -r '
        $host = getenv("database.default.hostname") ?: "restapi-codeigniter-mysql";
        $user = getenv("database.default.username") ?: "user";
        $pass = getenv("database.default.password") ?: "userpassword";
        
        $driver = getenv("database.default.DBDriver");
        if (stripos($driver, "sqlite") !== false) { exit(0); }

        try {
            $pdo = new PDO("mysql:host=$host;port=3306", $user, $pass);
            exit(0); 
        } catch (PDOException $e) {
            exit(1); 
        }
    '; then
        echo "Database is ready!"
        break
    fi
    echo "Database not ready yet... retrying ($i/30)"
    sleep 2
done

echo "Running Database Migrations..."
set +e 
php spark migrate --all -v
MIGRATE_STATUS=$?
set -e

if [ $MIGRATE_STATUS -ne 0 ]; then
    echo "ERROR: Migration failed! Check the logs above."
    # exit 1 
else
    echo "Migrations finished successfully."
fi

# 5. Start Apache
echo "Starting Apache on port 5005..."
exec apache2-foreground