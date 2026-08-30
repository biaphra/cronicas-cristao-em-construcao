(() => {
  const article = document.querySelector('[data-article]');
  const body = document.querySelector('[data-article-body]');
  const progress = document.querySelector('[data-reading-progress]');
  if (!article || !body || !progress) return;
  progress.hidden = false;

  const updateProgress = () => {
    const start = body.offsetTop;
    const distance = Math.max(body.offsetHeight - innerHeight, 1);
    const percent = Math.min(100, Math.max(0, ((scrollY - start + innerHeight * .2) / distance) * 100));
    progress.style.width = `${percent}%`;
  };
  addEventListener('scroll', updateProgress, { passive: true });
  addEventListener('resize', updateProgress);
  updateProgress();

  const readingButton = document.querySelector('[data-reading-mode]');
  const savedMode = localStorage.getItem('reading-mode') === 'true';
  document.body.classList.toggle('reading-mode', savedMode);
  readingButton?.setAttribute('aria-pressed', String(savedMode));
  readingButton?.addEventListener('click', () => {
    const active = document.body.classList.toggle('reading-mode');
    readingButton.setAttribute('aria-pressed', String(active));
    readingButton.textContent = active ? '◫ Sair do modo leitura' : '◫ Modo leitura';
    localStorage.setItem('reading-mode', String(active));
  });

  let size = Number(localStorage.getItem('article-font-size') || 1.2);
  const applySize = () => { body.style.fontSize = `${size}rem`; };
  applySize();
  document.querySelector('[data-font-increase]')?.addEventListener('click', () => {
    size = Math.min(1.5, Math.round((size + .1) * 10) / 10); applySize(); localStorage.setItem('article-font-size', String(size));
  });
  document.querySelector('[data-font-decrease]')?.addEventListener('click', () => {
    size = Math.max(1, Math.round((size - .1) * 10) / 10); applySize(); localStorage.setItem('article-font-size', String(size));
  });

  const copyButton = document.querySelector('[data-copy-link]');
  const copyStatus = document.querySelector('[data-copy-status]');
  copyButton?.addEventListener('click', async () => {
    try {
      await navigator.clipboard.writeText(copyButton.dataset.url || location.href);
      copyStatus.textContent = 'Link copiado. Agora é só compartilhar.';
    } catch {
      copyStatus.textContent = 'Não foi possível copiar automaticamente. Copie o endereço do navegador.';
    }
  });
})();
