# Training Booking System

A web application for managing and booking training sessions. It integrates with Google Sheets, stores bookings in a relational database, and provides both public API access and an admin interface (in the future).

## 🚀 Stack

- **Frontend**: Vue 3 + Vite + Tailwind CSS  
- **Backend**: Symfony (PHP 8.4)  
  *The backend is currently configured for PHP 8.4. For broader compatibility or long-term support, consider targeting PHP 8.2 or 8.3.*
- **Database**: MySQL  
- **Cache**: Redis  
- **API Integration**: Google Sheets API  
- **Mailing**: Symfony Mailer + Messenger  
- **Monitoring**: Sentry  
- **API Docs**: Swagger UI (via NelmioApiDocBundle)
- **Scheduling**: Cron + Supervisor

## 📦 Features

- View available trainings  
- Book a training session  
- View and cancel your bookings  
- Autofill form data using device token  
- Email confirmation on booking  
- Admin panel (planned via EasyAdminBundle)
- Automatic training data synchronization (every 15 minutes)

## 🛠 Requirements

- Docker + Docker Compose  
- Node.js + npm (for frontend)

## 📂 Getting Started

### 1. Clone the repository

```bash
git clone <repo-url>
cd project-folder
```

### 2. Set up environment

```bash
# Copy the environment file
cp symfony/.env symfony/.env.local

# Start the Docker containers
docker-compose up -d --build
```

### 3. Backend setup

```bash
docker exec php composer install
docker exec php php bin/console doctrine:migrations:migrate
```

### 4. Frontend setup

```bash
cd frontend
npm install
npm run dev
```

### 5. Run Google Sheets sync manually

```bash
docker exec php php bin/console app:sync-trainings
```

## 🔄 Scheduled Tasks

The application automatically synchronizes training data from Google Sheets every 15 minutes using a cron job configured in the PHP container. The synchronization logs are stored in `symfony/var/log/sync.log`.

To verify the cron setup:

```bash
docker exec php /usr/local/bin/test-cron.sh
```

## 🔗 Access URLs

- Frontend: [http://localhost:5173](http://localhost:5173)  
- Backend API: [http://localhost:8080/api](http://localhost:8080/api)  
- Swagger Docs: [http://localhost:8080/api/docs](http://localhost:8080/api/docs)

## 🧪 Running Tests

```bash
docker exec php php bin/phpunit
```

## 🛡 Security & Monitoring

- CORS configured with NelmioCorsBundle  
- Rate limiting via Symfony RateLimiter  
- Security headers set in Nginx  
- Errors tracked with Sentry

## 🔒 Security Considerations

- **Important**: The default Docker setup uses insecure credentials (e.g., `MYSQL_ROOT_PASSWORD=root_password`). These **must** be changed for any production or publicly accessible deployment. Refer to `.env` and `docker-compose.yml` for relevant settings.

## ✅ TODO

- Admin panel for managing trainings and bookings  
- Mobile-friendly improvements  
- Booking reminders and feedback collection
