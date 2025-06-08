<template>
  <div class="py-8">
    <div class="max-w-4xl mx-auto">
      <h1 class="text-4xl font-bold text-center text-blue-600 mb-8">Welcome to Training Booking System</h1>
      
      <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <h2 class="text-2xl font-semibold mb-4">About Our Platform</h2>
        <p class="text-gray-700 mb-4">
          Our training booking system allows you to browse available training sessions, 
          book your spot, and manage your bookings all in one place.
        </p>
        <div class="flex justify-center mt-6">
          <router-link 
            to="/trainings" 
            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition duration-300"
          >
            View Available Trainings
          </router-link>
        </div>
      </div>
      
      <div class="grid md:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow-lg p-6">
          <h3 class="text-xl font-semibold mb-3 text-blue-600">How It Works</h3>
          <ul class="list-disc pl-5 space-y-2 text-gray-700">
            <li>Browse our catalog of available training sessions</li>
            <li>Select a training that interests you</li>
            <li>Book your spot with a simple form</li>
            <li>Receive confirmation via email</li>
            <li>Manage or cancel your bookings anytime</li>
          </ul>
        </div>
        
        <div class="bg-white rounded-lg shadow-lg p-6">
          <h3 class="text-xl font-semibold mb-3 text-blue-600">Features</h3>
          <ul class="list-disc pl-5 space-y-2 text-gray-700">
            <li>Real-time availability updates</li>
            <li>Automatic form filling for returning users</li>
            <li>Email confirmations and reminders</li>
            <li>Easy booking management</li>
            <li>Mobile-friendly interface</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HomeView'
}
</script>
