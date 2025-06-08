#!/bin/bash

# Test script to verify cron setup
echo "Testing cron configuration..."

# Check if cron is installed
if ! command -v cron &> /dev/null; then
    echo "ERROR: cron is not installed"
    exit 1
fi

# Check if the crontab file exists
if [ ! -f /etc/cron.d/symfony-cron ]; then
    echo "ERROR: symfony-cron file not found in /etc/cron.d/"
    exit 1
fi

# Check if the crontab contains the sync command
if ! grep -q "app:sync-trainings" /etc/cron.d/symfony-cron; then
    echo "ERROR: app:sync-trainings command not found in crontab"
    exit 1
fi

# Check if the log directory exists
if [ ! -d /var/www/symfony/var/log ]; then
    echo "ERROR: Log directory not found"
    exit 1
fi

# Check if supervisord config exists
if [ ! -f /etc/supervisor/conf.d/supervisord.conf ]; then
    echo "ERROR: supervisord.conf not found"
    exit 1
fi

# Check if logrotate config exists
if [ ! -f /etc/logrotate.d/symfony-sync ]; then
    echo "ERROR: symfony-sync logrotate config not found"
    exit 1
fi

echo "All cron tests passed successfully!"
exit 0