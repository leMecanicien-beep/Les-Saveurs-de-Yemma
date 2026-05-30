<?php
session_start();
require_once __DIR__ . '/../../lib/users.php';

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

$body        = json_decode(file_get_contents('php://input'), true);
$user_id     = intval($body['user_id'] ?? 0);
$taux_remise = intval($body['taux_remise'] ?? 0);

if (!$user_id || $taux_remise < 0 || $taux_remise > 100) {
    echo json_encode(['success' => false, 'message' => 'Paramètres invalides.']);
    exit();
}

$users   = lire_users();
$updated = false;

foreach ($users as &$user) {
    if ($user['id'] === $user_id) {
        if ($user['role'] === 'admin') {
            echo json_encode(['success' => false, 'message' => 'Impossible de modifier un administrateur.']);
            exit();
        }
        $user['taux_remise'] = $taux_remise;
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
echo json_encode(['success' => true, 'taux_remise' => $taux_remise]);
