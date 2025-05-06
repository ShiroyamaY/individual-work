<script setup>
import TheHeader from './components/TheHeader.vue'
import TheFooter from "@/components/TheFooter.vue";
import TheMain from "@/components/TheMain.vue";
import { useAuthStore } from '@/store/auth.js';
import { onMounted } from 'vue';

const authStore = useAuthStore();

onMounted(async () => {
  if (authStore.isAuthenticated) {
    try {
      await authStore.fetchUser();
    } catch (error) {
      console.error('Error fetching user:', error);
    }
  }
});
</script>

<template>
  <TheHeader/>
  <TheMain />
  <TheFooter/>
</template>
