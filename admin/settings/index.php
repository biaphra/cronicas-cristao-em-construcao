<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/admin-functions.php';
require_admin();
$keys = ['site_name','site_description','instagram_url','author_name','author_bio','contact_email','newsletter_enabled','posts_per_page'];
$values = setting_repository()->all();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();
        $data = [];
        foreach ($keys as $key) $data[$key] = trim((string) ($_POST[$key] ?? ''));
        $data['newsletter_enabled'] = isset($_POST['newsletter_enabled']) ? '1' : '0';
        $data['posts_per_page'] = (string) max(3, min(24, (int) $data['posts_per_page']));
        if (mb_strlen($data['site_name']) < 3) $errors[] = 'Informe o nome do site.';
        if ($data['instagram_url'] && !filter_var($data['instagram_url'], FILTER_VALIDATE_URL)) $errors[] = 'Informe uma URL válida para o Instagram.';
        if ($data['contact_email'] && !filter_var($data['contact_email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Informe um e-mail de contato válido.';
        if (!$errors) { setting_repository()->saveMany($data); set_flash('success', 'Configurações atualizadas.'); redirect(admin_url('settings/index.php')); }
        $values = $data;
    } catch (Throwable $exception) { log_error($exception); $errors[] = 'Não foi possível salvar as configurações.'; }
}
$adminTitle = 'Configurações'; $adminSection = 'settings'; require __DIR__ . '/../includes/admin-header.php';
?>
<div class="admin-page-head"><div><p class="admin-kicker">Identidade e operação</p><h1>Configurações <em>do site.</em></h1><p>Os valores públicos são carregados diretamente desta área.</p></div></div><?php if ($errors): ?><div class="admin-alert error"><?= e(implode(' ', $errors)) ?></div><?php endif; ?><form method="post"><?= csrf_field() ?><div class="admin-form-layout"><div class="admin-fields"><section class="admin-form-section"><h2>Marca</h2><div class="admin-fields"><label class="admin-field">Nome do site<input name="site_name" value="<?= e($values['site_name'] ?? '') ?>" required></label><label class="admin-field">Descrição<textarea name="site_description" rows="4"><?= e($values['site_description'] ?? '') ?></textarea></label><label class="admin-field">URL do Instagram<input type="url" name="instagram_url" value="<?= e($values['instagram_url'] ?? '') ?>"></label></div></section><section class="admin-form-section"><h2>Autor</h2><div class="admin-fields"><label class="admin-field">Nome do autor<input name="author_name" value="<?= e($values['author_name'] ?? '') ?>"></label><label class="admin-field">Biografia curta<textarea name="author_bio" rows="4"><?= e($values['author_bio'] ?? '') ?></textarea></label><label class="admin-field">E-mail de contato<input type="email" name="contact_email" value="<?= e($values['contact_email'] ?? '') ?>"></label></div></section></div><aside class="admin-fields"><section class="admin-form-section"><h2>Publicação</h2><div class="admin-fields"><label class="admin-field">Posts por página<input type="number" name="posts_per_page" min="3" max="24" value="<?= e($values['posts_per_page'] ?? '9') ?>"></label><label class="check-field"><input type="checkbox" name="newsletter_enabled" value="1" <?= ($values['newsletter_enabled'] ?? '1') === '1' ? 'checked' : '' ?>> Exibir newsletter</label></div></section><div class="help-box">Configurações sensíveis e segredos continuam em variáveis de ambiente, nunca nesta tela.</div></aside></div><div class="admin-actions"><button class="admin-button primary" type="submit">Salvar configurações</button></div></form>
<?php require __DIR__ . '/../includes/admin-footer.php'; ?>
