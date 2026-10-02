document.addEventListener('DOMContentLoaded', () => {
  const yearNodes = document.querySelectorAll('#year');
  const currentYear = new Date().getFullYear();

  yearNodes.forEach((node) => {
    node.textContent = currentYear;
  });
});
