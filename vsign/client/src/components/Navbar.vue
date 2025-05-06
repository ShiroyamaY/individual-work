<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '@/store/auth.js';
import { useRoute, useRouter } from 'vue-router';

const authStore = useAuthStore();
const route = useRoute();
const router = useRouter();

const isAuthenticated = computed(() => authStore.isAuthenticated.value);
const isDropdownOpen = ref(false);
const user = computed(() => authStore.user.value);

const menuItems = [
  { name: 'Dashboard', route: 'Dashboard' },
  { name: 'Documents', route: 'DocumentsToSign'}
];

const logout = async () => {
  authStore.logout();
  router.push({ name: 'Login' });
};

const toggleDropdown = () => {
  isDropdownOpen.value = !isDropdownOpen.value;
};

const isCurrentPage = (name) => {
  return route.name === name;
}

const filteredMenuItems = computed(() => {
  if (!isAuthenticated.value) {
    return [];
  }

  return menuItems.filter(item => !isCurrentPage(item.route));
});

onMounted(async () => {
  if (isAuthenticated.value) {
    await authStore.fetchUser();
  }
});
</script>

<template>
  <nav class="flex space-x-4 items-center">
    <router-link
      v-for="item in filteredMenuItems"
      :key="item.route"
      :to="{ name: item.route }"
      class="text-gray-700 hover:text-indigo-600"
    >
      {{ item.name }}
    </router-link>

    <div v-if="isAuthenticated" class="relative">
      <button @click="toggleDropdown" class="flex items-center text-gray-700 hover:text-indigo-600 focus:outline-none">
        {{ user?.name }}
        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
      </button>

      <nav v-show="isDropdownOpen" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-2 z-50">
        <button @click="logout" class="block w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100">
          Logout
        </button>
      </nav>
    </div>

    <router-link v-if="!isAuthenticated && !isCurrentPage('Login')" :to="{ name: 'Login' }" class="text-gray-700 hover:text-indigo-600">
      Login
    </router-link>
  </nav>
</template>
