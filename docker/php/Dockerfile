FROM php:8.4-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    libonig-dev \
    cron \
    supervisor \
    logrotate \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_mysql \
    intl \
    opcache \
    zip

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/symfony

# Create log directory
RUN mkdir -p /var/www/symfony/var/log && \
    chmod -R 777 /var/www/symfony/var

# Copy cron configuration
COPY crontab /etc/cron.d/symfony-cron
RUN chmod 0644 /etc/cron.d/symfony-cron && \
    crontab /etc/cron.d/symfony-cron

# Copy logrotate configuration
COPY logrotate-sync /etc/logrotate.d/symfony-sync
RUN chmod 0644 /etc/logrotate.d/symfony-sync

# Copy supervisor configuration
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy test script
COPY test-cron.sh /usr/local/bin/test-cron.sh
RUN chmod +x /usr/local/bin/test-cron.sh

# Set proper permissions
RUN chown -R www-data:www-data /var/www/symfony

# Expose port 9000 for PHP-FPM
EXPOSE 9000

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
