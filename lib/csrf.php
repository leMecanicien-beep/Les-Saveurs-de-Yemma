<?php
function generer_token_csrf(): string {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifier_token_csrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        die('Erreur de sécurité : token CSRF invalide. <a href="javascript:history.back()">Retour</a>');
    }
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
