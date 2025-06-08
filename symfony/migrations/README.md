# Database Migrations

This directory contains database migrations for the project.

## Running Migrations

To run the migrations, use the following command:

```bash
php bin/console doctrine:migrations:migrate
```

This will apply all pending migrations to your database.

## Creating New Migrations

After making changes to your entity classes, you can generate a new migration with:

```bash
php bin/console make:migration
```

This will create a new migration file in this directory with the SQL needed to update your database schema.

## Migration Status

To check the status of your migrations, use:

```bash
php bin/console doctrine:migrations:status
```

## Current Schema

The initial migration (Version20240601000000) creates the following tables:

1. `training` - Stores information about training sessions
2. `booking` - Stores booking information for trainings
3. `user_session` - Tracks user sessions with device tokens
4. `user` - Stores user authentication information
5. `training_review` - Stores reviews for trainings

Each table includes appropriate relationships, constraints, and timestamp fields for auditing.