<script setup>
import { useAuthStore } from '@/store/auth.js';

const authStore = useAuthStore();
const token = new URLSearchParams(window.location.search).get('token');

if (token) {
  console.log(token)
  authStore.setToken(token);
  authStore.fetchUser();
  window.location.href = '/';
}
</script>
