import { createRouter, createWebHistory } from 'vue-router';
import { routes } from "@/router/routes.js";
import { useAuthStore } from '@/store/auth.js';

export const router = createRouter({
  history: createWebHistory(),
  routes: routes
});

router.beforeEach(async (to) => {
  const authStore = useAuthStore();

  if ('auth' in to.meta) {
    if (to.meta.auth) {
      if (!authStore.isAuthenticated.value) {
        return { name: 'Login' };
      }
      return true;
    }

    if (authStore.isAuthenticated.value) {
      return { name: 'Dashboard' };
    }
  }

  return true;
});
