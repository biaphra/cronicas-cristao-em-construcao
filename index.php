<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

$meta = page_meta([
    'title' => site_name() . ' — Fé, humor e vida real',
    'canonical' => url(),
]);
$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => site_name(),
    'url' => url(),
    'description' => setting('site_description', SITE_DESCRIPTION),
];
require __DIR__ . '/includes/header.php';
$featured = featured_post();
$latestPosts = array_values(array_filter(post_repository()->published(7), fn(array $item): bool => !$featured || $item['id'] !== $featured['id']));
$latestPosts = array_slice($latestPosts, 0, 6);
?>
<main id="conteudo">
    <section class="hero">
        <div class="container hero-grid">
            <div class="hero-copy reveal">
                <div class="hero-kicker"><span>FÉ</span><i></i><span>HUMOR</span><i></i><span>VIDA REAL</span></div>
                <h1><span>As crônicas</span><span> de um cristão</span><em>em construção.</em></h1>
                <p>Crônicas sobre fé, tropeços, risadas e a estranha beleza de aprender a caminhar com Deus todos os dias.</p>
                <div class="hero-actions"><a class="button button-dark" href="cronicas.php">Ler as crônicas <span>→</span></a><a class="button button-text" href="sobre.php">Conhecer o autor</a></div>
            </div>
            <div class="hero-art reveal" aria-label="Composição editorial abstrata sobre processo e transformação">
                <div class="art-paper"><span class="art-label">PROJETO EM CURSO</span><span class="art-cross">+</span><div class="art-arch"></div><p>“Deus ainda<br>não terminou.”</p><small>rascunho · capítulo 01</small></div>
                <div class="status-stamp"><span>STATUS DA OBRA</span><strong>EM ANDAMENTO</strong><b aria-hidden="true">↗</b></div>
                <span class="margin-note">a graça trabalha<br>nos bastidores</span>
            </div>
        </div>
        <div class="container hero-foot"><span>CRÔNICAS DE UM CRISTÃO EM CONSTRUÇÃO</span><span>DESÇA PARA LER <b>↓</b></span></div>
    </section>

    <section class="section featured-section" aria-labelledby="featured-title">
        <div class="container"><div class="section-heading reveal"><div><span class="eyebrow">Escolha do editor · 01</span><h2 id="featured-title">Crônica em <em>destaque</em></h2></div><p>Um texto para ler com calma.<br>Talvez com café.</p></div><?php if ($featured): $post = $featured; require __DIR__ . '/components/featured-post.php'; else: ?><p>Nenhuma crônica em destaque ainda.</p><?php endif; ?></div>
    </section>

    <section class="section latest-section" aria-labelledby="latest-title">
        <div class="container"><div class="section-heading reveal"><div><span class="eyebrow">Caderno recente · 02</span><h2 id="latest-title">Últimas <em>crônicas</em></h2></div><a class="text-link" href="cronicas.php">Ver todas as crônicas <span>→</span></a></div>
            <div class="posts-grid"><?php foreach ($latestPosts as $post) { require __DIR__ . '/components/post-card.php'; } ?></div>
        </div>
    </section>

    <section class="section fragments" aria-labelledby="fragments-title">
        <div class="container fragments-grid">
            <div class="fragments-intro reveal"><span class="eyebrow light">Notas à margem · 03</span><h2 id="fragments-title">Entre risos<br><em>e orações.</em></h2><p>Porque algumas coisas Deus ensina no silêncio. Outras, depois que a gente faz besteira.</p><span class="handwritten">anota isso →</span></div>
            <div class="fragment-list">
                <blockquote class="fragment reveal"><span>01</span><p>“Eu pedi paciência a Deus. Ele aparentemente entendeu que eu queria oportunidades para praticá-la.”</p></blockquote>
                <blockquote class="fragment reveal"><span>02</span><p>“Fé não significa entender o caminho inteiro. Às vezes significa apenas continuar andando.”</p></blockquote>
                <blockquote class="fragment reveal"><span>03</span><p>“Algumas orações não mudam a situação imediatamente. Mudam primeiro quem está orando.”</p></blockquote>
                <small>Textos editoriais </small>
            </div>
        </div>
    </section>

    <section class="section topics" aria-labelledby="topics-title">
        <div class="container"><div class="section-heading reveal"><div><span class="eyebrow">Assuntos da casa · 04</span><h2 id="topics-title">Sobre o que conversamos<br><em>por aqui?</em></h2></div></div>
            <div class="topic-grid">
                <a href="cronicas.php?categoria=Fé" class="topic reveal"><span>01</span><h3>Fé</h3><p>Sobre acreditar mesmo quando nem tudo faz sentido.</p><b>↗</b></a>
                <a href="cronicas.php?categoria=Vida+Real" class="topic reveal"><span>02</span><h3>Vida real</h3><p>Boletos, trabalho, família, escolhas e outras provas não mencionadas no culto de domingo.</p><b>↗</b></a>
                <a href="cronicas.php?categoria=Humor" class="topic reveal"><span>03</span><h3>Humor</h3><p>Porque rir de nós mesmos também pode ser uma forma de amadurecer.</p><b>↗</b></a>
            </div>
        </div>
    </section>

    <section id="autor" class="section author-home" aria-labelledby="author-title">
        <div class="container author-grid">
            <figure class="author-placeholder author-photo reveal">
                <picture>
                    <source srcset="assets/images/author-portrait.webp" type="image/webp">
                    <img src="assets/images/author-portrait.png" width="1122" height="1402" loading="lazy" decoding="async" alt="Retrato do autor das Crônicas de um Cristão em Construção">
                </picture>
            </figure>
            <div class="author-copy reveal"><span class="eyebrow">Quem escreve · 05</span><h2 id="author-title">Por trás<br><em>das crônicas</em></h2><p class="large-copy">Eu escrevo sobre uma coisa que conheço muito bem: <strong>a de estar em construção.</strong></p><p>Este projeto nasceu da vontade de conversar sobre fé de uma maneira humana, bem humorada, cotidiana e sem fingir que todas as respostas já foram encontradas.</p><a class="button button-outline" href="sobre.php">Conheça minha história <span>→</span></a></div>
        </div>
    </section>

    <section class="manifesto section" aria-labelledby="manifesto-title">
        <div class="container manifesto-grid"><div class="manifesto-side reveal"><span class="eyebrow light">Manifesto · 06</span><p>CONFISSÕES DE QUEM<br>AINDA ESTÁ APRENDENDO</p></div><div class="manifesto-copy reveal"><h2 id="manifesto-title"><span>Não tenho todas as respostas.</span><span>Ainda erro.</span><span>Ainda duvido.</span><span>Ainda fico impaciente.</span><span>Ainda preciso aprender a melhorar.</span></h2><div class="manifesto-end"><strong>Mas continuo caminhando.</strong><p>Porque a obra ainda não terminou.</p></div></div></div>
    </section>

    <section class="section central-quote"><div class="container reveal"><span class="quote-mark">“</span><blockquote>Talvez maturidade espiritual não seja chegar ao ponto em que não precisamos mais de Deus, mas perceber todos os dias o quanto precisamos.</blockquote><small>— Biaphra Araujo</small></div></section>

    <section class="section instagram-section" aria-labelledby="instagram-title"><div class="container instagram-grid"><div class="instagram-copy reveal"><span class="eyebrow">Lá fora · 07</span><h2 id="instagram-title">A construção continua<br><em>no Instagram.</em></h2><p>Crônicas curtas, pensamentos, humor e aqueles lembretes que talvez a gente precisasse ouvir hoje.</p><a class="button button-outline" href="<?= e(setting('instagram_url', INSTAGRAM_URL)) ?>" target="_blank" rel="noopener">Seguir no Instagram <span>↗</span></a></div><div class="instagram-board reveal"><div><span>@</span><strong>cronicas_<br>em_construcao</strong></div><p>fé • humor • vida real</p><small>SIGA-NOS NO INSTAGRAM.<br>NÃO VAI TE CUSTAR NADA COMPARTILHAR ESSA MENSAGEM</small></div></div></section>

    <?php require __DIR__ . '/components/newsletter.php'; ?>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
