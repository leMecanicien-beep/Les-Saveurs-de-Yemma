<?php
session_start();
require_once __DIR__ . '/../lib/users.php';
require_once __DIR__ . '/../lib/getapikey.php';

if (!isset($_SESSION['user'])) {
    header('Location: connexion.php');
    exit();
}

$user = $_SESSION['user'];
$vendeur = $_GET['vendeur'] ?? '';
$transaction = $_GET['transaction'] ?? '';
$montant = $_GET['montant'] ?? '';
$statut = $_GET['status'] ?? '';
$control_recu = $_GET['control'] ?? '';
$type            = $_GET['type'] ?? 'livraison';
$panier          = json_decode(urldecode($_GET['panier'] ?? '[]'), true);
$heure_souhaitee = urldecode($_GET['heure'] ?? '') ?: null;

$api_key = getAPIKey($vendeur);
$control_calcule = md5($api_key . '#' . $transaction . '#' . $montant . '#' . $vendeur . '#' . $statut . '#');
$paiement_valide = ($control_recu === $control_calcule && $statut === 'accepted');

if ($paiement_valide) {
    $commandes = json_decode(file_get_contents(__DIR__ . '/../data/commandes.json'), true);
    $nouvel_id = max(array_column($commandes, 'id')) + 1;
    $plats_ids = array_map('intval', array_keys($panier));
    $quantites = [];
    foreach ($panier as $pid => $qte) {
        $quantites[(string)intval($pid)] = intval($qte);
    }
    $statut_initial = $heure_souhaitee ? 'en_attente' : 'en_preparation';
    $nouvelle_commande = [
        "id"              => $nouvel_id,
        "user_id"         => $user['id'],
        "plats_ids"       => $plats_ids,
        "menus_ids"       => [],
        "quantites"       => $quantites,
        "montant"         => floatval($montant),
        "statut"          => $statut_initial,
        "type"            => $type,
        "date"            => date("Y-m-d H:i:s"),
        "heure_souhaitee" => $heure_souhaitee,
        "adresse_livraison" => $user['adresse'],
        "livreur_id"      => null,
        "note"            => null
    ];
    $commandes[] = $nouvelle_commande;
    file_put_contents(
        __DIR__ . '/../data/commandes.json',
        json_encode($commandes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
    $_SESSION['panier'] = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Retour paiement - Les Saveurs de Yemma</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<header>
    <div class="cadre">
        <div class="enseigne">
            <p>Les saveurs</p>
            <p>de</p>
            <p>Yemma</p>
        </div>
        <nav>
            <ul>
                <li><a href="../index.php">ACCUEIL</a></li>
                <li><a href="profil.php">MES COMMANDES</a></li>
                <li><a href="deconnexion.php">DÉCONNEXION</a></li>
            </ul>
        </nav>
    </div>
</header>
<main class="main-centered">
    <div class="card">
        <?php if ($paiement_valide): ?>
            <h2 class="text-success">Paiement accepté !</h2>
            <p>Votre commande a bien été enregistrée.</p>
            <p><strong>Montant :</strong> <?php echo $montant; ?>€</p>
            <p><strong>Transaction :</strong> <?php echo $transaction; ?></p>
            <br>
            <a href="profil.php" class="btn">Voir mes commandes</a>
        <?php else: ?>
            <h2 class="text-danger">Paiement refusé</h2>
            <p>Le paiement n'a pas pu être effectué.</p>
            <br>
            <a href="panier.php" class="btn">Retour au panier</a>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
