<script setup>
import { useRouter } from 'vue-router';

const router = useRouter();
</script>

<template>
  <div class="bg-white p-8 rounded-lg shadow-lg max-w-4xl mx-auto flex flex-col items-center text-center">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">404 - Page Not Found</h1>
    <p class="text-gray-600 mb-6">
      The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
    </p>
    <router-link
      :to="{ name: 'Dashboard' }"
      class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 cursor-pointer"
    >
      Go to dashboard
    </router-link>
  </div>
</template>
