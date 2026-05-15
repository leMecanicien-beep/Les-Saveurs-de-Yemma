<?php
session_start();
require_once __DIR__ . '/../lib/users.php';
verifier_session_revoquee();

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
            <div class="panier-wrapper">

                <?php foreach ($_SESSION['panier'] as $id => $quantite): ?>
                    <?php foreach ($plats as $plat): ?>
                        <?php if ($plat['id'] === $id): ?>
                        <div class="panier-item">
                            <div>
                                <strong><?php echo $plat['nom']; ?></strong>
                                <p class="prix-unite"><?php echo $plat['prix']; ?>€ l'unité</p>
                            </div>
                            <div class="panier-item-actions">
                                <a href="?diminuer=<?php echo $id; ?>" class="btn-diminuer">−</a>
                                <span class="panier-quantite"><?php echo $quantite; ?></span>
                                <a href="?ajouter=<?php echo $id; ?>" class="btn-augmenter">+</a>
                                <strong class="panier-item-prix"><?php echo round($plat['prix'] * $quantite, 2); ?>€</strong>
                                <a href="?supprimer=<?php echo $id; ?>" class="btn-supprimer-item">✕</a>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>

                <div class="panier-total">
                    <strong>Total : <?php echo $total; ?>€</strong>
                </div>

                <form action="paiement.php" method="post">
                    <input type="hidden" name="total" value="<?php echo $total; ?>">
                    <input type="hidden" name="panier" value="<?php echo htmlspecialchars(json_encode($_SESSION['panier'])); ?>">

                    <div class="form-group">
                        <label><strong>Type de commande :</strong></label><br>
                        <select name="type" class="select-commande">
                            <option value="livraison">Livraison à domicile</option>
                            <option value="emporter">À emporter</option>
                            <option value="sur_place">Sur place</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><strong>Date et heure souhaitées (optionnel) :</strong></label><br>
                        <input type="datetime-local" name="heure_souhaitee"
                               min="<?php echo date('Y-m-d\TH:i', strtotime('+30 minutes')); ?>"
                               class="input-datetime">
                        <p class="hint-text">
                            Laisser vide pour une préparation dès que possible.
                        </p>
                    </div>

                    <button type="submit" class="btn-valider-panier">
                        Valider et payer
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </section>
</main>
</script>
</body>
</html>
