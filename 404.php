<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
http_response_code(404);
$meta = page_meta(['title' => 'Página em construção — ' . SITE_NAME, 'description' => 'A página que você procura não foi encontrada.', 'canonical' => url('404.php')]);
require __DIR__ . '/includes/header.php';
?>
<main id="conteudo" class="not-found"><div class="container"><span class="error-number">404</span><span class="eyebrow">Desvio de rota</span><h1>Acho que essa página<br>ainda está <em>em construção.</em> 🚧</h1><p>Ou talvez você tenha pegado um caminho que nem Moisés encontraria.</p><a class="button button-dark" href="index.php">Voltar para o início <span>→</span></a></div></main>
<?php require __DIR__ . '/includes/footer.php'; ?>
