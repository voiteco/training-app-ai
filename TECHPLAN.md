# 📄 Technical Plan: Training Booking System

## 1. Architecture

- **Frontend**: Vue 3 + Vite + Tailwind CSS  
- **Backend**: Symfony (PHP 8.4)  
- **Database**: MySQL  
- **Cache**: Redis  
- **Integrations**: Google Sheets API (read-only)  
- **Mailing**: Symfony Mailer (asynchronous via Messenger)  
- **API Documentation**: NelmioApiDocBundle + OpenAPI (Swagger UI)  
- **Monitoring**: Sentry  
- **Security**: CORS, security headers, rate limiting

## 2. Database

- Tables: `trainings`, `bookings`, `user_sessions`, `users`, `training_reviews`  
- Managed via Doctrine migrations

## 3. Key Modules

- **Training Sync**: Cron every 15 minutes, loads from Google Sheets, cached in Redis  
- **API**: REST endpoints for trainings, bookings, and user data  
- **Booking**: form with validation, slot availability check, confirmation email  
- **User Session**: device-based identification via `deviceToken`, auto-fill support  
- **Frontend**: SPA app with list view, detail page, booking form, and user history

## 4. Security

- CORS via NelmioCorsBundle  
- Rate limiting via Symfony RateLimiter  
- Security headers (X-Frame-Options, X-XSS-Protection, etc.)  
- Input validation and sanitization

## 5. Documentation & CI/CD

- API docs available at `/api/docs` via Swagger UI  
- README with setup instructions  
- Unit and integration tests using PHPUnit + Symfony WebTestCase  
- Environment setup via Docker + Docker Compose

## 6. Development Sprints

The implementation is divided into 7 sprints:

1. Docker + environment + database setup  
2. Google Sheets integration  
3. REST API implementation  
4. Email, monitoring, Swagger  
5. Security  
6. Frontend  
7. Final testing and documentation
