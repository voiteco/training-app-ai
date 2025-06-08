<template>
  <div class="py-8">
    <h1 class="text-3xl font-bold text-center text-blue-600 mb-8">My Bookings</h1>
    
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
    </div>
    
    <div v-else-if="bookings.length === 0" class="max-w-2xl mx-auto bg-white rounded-lg shadow-lg p-8 text-center">
      <svg class="h-16 w-16 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
      </svg>
      <h2 class="text-xl font-semibold mb-2">No bookings yet</h2>
      <p class="text-gray-600 mb-6">You haven't booked any training sessions yet.</p>
      <router-link 
        to="/trainings" 
        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition duration-300"
      >
        Browse Available Trainings
      </router-link>
    </div>
    
    <div v-else class="max-w-4xl mx-auto">
      <div 
        v-for="booking in bookings" 
        :key="booking.id" 
        class="bg-white rounded-lg shadow-lg mb-6 overflow-hidden"
      >
        <div class="p-6">
          <div class="flex flex-col md:flex-row md:justify-between md:items-center">
            <div>
              <h2 class="text-xl font-semibold text-blue-600 mb-2">{{ booking.trainingTitle }}</h2>
              <div class="flex items-center text-gray-600 mb-2">
                <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>{{ booking.date }}</span>
              </div>
              <div class="flex items-center text-gray-600 mb-4">
                <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ booking.time }}</span>
              </div>
            </div>
            
            <div class="mt-4 md:mt-0">
              <span 
                class="inline-block px-3 py-1 rounded-full text-sm font-semibold mb-4 md:mb-0 md:mr-4"
                :class="statusClass(booking.status)"
              >
                {{ booking.status }}
              </span>
              <button 
                v-if="booking.status === 'Confirmed'" 
                @click="cancelBooking(booking.id)"
                class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300"
              >
                Cancel Booking
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'BookingsView',
  data() {
    return {
      bookings: [
        {
          id: 1,
          trainingTitle: 'Introduction to Vue 3',
          date: '2023-09-15',
          time: '10:00 - 12:00',
          status: 'Confirmed'
        },
        {
          id: 2,
          trainingTitle: 'Advanced JavaScript Patterns',
          date: '2023-09-20',
          time: '14:00 - 16:00',
          status: 'Confirmed'
        },
        {
          id: 3,
          trainingTitle: 'Responsive Design Workshop',
          date: '2023-08-10',
          time: '09:00 - 11:00',
          status: 'Completed'
        }
      ],
      loading: false
    }
  },
  methods: {
    statusClass(status) {
      switch (status) {
        case 'Confirmed':
          return 'bg-green-100 text-green-800'
        case 'Cancelled':
          return 'bg-red-100 text-red-800'
        case 'Completed':
          return 'bg-gray-100 text-gray-800'
        default:
          return 'bg-blue-100 text-blue-800'
      }
    },
    cancelBooking(id) {
      // In a real app, this would make an API call
      const booking = this.bookings.find(b => b.id === id)
      if (booking) {
        booking.status = 'Cancelled'
      }
    }
  }
}
</script>
