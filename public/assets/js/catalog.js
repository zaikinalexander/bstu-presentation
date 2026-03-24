document.addEventListener('DOMContentLoaded', () => {
  const searchInput = document.querySelector('.search-form input[type="search"]');
  const cards = Array.from(document.querySelectorAll('[data-catalog] .person-card'));

  if (!searchInput || cards.length === 0) {
    return;
  }

  searchInput.addEventListener('input', () => {
    const term = searchInput.value.trim().toLowerCase();

    cards.forEach((card) => {
      const haystack = `${card.dataset.name || ''} ${card.dataset.search || ''}`;
      const visible = term === '' || haystack.includes(term);
      card.hidden = !visible;
    });
  });
});
