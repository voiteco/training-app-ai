# Training Booking System Frontend

This is the frontend for the Training Booking System, built with Vue 3, Vite, and Tailwind CSS.

## Project Setup

```sh
npm install
```

### Compile and Hot-Reload for Development

```sh
npm run dev
```

### Compile and Minify for Production

```sh
npm run build
```

### Lint with ESLint

```sh
npm run lint
```

## Features

- View available trainings
- Book training sessions
- View and manage your bookings
- Responsive design with Tailwind CSS

## Project Structure

- `src/components/`: Reusable Vue components
- `src/views/`: Page components
- `src/router/`: Vue Router configuration
- `src/assets/`: Static assets like images and styles
- `src/App.vue`: Root component
- `src/main.js`: Application entry point

## API Integration

The frontend communicates with the Symfony backend API. The API base URL is configured in the Vite config file.