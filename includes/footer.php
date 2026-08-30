<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <a class="brand footer-brand" href="index.php"><span class="brand-mark" aria-hidden="true">C</span><span class="brand-text"><strong>Crônicas</strong><small>de um cristão em construção</small></span></a>
            <p>Fé, humor e vida real.<br>Um lugar para continuar caminhando.</p>
        </div>
        <nav aria-label="Links do rodapé">
            <span class="eyebrow">Navegue</span>
            <a href="cronicas.php">Crônicas</a>
            <a href="sobre.php">Sobre</a>
            <a href="contato.php">Contato</a>
            <a href="privacidade.php">Privacidade</a>
        </nav>
        <div class="footer-note">
            <span class="eyebrow">Por aqui</span>
            <a href="<?= e(setting('instagram_url', INSTAGRAM_URL)) ?>" target="_blank" rel="noopener"><?= e(INSTAGRAM_HANDLE) ?></a>
            <p>🚧 Esta obra continua<br>em andamento.</p>
        </div>
    </div>
    <div class="container footer-bottom"><span>© <?= date('Y') ?> <?= e(site_name()) ?>.</span><button type="button" class="back-to-top" data-back-to-top aria-label="Voltar ao topo">↑ Topo</button></div>
</footer>
<script src="assets/js/main.js" defer></script>
<?php if (current_page() === 'cronica.php'): ?><script src="assets/js/reading-progress.js" defer></script><?php endif; ?>
</body>
</html>
