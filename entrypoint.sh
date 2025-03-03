#!/bin/bash

# Setup Laravel Schedule Run
echo "* * * * * cd /var/www/html && php artisan schedule:run >> /var/log/cron.log 2>&1" | crontab -

# Start cron
service cron start
#!/bin/bash
set -e

# Set the Apache port configuration based on the PORT environment variable
if [ -z "${PORT}" ]; then
  PORT=8080
fi

echo "Updating Apache to listen on port ${PORT}"
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost *:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Start Apache in the foreground
exec apache2-foreground