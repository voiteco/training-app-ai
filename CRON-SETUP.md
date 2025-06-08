# Cron Job Setup for Training Synchronization

This document explains how the automatic training synchronization is set up in the Training Booking System.

## Overview

The system automatically synchronizes training data from Google Sheets every 15 minutes using a cron job configured in the PHP container. This ensures that the local database stays updated with the latest data from Google Sheets without manual intervention.

## Implementation Details

### 1. Cron Configuration

The cron job is configured to run the `app:sync-trainings` Symfony command every 15 minutes. The configuration is stored in the `/etc/cron.d/symfony-cron` file inside the PHP container:

```cron
*/15 * * * * cd /var/www/symfony && php bin/console app:sync-trainings >> var/log/sync.log 2>&1
```

### 2. Process Management

The PHP container uses Supervisor to manage both PHP-FPM and the cron daemon. This ensures that both services are running properly and are automatically restarted if they fail. The Supervisor configuration is stored in `/etc/supervisor/conf.d/supervisord.conf`.

### 3. Logging

The output of each synchronization run is logged to `/var/www/symfony/var/log/sync.log`. This log file is automatically rotated using logrotate to prevent it from growing too large. The logrotate configuration is stored in `/etc/logrotate.d/symfony-sync`.

### 4. Docker Setup

The cron job is set up in the PHP container during the Docker build process. The following files are involved:

- `docker/php/Dockerfile`: Installs cron, supervisor, and logrotate, and sets up the necessary configurations
- `docker/php/crontab`: Contains the cron job configuration
- `docker/php/supervisord.conf`: Contains the Supervisor configuration
- `docker/php/logrotate-sync`: Contains the logrotate configuration
- `docker/php/test-cron.sh`: A test script to verify the cron setup

## Verification

To verify that the cron job is properly set up, you can run the following command:

```bash
docker exec php /usr/local/bin/test-cron.sh
```

This script checks that:
- Cron is installed
- The crontab file exists
- The crontab contains the sync command
- The log directory exists
- The Supervisor configuration exists
- The logrotate configuration exists

## Manual Execution

To manually run the synchronization command:

```bash
docker exec php php bin/console app:sync-trainings
```

## Troubleshooting

If the automatic synchronization is not working as expected:

1. Check the sync log:
   ```bash
   docker exec php cat /var/www/symfony/var/log/sync.log
   ```

2. Verify that cron is running:
   ```bash
   docker exec php ps aux | grep cron
   ```

3. Check the Supervisor status:
   ```bash
   docker exec php supervisorctl status
   ```

4. Restart the cron service:
   ```bash
   docker exec php supervisorctl restart cron
   ```

5. Ensure the log directory is writable:
   ```bash
   docker exec php ls -la /var/www/symfony/var/log
   ```