# Redis Caching for Training Data

This document describes the implementation of Redis caching for training data in the application.

## Overview

The application now uses Redis to cache training data after synchronization from Google Sheets. This reduces database load and improves API response times for the `/api/trainings` endpoint.

## Implementation Details

### Configuration

- Redis connection is configured in `.env` with `CACHE_URL=redis://redis:6379`
- The Symfony Cache component is configured to use Redis in `config/packages/framework.yaml`

### Components

1. **TrainingCacheService**
   - Handles all caching operations for training data
   - Provides methods to get, refresh, and check cache status
   - Uses a 15-minute expiration time for cached data

2. **SyncTrainingsCommand**
   - After successful synchronization from Google Sheets, refreshes the Redis cache
   - Gracefully handles cache failures without interrupting the sync process

3. **ApiController**
   - The `/api/trainings` endpoint first tries to get data from Redis
   - Falls back to database queries if Redis is unavailable
   - Includes the data source (cache/database) in the response

4. **TrainingRepository**
   - Added `findUpcomingTrainings()` method to get trainings with dates >= today
   - Added `serializeTraining()` method to standardize training data format

## Cache Keys

- `training_list` - Stores the complete list of upcoming trainings

## Fallback Mechanism

If Redis is unavailable or returns an error:
1. The application logs a warning
2. Falls back to querying the database directly
3. Continues operation without interruption

## Testing

Two test classes were added:
- `TrainingCacheServiceTest` - Tests the caching service functionality
- `ApiControllerTest` - Tests the API endpoint with both cache hits and cache misses

## Usage

No changes are required in how you use the application. The caching is transparent to users and automatically refreshes when training data is synchronized.