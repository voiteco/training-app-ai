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

4. Run database migrations:
   ```bash
   ../docker.sh symfony doctrine:migrations:migrate
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
- Doctrine entities and repositories
- Database migrations

### Environment Variables

The following environment variables are configured:

- `DATABASE_URL`: MySQL connection string
- `REDIS_URL`: Redis connection string

These are set in the `.env.local` file and in the Docker environment.

### Directory Structure

- `config/`: Configuration files
- `migrations/`: Database migrations
- `src/`: Source code
  - `Controller/`: Controllers
  - `Command/`: Console commands
  - `Entity/`: Doctrine entities
  - `Repository/`: Doctrine repositories
- `public/`: Public files, including the front controller

## Database Schema

The application uses the following database tables:

1. **Training**
   - Stores information about training sessions
   - Fields: id, googleSheetId, date, time, title, slots, slotsAvailable, price, createdAt, updatedAt

2. **Booking**
   - Stores booking information for trainings
   - Fields: id, training (relation), fullName, email, phone, confirmationToken, status, deviceToken, createdAt, updatedAt
   - Status can be: active, cancelled, completed

3. **UserSession**
   - Tracks user sessions with device tokens
   - Fields: id, deviceToken, fullName, email, phone, lastVisit, createdAt, updatedAt

4. **User**
   - Stores user authentication information
   - Fields: id, email, password, createdAt

5. **TrainingReview**
   - Stores reviews for trainings
   - Fields: id, training (relation), user (relation, nullable), rating, comment, createdAt

## Future Enhancements & Planned Bundles

The following Symfony bundles are planned for future integration as per the project's technical plan:
- NelmioApiDocBundle (for enhanced API documentation)
- Symfony Mailer + Messenger (for asynchronous email notifications)
- Sentry (for error monitoring)
- EasyAdminBundle (for an admin panel)

## Next Steps

1. ✅ Create entities and repositories
2. ✅ Set up migrations
3. Implement API endpoints
4. Add authentication and authorization