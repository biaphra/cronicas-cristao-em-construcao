<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/admin/includes/admin-functions.php';

header('Content-Type: application/json; charset=UTF-8');

try {
    $path = store_post_image($_FILES['image'] ?? []);
    echo json_encode(['ok' => true, 'path' => $path], JSON_THROW_ON_ERROR);
} catch (RuntimeException $exception) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => $exception->getMessage()], JSON_THROW_ON_ERROR);
}
