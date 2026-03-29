<?php
session_start();
require_once __DIR__ . '/../lib/users.php';

if (!isset($_SESSION['user'])) {
    header('Location: connexion.php');
    exit();
}

$plats = json_decode(file_get_contents(__DIR__ . '/../data/plats.json'), true);

if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Ajouter un plat
if (isset($_GET['ajouter'])) {
    $id = intval($_GET['ajouter']);
    if (isset($_SESSION['panier'][$id])) {
        $_SESSION['panier'][$id]++;
    } else {
        $_SESSION['panier'][$id] = 1;
    }
    header('Location: panier.php');
    exit();
}

// Diminuer la quantité d'un plat
if (isset($_GET['diminuer'])) {
    $id = intval($_GET['diminuer']);
    if (isset($_SESSION['panier'][$id])) {
        $_SESSION['panier'][$id]--;
        if ($_SESSION['panier'][$id] <= 0) {
            unset($_SESSION['panier'][$id]);
        }
    }
    header('Location: panier.php');
    exit();
}

// Supprimer un plat
if (isset($_GET['supprimer'])) {
    $id = intval($_GET['supprimer']);
    unset($_SESSION['panier'][$id]);
    header('Location: panier.php');
    exit();
}

// Calculer le total
$total = 0;
foreach ($_SESSION['panier'] as $id => $quantite) {
    foreach ($plats as $plat) {
        if ($plat['id'] === $id) {
            $total += $plat['prix'] * $quantite;
        }
    }
}
$total = round($total, 2);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Panier - Les Saveurs de Yemma</title>
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
                <li><a href="plat.php">CARTE</a></li>
                <li><a href="profil.php">PROFIL</a></li>
                <li><a href="deconnexion.php">DÉCONNEXION</a></li>
            </ul>
        </nav>
        <div class="barre">
            <input type="text" placeholder="Rechercher un plat...">
        </div>
    </div>
</header>
<main>
    <section class="profil">
        <h2>Mon panier</h2>

        <?php if (empty($_SESSION['panier'])): ?>
            <p>Votre panier est vide. <a href="plat.php">Voir la carte</a></p>
        <?php else: ?>
            <div style="width:65%;margin:0 auto;">

                <?php foreach ($_SESSION['panier'] as $id => $quantite): ?>
                    <?php foreach ($plats as $plat): ?>
                        <?php if ($plat['id'] === $id): ?>
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:15px;background:white;border:1px solid #ddd;border-radius:8px;margin-bottom:10px;">
                            <div>
                                <strong><?php echo $plat['nom']; ?></strong>
                                <p style="margin:4px 0;color:#666;"><?php echo $plat['prix']; ?>€ l'unité</p>
                            </div>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <a href="?diminuer=<?php echo $id; ?>" style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;background:#ddd;border-radius:50%;text-decoration:none;font-size:18px;font-weight:bold;color:#333;">−</a>
                                <span style="font-size:16px;font-weight:bold;min-width:20px;text-align:center;"><?php echo $quantite; ?></span>
                                <a href="?ajouter=<?php echo $id; ?>" style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;background:#7b2cbf;border-radius:50%;text-decoration:none;font-size:18px;font-weight:bold;color:white;">+</a>
                                <strong style="min-width:60px;text-align:right;"><?php echo round($plat['prix'] * $quantite, 2); ?>€</strong>
                                <a href="?supprimer=<?php echo $id; ?>" style="color:red;font-size:18px;text-decoration:none;">✕</a>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>

                <div style="text-align:right;padding:20px;font-size:20px;">
                    <strong>Total : <?php echo $total; ?>€</strong>
                </div>

                <form action="paiement.php" method="post">
                    <input type="hidden" name="total" value="<?php echo $total; ?>">
                    <input type="hidden" name="panier" value="<?php echo htmlspecialchars(json_encode($_SESSION['panier'])); ?>">

                    <div style="margin-bottom:15px;">
                        <label><strong>Type de commande :</strong></label><br>
                        <select name="type" id="type_commande" style="padding:8px;margin-top:8px;border-radius:6px;border:1px solid #ccc;" onchange="toggleHeureSouhaitee()">
                            <option value="livraison">Livraison à domicile</option>
                            <option value="emporter">À emporter</option>
                            <option value="sur_place">Sur place</option>
                        </select>
                    </div>

                    <div style="margin-bottom:15px;">
                        <label><strong>Quand souhaitez-vous être servi ?</strong></label><br>
                        <div style="display:flex;gap:12px;margin-top:8px;align-items:center;">
                            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                                <input type="radio" name="quand" value="maintenant" checked onchange="toggleHeureSouhaitee()">
                                Dès que possible
                            </label>
                            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                                <input type="radio" name="quand" value="plus_tard" onchange="toggleHeureSouhaitee()">
                                Programmer pour plus tard
                            </label>
                        </div>
                    </div>

                    <div id="heure_souhaitee_bloc" style="margin-bottom:15px;display:none;">
                        <label><strong>Date et heure souhaitées :</strong></label><br>
                        <input type="datetime-local" name="heure_souhaitee"
                               min="<?php echo date('Y-m-d\TH:i', strtotime('+30 minutes')); ?>"
                               style="padding:8px;margin-top:8px;border-radius:6px;border:1px solid #ccc;">
                        <p style="font-size:0.85em;color:#888;margin-top:4px;">
                            La commande ne sera pas préparée avant cette heure.
                        </p>
                    </div>

                    <button type="submit" style="width:100%;padding:15px;background:#7b2cbf;color:white;border:none;border-radius:8px;font-size:16px;cursor:pointer;">
                        Valider et payer
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </section>
</main>
<script>
function toggleHeureSouhaitee() {
    var plusTard = document.querySelector('input[name="quand"][value="plus_tard"]').checked;
    document.getElementById('heure_souhaitee_bloc').style.display = plusTard ? 'block' : 'none';
}
</script>
</body>
</html>
