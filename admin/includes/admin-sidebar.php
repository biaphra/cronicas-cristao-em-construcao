<aside class="admin-sidebar" data-sidebar>
    <a class="admin-brand" href="<?= e(admin_url()) ?>"><span>C</span><strong>Crônicas<small>painel editorial</small></strong></a>
    <nav aria-label="Administração">
        <a href="<?= e(admin_url('dashboard.php')) ?>" class="<?= $adminSection === 'dashboard' ? 'active' : '' ?>"><span>⌂</span> Dashboard</a>
        <a href="<?= e(admin_url('posts/index.php')) ?>" class="<?= $adminSection === 'posts' ? 'active' : '' ?>"><span>□</span> Crônicas</a>
        <a href="<?= e(admin_url('posts/create.php')) ?>"><span>＋</span> Nova crônica</a>
        <a href="<?= e(admin_url('categories/index.php')) ?>" class="<?= $adminSection === 'categories' ? 'active' : '' ?>"><span>◇</span> Categorias</a>
        <a href="<?= e(admin_url('settings/index.php')) ?>" class="<?= $adminSection === 'settings' ? 'active' : '' ?>"><span>⚙</span> Configurações</a>
    </nav>
    <div class="sidebar-bottom"><a href="<?= e(url()) ?>" target="_blank" rel="noopener">↗ Ver site</a><form action="<?= e(admin_url('logout.php')) ?>" method="post"><?= csrf_field() ?><button type="submit">Sair</button></form></div>
</aside>
<div class="sidebar-overlay" data-sidebar-overlay></div>
