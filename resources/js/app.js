import './bootstrap';
/*
  Add custom scripts here
*/
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();
import './alpine-chartjs';
import.meta.glob([
  '../assets/img/**',
  // '../assets/json/**',
  '../assets/vendor/fonts/**'
]);
