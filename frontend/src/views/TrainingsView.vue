<template>
  <div class="py-8">
    <h1 class="text-3xl font-bold text-center text-blue-600 mb-8">Available Trainings</h1>
    
    <div class="mb-6">
      <div class="max-w-md mx-auto">
        <div class="relative">
          <input 
            type="text" 
            v-model="searchQuery" 
            placeholder="Search trainings..." 
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
          <div class="absolute inset-y-0 right-0 flex items-center pr-3">
            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>
      </div>
    </div>
    
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
    </div>
    
    <div v-else-if="trainings.length === 0" class="text-center py-12">
      <p class="text-xl text-gray-600">No trainings available at the moment.</p>
    </div>
    
    <div v-else class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div 
        v-for="training in filteredTrainings" 
        :key="training.id" 
        class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300"
      >
        <div class="p-6">
          <h2 class="text-xl font-semibold text-blue-600 mb-2">{{ training.title }}</h2>
          <div class="flex items-center text-gray-600 mb-2">
            <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>{{ training.date }}</span>
          </div>
          <div class="flex items-center text-gray-600 mb-4">
            <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ training.time }}</span>
          </div>
          <p class="text-gray-700 mb-4">{{ training.description }}</p>
          <div class="flex items-center justify-between">
            <span class="text-sm font-semibold" :class="training.spotsAvailable > 0 ? 'text-green-600' : 'text-red-600'">
              {{ training.spotsAvailable > 0 ? `${training.spotsAvailable} spots available` : 'Fully booked' }}
            </span>
            <button 
              class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
              :disabled="training.spotsAvailable <= 0"
            >
              Book Now
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TrainingsView',
  data() {
    return {
      trainings: [
        {
          id: 1,
          title: 'Introduction to Vue 3',
          date: '2023-09-15',
          time: '10:00 - 12:00',
          description: 'Learn the basics of Vue 3 and its composition API.',
          spotsAvailable: 5
        },
        {
          id: 2,
          title: 'Advanced JavaScript Patterns',
          date: '2023-09-20',
          time: '14:00 - 16:00',
          description: 'Dive deep into advanced JavaScript patterns and best practices.',
          spotsAvailable: 3
        },
        {
          id: 3,
          title: 'Tailwind CSS Masterclass',
          date: '2023-09-25',
          time: '09:00 - 11:00',
          description: 'Master utility-first CSS with Tailwind CSS framework.',
          spotsAvailable: 0
        },
        {
          id: 4,
          title: 'API Integration with Axios',
          date: '2023-10-05',
          time: '13:00 - 15:00',
          description: 'Learn how to integrate APIs in your Vue applications using Axios.',
          spotsAvailable: 8
        }
      ],
      loading: false,
      searchQuery: ''
    }
  },
  computed: {
    filteredTrainings() {
      if (!this.searchQuery) return this.trainings
      
      const query = this.searchQuery.toLowerCase()
      return this.trainings.filter(training => 
        training.title.toLowerCase().includes(query) || 
        training.description.toLowerCase().includes(query)
      )
    }
  }
}
</script>