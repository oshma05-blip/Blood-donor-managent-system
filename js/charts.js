document.addEventListener('DOMContentLoaded', () => {
  const chart = document.querySelector('[data-chart]');
  if (!chart) return;

  const labels = ['Donors', 'Recipients', 'Hospitals', 'Requests'];
  const values = [42, 18, 6, 24];
  chart.innerHTML = labels.map((label, index) => {
    return `<div class="chart-bar"><strong>${label}</strong><span>${values[index]}</span></div>`;
  }).join('');
});
