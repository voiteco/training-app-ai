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

    <div v-else-if="error" class="text-center py-12">
      <p class="text-xl text-red-500">{{ error }}</p>
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
      trainings: [],
      loading: true,
      error: null,
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
  },
  async mounted() {
    try {
      const response = await fetch('/api/trainings');
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      const data = await response.json();
      // Assuming the API response structure is { status: 'success', source: '...', count: ..., trainings: [...] }
      // Or if it's directly an array of trainings from the backend response.
      // For now, let's assume the provided example structure where trainings are nested under a 'trainings' key.
      // If the actual API returns data.trainings directly, this will need adjustment.
      if (data && data.trainings) {
        this.trainings = data.trainings;
      } else if (Array.isArray(data)) { // Fallback if the API returns an array directly
        this.trainings = data;
      } else {
        // If the structure is different and trainings are not found
        console.error('Unexpected API response structure:', data);
        this.trainings = []; // Set to empty if data is not in expected format
        this.error = 'Failed to load trainings due to unexpected data format.';
      }
    } catch (e) {
      console.error('Failed to fetch trainings:', e);
      this.error = 'Failed to load trainings. Please try again later.';
      this.trainings = []; // Ensure trainings is empty on error
    } finally {
      this.loading = false;
    }
  }
}
</script>
