<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

$token = $_GET['token'] ?? '';
if (!hash_equals('codex-restore-20260620', $token)) {
    http_response_code(403);
    exit('forbidden');
}

try {
    $pdo = db();
    $pdo->beginTransaction();

    $pdo->prepare(
        'UPDATE products
         SET stock = 1,
             status = "active",
             sold_at = NULL,
             updated_at = NOW()
         WHERE id = 1'
    )->execute();

    $pdo->prepare(
        'DELETE FROM orders
         WHERE product_id = 1
           AND buyer_id = 3
         ORDER BY id DESC
         LIMIT 1'
    )->execute();

    $pdo->commit();
    echo 'RESTORE_OK';
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo 'RESTORE_FAIL';
}
