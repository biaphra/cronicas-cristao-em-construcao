(() => {
  const root = document.querySelector('[data-filter-root]');
  if (!root) return;
  const buttons = [...root.querySelectorAll('[data-filter]')];
  const cards = [...root.querySelectorAll('[data-post]')];
  const input = root.querySelector('[data-search-input]');
  const count = root.querySelector('[data-results-count]');
  const empty = root.querySelector('[data-empty]');
  const available = buttons.map((button) => button.dataset.filter);
  let category = available.includes(root.dataset.initialCategory) ? root.dataset.initialCategory : 'Todas';

  const normalize = (value) => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
  const apply = () => {
    const term = normalize(input?.value.trim() || '');
    let visible = 0;
    cards.forEach((card) => {
      const categoryMatch = category === 'Todas' || card.dataset.category === category;
      const searchMatch = !term || normalize(card.dataset.search || '').includes(term);
      const show = categoryMatch && searchMatch;
      card.hidden = !show;
      if (show) visible += 1;
    });
    buttons.forEach((button) => {
      const active = button.dataset.filter === category;
      button.classList.toggle('active', active);
      button.setAttribute('aria-pressed', String(active));
    });
    count.textContent = `${visible} ${visible === 1 ? 'crônica encontrada' : 'crônicas encontradas'}`;
    empty.hidden = visible !== 0;
  };

  buttons.forEach((button) => button.addEventListener('click', () => {
    category = button.dataset.filter;
    apply();
  }));
  input?.addEventListener('input', apply);
  apply();
})();
