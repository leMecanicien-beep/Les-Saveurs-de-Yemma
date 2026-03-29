<?php
session_start();
require_once __DIR__ . '/../lib/users.php';

if (!isset($_SESSION['user'])) {
    header('Location: connexion.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: panier.php');
    exit();
}

$user = $_SESSION['user'];
$total = $_POST['total'];
$type  = $_POST['type'];
$panier = $_POST['panier'];
$quand  = $_POST['quand'] ?? 'maintenant';
$heure_souhaitee = ($quand === 'plus_tard' && !empty($_POST['heure_souhaitee']))
    ? $_POST['heure_souhaitee']
    : '';

// Générer un identifiant de transaction unique
$transaction = strtoupper(substr(md5(uniqid()), 0, 12));

// Vendeur
$vendeur = 'SUPMECA_A';

// URL de retour
$retour = 'http://127.0.0.1:8000/vues/retour_paiement.php?type=' . $type
        . '&panier=' . urlencode($panier)
        . '&heure=' . urlencode($heure_souhaitee);

// Calcul du control
require_once __DIR__ . '/../lib/getapikey.php';
$api_key = getAPIKey($vendeur);
$control = md5($api_key . '#' . $transaction . '#' . $total . '#' . $vendeur . '#' . $retour . '#');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paiement - Les Saveurs de Yemma</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/choix_connexion_inscription.css">
</head>
<body>
<div class="card">
    <h2>Récapitulatif</h2>
    <p><strong>Total :</strong> <?php echo $total; ?>€</p>
    <p><strong>Type :</strong> <?php echo $type; ?></p>
    <?php if ($heure_souhaitee): ?>
    <p><strong>Livraison souhaitée :</strong> <?php echo htmlspecialchars($heure_souhaitee); ?></p>
    <?php else: ?>
    <p><strong>Préparation :</strong> Dès que possible</p>
    <?php endif; ?>
    <br>
    <form action='https://www.plateforme-smc.fr/cybank/index.php' method='POST'>
        <input type='hidden' name='transaction' value='<?php echo $transaction; ?>'>
        <input type='hidden' name='montant' value='<?php echo $total; ?>'>
        <input type='hidden' name='vendeur' value='<?php echo $vendeur; ?>'>
        <input type='hidden' name='retour' value='<?php echo $retour; ?>'>
        <input type='hidden' name='control' value='<?php echo $control; ?>'>
        <button type='submit' class='btn'>Procéder au paiement</button>
    </form>
    <p><a href="panier.php">Retour au panier</a></p>
</div>
</body>
</html>
