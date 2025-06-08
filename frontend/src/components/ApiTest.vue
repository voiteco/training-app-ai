<template>
  <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
    <h2 class="text-2xl font-semibold mb-4">API Connection Test</h2>
    <div v-if="loading" class="text-gray-700">
      Loading...
    </div>
    <div v-else-if="error" class="text-red-600">
      Error: {{ error }}
    </div>
    <div v-else class="text-gray-700">
      <p class="mb-2"><strong>Message:</strong> {{ apiData.message }}</p>
      <p class="mb-2"><strong>Status:</strong> {{ apiData.status }}</p>
      <p class="mb-2"><strong>Timestamp:</strong> {{ apiData.timestamp }}</p>
    </div>
    <button 
      @click="testApiConnection" 
      class="mt-4 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition duration-300"
    >
      Test API Connection
    </button>
  </div>
</template>

<script>
export default {
  name: 'ApiTest',
  data() {
    return {
      apiData: null,
      loading: false,
      error: null
    }
  },
  methods: {
    async testApiConnection() {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await fetch('/api/test');
        if (!response.ok) {
          throw new Error(`HTTP error! Status: ${response.status}`);
        }
        this.apiData = await response.json();
      } catch (error) {
        this.error = error.message;
        console.error('API connection error:', error);
      } finally {
        this.loading = false;
      }
    }
  }
}
</script>