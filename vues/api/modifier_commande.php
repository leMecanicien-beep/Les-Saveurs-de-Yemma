<?php

session_start();
require_once __DIR__ . '/../../lib/users.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non authentifié.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit();
}

$body = json_decode(file_get_contents('php://input'), true);
$commande_id = intval($body['commande_id'] ?? 0);
$nouvelles_qtes = $body['quantites'] ?? [];

if (!$commande_id || empty($nouvelles_qtes)) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes.']);
    exit();
}

$commandes = json_decode(file_get_contents(__DIR__ . '/../../data/commandes.json'), true);
$plats     = json_decode(file_get_contents(__DIR__ . '/../../data/plats.json'), true);

// Trouver la commande
$idx_cmd = null;
foreach ($commandes as $i => $c) {
    if ($c['id'] === $commande_id && $c['user_id'] === $_SESSION['user']['id']) {
        $idx_cmd = $i;
        break;
    }
}

if ($idx_cmd === null) {
    echo json_encode(['success' => false, 'message' => 'Commande introuvable.']);
    exit();
}

$commande = $commandes[$idx_cmd];

// Vérifier que la commande est en_attente (pas encore démarrée)
if ($commande['statut'] !== 'en_attente') {
    echo json_encode(['success' => false, 'message' => 'La commande est déjà en préparation ou terminée. Modification impossible.']);
    exit();
}

// Helper pour trouver le prix d'un plat
function prix_plat($plats, $id) {
    foreach ($plats as $p) {
        if ($p['id'] === intval($id)) return $p['prix'];
    }
    return 0;
}

// Calculer l'ancien montant
$ancien_montant = floatval($commande['montant']);

// Construire les nouvelles quantités (supprimer les 0)
$new_quantites = [];
$new_plats_ids = [];
$new_montant   = 0.0;

foreach ($nouvelles_qtes as $plat_id => $qte) {
    $qte = intval($qte);
    if ($qte > 0) {
        $new_quantites[(string)$plat_id] = $qte;
        $new_plats_ids[] = intval($plat_id);
        $new_montant += prix_plat($plats, $plat_id) * $qte;
    }
}

if (empty($new_plats_ids)) {
    echo json_encode(['success' => false, 'message' => 'La commande ne peut pas être vide.']);
    exit();
}

$new_montant = round($new_montant, 2);
$difference  = round($new_montant - $ancien_montant, 2);

// Mettre à jour la commande
$commandes[$idx_cmd]['quantites']  = $new_quantites;
$commandes[$idx_cmd]['plats_ids']  = $new_plats_ids;
$commandes[$idx_cmd]['montant']    = $new_montant;

// Si la commande est moins chère : crédit "bon de réduction" (ajout de points fidélité)
$bon_reduction = null;
if ($difference < 0) {
    // Convertir la différence en points (1 point = 0.10€)
    $points_bonus = intval(abs($difference) * 10);
    // Ajouter les points à l'utilisateur
    $users = lire_users();
    foreach ($users as &$u) {
        if ($u['id'] === $_SESSION['user']['id']) {
            $u['points_fidelite'] = ($u['points_fidelite'] ?? 0) + $points_bonus;
            $_SESSION['user'] = $u;
            break;
        }
    }
    unset($u);
    ecrire_users($users);
    $bon_reduction = $points_bonus;
}

file_put_contents(
    __DIR__ . '/../../data/commandes.json',
    json_encode($commandes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

$response = [
    'success'         => true,
    'nouveau_montant' => $new_montant,
    'difference'      => $difference,
    'message'         => 'Commande modifiée avec succès.'
];

if ($difference > 0) {
    $response['paiement_supplementaire'] = $difference;
    $response['message'] = 'Commande modifiée. Un paiement supplémentaire de ' . number_format($difference, 2) . '€ est requis.';
} elseif ($difference < 0 && $bon_reduction !== null) {
    $response['bon_reduction'] = $bon_reduction;
    $response['message'] = 'Commande modifiée. ' . $bon_reduction . ' points de fidélité ont été crédités.';
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
