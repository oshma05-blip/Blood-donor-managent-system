document.addEventListener('DOMContentLoaded', () => {
  const items = document.querySelectorAll('[data-count]');
  items.forEach((item) => {
    const value = item.getAttribute('data-count');
    item.textContent = value;
  });
});
