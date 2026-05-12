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
$livreur_id  = intval($body['livreur_id'] ?? 0);

if (!$commande_id || !$livreur_id) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes.']);
    exit();
}

// Vérifier que le livreur existe
$users   = lire_users();
$livreur = null;
foreach ($users as $u) {
    if ($u['id'] === $livreur_id && $u['role'] === 'livreur') {
        $livreur = $u;
        break;
    }
}
if (!$livreur) {
    echo json_encode(['success' => false, 'message' => 'Livreur introuvable.']);
    exit();
}

// Mettre à jour la commande
$commandes = json_decode(file_get_contents(__DIR__ . '/../../data/commandes.json'), true);
$updated   = false;

foreach ($commandes as &$c) {
    if ($c['id'] === $commande_id) {
        $c['livreur_id'] = $livreur_id;
        // Passer automatiquement en "en_livraison" si la commande est prête
        if ($c['statut'] === 'prete' && $c['type'] === 'livraison') {
            $c['statut'] = 'en_livraison';
        }
        $updated = true;
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

echo json_encode([
    'success'     => true,
    'message'     => 'Livreur attribué : ' . $livreur['prenom'] . ' ' . $livreur['nom'],
    'livreur_nom' => $livreur['prenom'] . ' ' . $livreur['nom'],
    'livreur_id'  => $livreur_id,
]);
