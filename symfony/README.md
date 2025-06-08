# Training Booking System - Symfony Backend

This is the Symfony backend for the Training Booking System.

## Setup

1. Start the Docker environment:
   ```bash
   ../docker.sh up
   ```

2. Install Composer dependencies:
   ```bash
   ../docker.sh composer install
   ```

3. Create the database:
   ```bash
   ../docker.sh symfony doctrine:database:create
   ```

## Testing Connections

To test the database and Redis connections:

```bash
../docker.sh symfony app:test-connections
```

## Development

The application is configured with:

- MySQL database connection
- Redis cache
- Basic controller structure

### Environment Variables

The following environment variables are configured:

- `DATABASE_URL`: MySQL connection string
- `REDIS_URL`: Redis connection string

These are set in the `.env.local` file and in the Docker environment.

### Directory Structure

- `config/`: Configuration files
- `src/`: Source code
  - `Controller/`: Controllers
  - `Command/`: Console commands
  - `Entity/`: Doctrine entities (to be added)
- `public/`: Public files, including the front controller

## Next Steps

1. Create entities and repositories
2. Set up migrations
3. Implement API endpoints
4. Add authentication and authorization