<?php

session_start();
require_once __DIR__ . '/../../lib/users.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'restaurateur') {
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
$new_statut  = trim($body['statut'] ?? '');

$statuts_valides = ['en_attente', 'en_preparation', 'prete', 'en_livraison', 'livree', 'abandonnee'];
if (!$commande_id || !in_array($new_statut, $statuts_valides)) {
    echo json_encode(['success' => false, 'message' => 'Données invalides.']);
    exit();
}

$commandes = json_decode(file_get_contents(__DIR__ . '/../../data/commandes.json'), true);
$updated   = false;

foreach ($commandes as &$c) {
    if ($c['id'] === $commande_id) {
        $c['statut'] = $new_statut;
        $updated     = true;
        break;
    }
}
unset($c);

if (!$updated) {
    echo json_encode(['success' => false, 'message' => 'Commande introuvable.']);
    exit();
}

file_put_contents(
    __DIR__ . '/../../data/commandes.json',
    json_encode($commandes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

$labels = [
    'en_attente'     => 'En attente',
    'en_preparation' => 'En préparation',
    'prete'          => 'Prête',
    'en_livraison'   => 'En livraison',
    'livree'         => 'Livrée',
    'abandonnee'     => 'Abandonnée',
];

echo json_encode([
    'success' => true,
    'message' => 'Statut mis à jour : ' . ($labels[$new_statut] ?? $new_statut),
    'statut'  => $new_statut,
    'label'   => $labels[$new_statut] ?? $new_statut,
]);
