(() => {
  const sidebar = document.querySelector('[data-sidebar]');
  const overlay = document.querySelector('[data-sidebar-overlay]');
  const toggle = document.querySelector('[data-sidebar-toggle]');
  const setSidebar = (open) => {
    sidebar?.classList.toggle('open', open);
    overlay?.classList.toggle('open', open);
    toggle?.setAttribute('aria-expanded', String(open));
  };
  toggle?.addEventListener('click', () => setSidebar(!sidebar?.classList.contains('open')));
  overlay?.addEventListener('click', () => setSidebar(false));
  document.querySelectorAll('[data-dismiss]').forEach((button) => button.addEventListener('click', () => button.parentElement?.remove()));

  const title = document.querySelector('[data-title]');
  const slug = document.querySelector('[data-slug]');
  let slugEdited = Boolean(slug?.value);
  slug?.addEventListener('input', () => { slugEdited = true; });
  title?.addEventListener('input', () => {
    if (!slug || slugEdited) return;
    slug.value = title.value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
  });

  const editor = document.querySelector('[data-editor]');
  document.querySelectorAll('[data-insert]').forEach((button) => button.addEventListener('click', () => {
    if (!editor) return;
    const [before, after = ''] = button.dataset.insert.split('|');
    const start = editor.selectionStart;
    const end = editor.selectionEnd;
    const selection = editor.value.slice(start, end) || 'texto';
    editor.setRangeText(before + selection + after, start, end, 'select');
    editor.focus();
  }));

  document.querySelectorAll('[data-confirm]').forEach((form) => form.addEventListener('submit', (event) => {
    if (!confirm(form.dataset.confirm || 'Confirmar esta ação?')) event.preventDefault();
  }));

  const actionMenus = [...document.querySelectorAll('.action-menu')];
  actionMenus.forEach((menu) => menu.addEventListener('toggle', () => {
    menu.querySelector('summary')?.setAttribute('aria-expanded', String(menu.open));
    if (!menu.open) return;
    actionMenus.forEach((other) => {
      if (other !== menu) other.open = false;
    });
  }));
  document.addEventListener('click', (event) => {
    actionMenus.forEach((menu) => {
      if (menu.open && !menu.contains(event.target)) menu.open = false;
    });
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') actionMenus.forEach((menu) => { menu.open = false; });
  });

  const deleteDialog = document.querySelector('[data-delete-dialog]');
  const deleteTitle = deleteDialog?.querySelector('[data-delete-title]');
  let pendingDeleteForm = null;
  let deleteTrigger = null;
  document.querySelectorAll('[data-delete-form]').forEach((form) => form.addEventListener('submit', (event) => {
    event.preventDefault();
    const parentMenu = form.closest('.action-menu');
    if (parentMenu) parentMenu.open = false;
    if (!deleteDialog?.showModal) {
      if (confirm(`Excluir “${form.dataset.deleteTitle}” permanentemente?`)) HTMLFormElement.prototype.submit.call(form);
      return;
    }
    pendingDeleteForm = form;
    deleteTrigger = event.submitter;
    if (deleteTitle) deleteTitle.textContent = `“${form.dataset.deleteTitle}”`;
    deleteDialog.showModal();
  }));
  deleteDialog?.addEventListener('close', () => {
    if (deleteDialog.returnValue === 'confirm' && pendingDeleteForm) {
      HTMLFormElement.prototype.submit.call(pendingDeleteForm);
      return;
    }
    deleteTrigger?.focus();
    pendingDeleteForm = null;
  });
  deleteDialog?.addEventListener('click', (event) => {
    if (event.target === deleteDialog) deleteDialog.close('cancel');
  });
})();
