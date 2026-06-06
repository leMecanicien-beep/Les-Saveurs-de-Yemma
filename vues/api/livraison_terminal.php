<?php

session_start();
require_once __DIR__ . '/../../lib/users.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'livreur') {
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
$commande_id = intval($body['commande_id'] ?? 0);
$action      = trim($body['action'] ?? '');

if (!$commande_id || !in_array($action, ['livree', 'abandonnee'])) {
    echo json_encode(['success' => false, 'message' => 'Données invalides.']);
    exit();
}

$commandes = json_decode(file_get_contents(__DIR__ . '/../../data/commandes.json'), true);
$updated   = false;

foreach ($commandes as &$c) {
    if ($c['id'] === $commande_id
        && $c['livreur_id'] === $_SESSION['user']['id']
        && $c['statut'] === 'en_livraison') {
        $c['statut'] = $action;
        $updated     = true;
        break;
    }
}
unset($c);

if (!$updated) {
    echo json_encode(['success' => false, 'message' => 'Commande introuvable ou statut incorrect.']);
    exit();
}

file_put_contents(
    __DIR__ . '/../../data/commandes.json',
    json_encode($commandes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

$msg = $action === 'livree' ? 'Livraison confirmée !' : 'Livraison abandonnée.';
echo json_encode(['success' => true, 'message' => $msg, 'statut' => $action]);
