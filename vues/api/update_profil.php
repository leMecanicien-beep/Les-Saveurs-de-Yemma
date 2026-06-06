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

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    // Essayer $_POST
    $data = $_POST;
}

$nom             = trim($data['nom'] ?? '');
$prenom          = trim($data['prenom'] ?? '');
$telephone       = trim($data['telephone'] ?? '');
$adresse         = trim($data['adresse'] ?? '');
$code_interphone = trim($data['code_interphone'] ?? '');

// Validations serveur
if (strlen($nom) < 2) {
    echo json_encode(['success' => false, 'message' => 'Le nom doit contenir au moins 2 caractères.']);
    exit();
}
if (strlen($prenom) < 2) {
    echo json_encode(['success' => false, 'message' => 'Le prénom doit contenir au moins 2 caractères.']);
    exit();
}
if (!empty($telephone) && !preg_match('/^(\+33|0)[0-9](\s?\d{2}){4}$/', preg_replace('/[\s.-]/', '', $telephone))) {
    echo json_encode(['success' => false, 'message' => 'Numéro de téléphone invalide.']);
    exit();
}

// Mise à jour dans le fichier JSON
$users = lire_users();
$updated = false;
foreach ($users as &$user) {
    if ($user['id'] === $_SESSION['user']['id']) {
        $user['nom']             = htmlspecialchars($nom, ENT_QUOTES, 'UTF-8');
        $user['prenom']          = htmlspecialchars($prenom, ENT_QUOTES, 'UTF-8');
        if (!empty($telephone)) {
            $user['telephone'] = htmlspecialchars($telephone, ENT_QUOTES, 'UTF-8');
        }
        $user['adresse']         = htmlspecialchars($adresse, ENT_QUOTES, 'UTF-8');
        $user['code_interphone'] = htmlspecialchars($code_interphone, ENT_QUOTES, 'UTF-8');

        // Mettre à jour la session
        $_SESSION['user'] = $user;
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

echo json_encode([
    'success' => true,
    'message' => 'Profil mis à jour avec succès.',
    'user'    => [
        'nom'             => $_SESSION['user']['nom'],
        'prenom'          => $_SESSION['user']['prenom'],
        'telephone'       => $_SESSION['user']['telephone'],
        'adresse'         => $_SESSION['user']['adresse'],
        'code_interphone' => $_SESSION['user']['code_interphone'],
    ]
]);
