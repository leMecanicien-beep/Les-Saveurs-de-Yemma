<?php
session_start();
require_once __DIR__ . '/../lib/users.php';
verifier_session_revoquee();

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
$heure_souhaitee = !empty($_POST['heure_souhaitee']) ? $_POST['heure_souhaitee'] : '';

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
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<div style="position:fixed;top:16px;right:16px;z-index:100;">
    <button id="btn-theme" title="Changer le thème"
        style="cursor:pointer;border-radius:20px;padding:6px 14px;font-size:13px;border:2px solid #b98acb;background:#b98acb;color:#fff;">
        🌕 Mode sombre
    </button>
</div>

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
<script src="../assets/js/theme.js"></script>
</body>
</html>
