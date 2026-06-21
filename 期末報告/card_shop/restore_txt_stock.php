<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

$token = $_GET['token'] ?? '';
if (!hash_equals('codex-restore-txt-20260620', $token)) {
    http_response_code(403);
    exit('forbidden');
}

try {
    $pdo = db();
    $pdo->beginTransaction();

    $pdo->exec(
        'UPDATE products
         SET stock = 1,
             status = "active",
             sold_at = NULL,
             updated_at = NOW()
         WHERE id IN (1, 2)'
    );

    $pdo->commit();
    echo 'RESTORE_OK';
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    echo 'RESTORE_FAIL';
}
