import './bootstrap';
import Alpine from 'alpinejs';
import { Chart, registerables } from 'chart.js';
import { createIcons, icons } from 'lucide';

Chart.register(...registerables);
window.Chart = Chart;

window.renderLucide = () => {
    createIcons({ icons });
};

window.Alpine = Alpine;
Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    window.renderLucide();
});
