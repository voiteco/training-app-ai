# Training Booking System

A web application for managing and booking training sessions. It integrates with Google Sheets, stores bookings in a relational database, and provides both public API access and an admin interface (in the future).

## 🚀 Stack

- **Frontend**: Vue 3 + Vite + Tailwind CSS  
- **Backend**: Symfony (PHP 8.4)  
- **Database**: MySQL  
- **Cache**: Redis  
- **API Integration**: Google Sheets API  
- **Mailing**: Symfony Mailer + Messenger  
- **Monitoring**: Sentry  
- **API Docs**: Swagger UI (via NelmioApiDocBundle)

## 📦 Features

- View available trainings  
- Book a training session  
- View and cancel your bookings  
- Autofill form data using device token  
- Email confirmation on booking  
- Admin panel (planned via EasyAdminBundle)

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
cp .env .env.local
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

## ✅ TODO

- Admin panel for managing trainings and bookings  
- Mobile-friendly improvements  
- Booking reminders and feedback collection
