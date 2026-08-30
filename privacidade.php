<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
$meta = page_meta(['title' => 'Privacidade — ' . SITE_NAME, 'description' => 'Política de privacidade do site.', 'canonical' => url('privacidade.php')]);
require __DIR__ . '/includes/header.php';
?>
<main id="conteudo"><header class="page-hero container compact"><span class="eyebrow">Informação clara</span><h1>Privacidade</h1><p>O essencial sobre os dados enviados neste site.</p></header><article class="article-body legal"><h2>Dados de formulários</h2><p>Nome, e-mail e mensagens são usados somente para responder ao contato ou registrar o interesse na newsletter. Neste protótipo, os dados são armazenados localmente no servidor e não são compartilhados com terceiros.</p><h2>Preferências locais</h2><p>O site usa o armazenamento local do navegador para guardar preferências de tema e modo de leitura. Não há rastreamento de publicidade implementado.</p><h2>Configuração futura</h2><p>Antes da publicação, esta política deve ser revisada pelo responsável pelo projeto e atualizada para refletir os serviços de e-mail, métricas e hospedagem realmente utilizados.</p></article></main>
<?php require __DIR__ . '/includes/footer.php'; ?>
