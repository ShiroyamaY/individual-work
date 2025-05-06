import axios from 'axios';

const instance = axios.create({
  baseURL: 'https://vsign.localdev.me:8443/api/v1',
});

instance.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers['Authorization'] = `Bearer ${token}`;
    config.headers['Accept'] = 'application/json';
  }
  return config;
});

export default instance;
