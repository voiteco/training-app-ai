# Docker Setup for Training Booking System

This document explains the Docker environment setup for the Training Booking System.

## 📦 Components

The Docker environment consists of the following services:

- **PHP/Symfony**: PHP 8.4 with required extensions for Symfony
- **MySQL**: Database server
- **Redis**: Caching server
- **Nginx**: Web server for serving the Symfony application

## 🗂️ Directory Structure

```
.
├── docker/
│   ├── nginx/
│   │   └── nginx.conf       # Nginx configuration for Symfony
│   └── php/
│       └── Dockerfile       # PHP 8.4 with required extensions
├── symfony/                 # Symfony application code
│   ├── public/
│   │   └── index.php        # Entry point for the application
│   └── .env                 # Environment variables
├── docker-compose.yml       # Docker Compose configuration
├── docker.sh                # Helper script for Docker commands
└── .gitignore               # Git ignore file
```

## 🚀 Getting Started

1. **Start the Docker environment**:
   ```bash
   docker-compose up -d
   ```

2. **Access the application**:
   - Symfony app: http://localhost:8080
   - MySQL: localhost:3306
   - Redis: localhost:6379

3. **Run Symfony commands**:
   ```bash
   docker exec -it php php bin/console <command>
   ```

4. **Run Composer commands**:
   ```bash
   docker exec -it php composer <command>
   ```

## 🔧 Configuration

### PHP/Symfony

The PHP container is built from the `docker/php/Dockerfile` and includes:
- PHP 8.4 with FPM
- Extensions: pdo_mysql, redis, intl, opcache
- Composer for dependency management

### MySQL

The MySQL service uses the official MySQL 8.0 image with:
- Root password: root_password
- Database name: training_booking
- Persistent volume for data storage

### Redis

The Redis service uses the official Redis Alpine image with:
- Persistent volume for data storage
- Default configuration

### Nginx

The Nginx service uses the official Nginx Alpine image with:
- Custom configuration for Symfony routing
- Serves the Symfony application from the `symfony/public` directory

## 🛠️ Helper Script

The `docker.sh` script provides shortcuts for common Docker commands:

```bash
# Make the script executable
chmod +x docker.sh

# Start containers
./docker.sh up

# Rebuild containers
./docker.sh build

# View logs
./docker.sh logs

# Access PHP container
./docker.sh php

# Run Composer commands
./docker.sh composer install

# Run Symfony commands
./docker.sh symfony cache:clear

# Run tests
./docker.sh test
```