<?php $newsletterFlash = get_flash(); if (setting('newsletter_enabled', '1') !== '1') return; ?>
<section class="newsletter section" aria-labelledby="newsletter-title">
    <div class="container newsletter-inner reveal">
        <div><span class="eyebrow">Carta da obra · 01</span><h2 id="newsletter-title">Uma crônica de vez em quando.<br><em>Sem spam. Prometo.</em></h2><p>Reflexões sobre fé e vida real diretamente no seu e-mail.</p></div>
        <form class="newsletter-form" action="newsletter-process.php" method="post" data-form>
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <div class="field"><label for="newsletter-name">Seu nome</label><input id="newsletter-name" name="name" type="text" autocomplete="name" maxlength="80" required></div>
            <div class="field"><label for="newsletter-email">Seu melhor e-mail</label><input id="newsletter-email" name="email" type="email" autocomplete="email" maxlength="160" required></div>
            <button class="button button-light" type="submit">Quero receber <span>→</span></button>
            <p class="form-note">Ao assinar, você concorda com nossa política de privacidade.</p>
            <?php if ($newsletterFlash): ?><p class="form-message <?= e($newsletterFlash['type']) ?>" role="status"><?= e($newsletterFlash['message']) ?></p><?php endif; ?>
        </form>
    </div>
</section>
