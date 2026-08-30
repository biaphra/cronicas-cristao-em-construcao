<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
$meta = page_meta(['title' => 'Contato — ' . SITE_NAME, 'description' => 'Entre em contato para convites, parcerias e projetos editoriais.', 'canonical' => url('contato.php')]);
$flash = get_flash();
require __DIR__ . '/includes/header.php';
?>
<main id="conteudo">
    <header class="page-hero contact-hero container"><span class="eyebrow">Correspondência</span><h1>Vamos <em>conversar?</em></h1><p>Para convites, parcerias, projetos editoriais ou uma boa conversa sobre palavras e caminhos.</p></header>
    <section class="section contact-section"><div class="container contact-grid">
        <aside class="contact-aside"><span class="eyebrow">Antes de enviar</span><h2>Do outro lado há uma pessoa.</h2><p>Escreva com calma. Sua mensagem será lida com atenção.</p><div><span>Instagram</span><a href="<?= e(setting('instagram_url', INSTAGRAM_URL)) ?>" target="_blank" rel="noopener"><?= e(INSTAGRAM_HANDLE) ?></a></div></aside>
        <form class="contact-form" action="contato-process.php" method="post" data-form>
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input class="honeypot" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
            <?php if ($flash): ?><p class="form-message <?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></p><?php endif; ?>
            <div class="field-row"><div class="field"><label for="name">Nome</label><input id="name" name="name" type="text" maxlength="100" autocomplete="name" required></div><div class="field"><label for="email">E-mail</label><input id="email" name="email" type="email" maxlength="160" autocomplete="email" required></div></div>
            <div class="field"><label for="subject">Assunto</label><select id="subject" name="subject" required><option value="">Selecione uma opção</option><option>Convites</option><option>Parcerias</option><option>Publicidade</option><option>Eventos</option><option>Projetos editoriais</option><option>Outros</option></select></div>
            <div class="field"><label for="message">Mensagem</label><textarea id="message" name="message" rows="7" maxlength="3000" required></textarea><small>Até 3.000 caracteres.</small></div>
            <button class="button button-dark" type="submit">Enviar mensagem <span>→</span></button>
        </form>
    </div></section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
