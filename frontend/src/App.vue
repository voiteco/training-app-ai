<template>
  <div class="min-h-screen bg-gray-100">
    <NavBar />
    <main class="container mx-auto px-4 py-8">
      <router-view />
    </main>
    <footer class="bg-gray-800 text-white p-4 text-center">
      <p>&copy; {{ new Date().getFullYear() }} Training Booking System</p>
    </footer>
  </div>
</template>

<script>
import NavBar from './components/NavBar.vue'

export default {
  name: 'App',
  components: {
    NavBar
  }
}
</script>