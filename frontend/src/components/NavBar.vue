<template>
  <nav class="bg-blue-600 text-white shadow-lg">
    <div class="container mx-auto px-4">
      <div class="flex justify-between items-center py-4">
        <div class="flex items-center">
          <router-link to="/" class="text-xl font-bold">Training Booking System</router-link>
        </div>
        <div class="hidden md:flex space-x-6">
          <router-link to="/" class="hover:text-blue-200 transition duration-300">Home</router-link>
          <router-link to="/trainings" class="hover:text-blue-200 transition duration-300">Trainings</router-link>
          <router-link to="/bookings" class="hover:text-blue-200 transition duration-300">My Bookings</router-link>
        </div>
        <div class="md:hidden">
          <button @click="toggleMobileMenu" class="focus:outline-none">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
      <div v-if="mobileMenuOpen" class="md:hidden py-4">
        <div class="flex flex-col space-y-3">
          <router-link to="/" class="hover:text-blue-200 transition duration-300">Home</router-link>
          <router-link to="/trainings" class="hover:text-blue-200 transition duration-300">Trainings</router-link>
          <router-link to="/bookings" class="hover:text-blue-200 transition duration-300">My Bookings</router-link>
        </div>
      </div>
    </div>
  </nav>
</template>

<script>
export default {
  name: 'NavBar',
  data() {
    return {
      mobileMenuOpen: false
    }
  },
  methods: {
    toggleMobileMenu() {
      this.mobileMenuOpen = !this.mobileMenuOpen
    }
  }
}
</script>
