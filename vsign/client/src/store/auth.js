import { ref, computed } from 'vue';
import axios from '@/axios';

export const useAuthStore = () => {
  const user = ref(null);
  const token = ref(localStorage.getItem('token') || null);

  const isAuthenticated = computed(() => !!token.value);

  const loginWithGithub = async () => {
    window.location.href = '/api/v1/auth/github/redirect';
  };

  const fetchUser = async () => {
    if (!token.value) return false;

    try {
      const response = await axios.get('/user');
      user.value = response.data;

      return true;
    } catch (error) {
      console.error('Error on getting users data', error);

      token.value = null;
      localStorage.removeItem('token');

      return false;
    }
  };

  const logout = () => {
    token.value = null;
    user.value = null;
    localStorage.removeItem('token');
  };

  const setToken = (newToken) => {
    token.value = newToken;
    localStorage.setItem('token', newToken);
  };

  return {
    user,
    token,
    loginWithGithub,
    fetchUser,
    logout,
    setToken,
    isAuthenticated
  };
};
