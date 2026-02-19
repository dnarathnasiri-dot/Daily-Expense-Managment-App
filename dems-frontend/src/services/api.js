import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost/dems-backend/handlers/auth',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true,
});

// Attach token to every request
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('dems_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Handle 401 globally
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('dems_token');
      localStorage.removeItem('dems_user');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default api;