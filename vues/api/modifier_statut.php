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
$statut  = $body['statut'] ?? '';

$statuts_valides = ['Standard', 'VIP', 'Premium'];
if (!$user_id || !in_array($statut, $statuts_valides)) {
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
        $ancien = $user['statut'] ?? 'Standard';
        $user['statut'] = $statut;
        // Appliquer le taux de remise selon le statut
        $user['taux_remise'] = match($statut) {
            'VIP'     => 10,
            'Premium' => 20,
            default   => 0,
        };
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
ajouter_log('modification_statut', "Statut user_id=$user_id changé en $statut par admin_id=" . $_SESSION['user']['id'], $_SESSION['user']['id']);

echo json_encode(['success' => true, 'statut' => $statut]);
