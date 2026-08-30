<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
header('Content-Type: application/xml; charset=UTF-8');
$items = [url(), url('cronicas.php'), url('sobre.php'), url('contato.php')];
foreach (post_repository()->published(1000) as $post) {
    $items[] = url('cronica.php?slug=' . rawurlencode($post['slug']));
}
echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($items as $item): ?>  <url><loc><?= e($item) ?></loc></url>
<?php endforeach; ?></urlset>
