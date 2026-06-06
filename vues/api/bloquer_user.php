<?php

session_start();
require_once __DIR__ . '/../../lib/users.php';
require_once __DIR__ . '/../../lib/logs.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit();
}

$body    = json_decode(file_get_contents('php://input'), true);
$user_id = intval($body['user_id'] ?? 0);

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'ID utilisateur manquant.']);
    exit();
}

$users   = lire_users();
$updated = false;
$bloque  = false;

foreach ($users as &$user) {
    if ($user['id'] === $user_id) {
        // Empêcher de bloquer un admin
        if ($user['role'] === 'admin') {
            echo json_encode(['success' => false, 'message' => 'Impossible de bloquer un administrateur.']);
            exit();
        }
        $user['bloque'] = !($user['bloque'] ?? false);
        $bloque  = $user['bloque'];
        $updated = true;
        break;
    }
}
unset($user);

if (!$updated) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable.']);
    exit();
}

ecrire_users($users);

$admin_id = $_SESSION['user']['id'];
if ($bloque) {
    ajouter_log('blocage_compte', "Compte bloqué (user_id=$user_id) par admin_id=$admin_id", $admin_id);
} else {
    ajouter_log('deblocage_compte', "Compte débloqué (user_id=$user_id) par admin_id=$admin_id", $admin_id);
}

// Si l'utilisateur est bloqué : noter son ID dans un fichier pour invalider sa session
// (mécanisme simple : à chaque page PHP on vérifie ce fichier)
$revoked_file = __DIR__ . '/../../data/sessions_revoquees.json';
$revoked = [];
if (file_exists($revoked_file)) {
    $revoked = json_decode(file_get_contents($revoked_file), true) ?? [];
}

if ($bloque) {
    if (!in_array($user_id, $revoked)) {
        $revoked[] = $user_id;
    }
} else {
    $revoked = array_values(array_filter($revoked, fn($id) => $id !== $user_id));
}

file_put_contents($revoked_file, json_encode($revoked, JSON_PRETTY_PRINT));

echo json_encode([
    'success' => true,
    'bloque'  => $bloque,
    'message' => $bloque
        ? 'L\'utilisateur a été bloqué. Sa session sera terminée.'
        : 'L\'utilisateur a été débloqué.',
]);
