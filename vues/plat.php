<?php
session_start();
$plats = json_decode(file_get_contents(__DIR__ . '/../data/plats.json'), true);

$recherche = isset($_GET['recherche']) ? strtolower($_GET['recherche']) : '';

$plats_filtres = array_filter($plats, function($plat) use ($recherche) {
    return $recherche === '' || strpos(strtolower($plat['nom']), $recherche) !== false;
});
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nos plats - Les Saveurs de Yemma</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/plat.css">
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
                <li class="carte">
                    <a href="plat.php">CARTE</a>
                    <ul class="deroulant">
                        <li><a href="plat.php">FORMULES</a></li>
                        <li><a href="tradition.php">TRADITION DU JOUR</a></li>
                        <li><a href="offre.php">OFFRES</a></li>
                    </ul>
                </li>
                <li><a href="panier.php">COMMANDE</a></li>
                <li><a href="horaires.php">HORAIRES</a></li>
                <li class="propos">
                    <a href="#">A PROPOS</a>
                    <ul class="deroulant2">
                        <li><a href="apropos.php">RESTAURANT</a></li>
                        <li><a href="partenaires.php">PARTENAIRES</a></li>
                    </ul>
                </li>
                <?php if (isset($_SESSION['user'])): ?>
                    <li><a href="profil.php">PROFIL</a></li>
                    <li><a href="deconnexion.php">DÉCONNEXION</a></li>
                <?php else: ?>
                    <li><a href="connexion.php">CONNEXION</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="barre">
            <form action="" method="get">
                <?php if ($categorie !== 'Tous'): ?>
                    <input type="hidden" name="categorie" value="<?php echo htmlspecialchars($categorie); ?>">
                <?php endif; ?>
                <input type="text" name="recherche" placeholder="Rechercher un plat..." value="<?php echo htmlspecialchars($recherche); ?>">
            </form>
        </div>
    </div>
</header>

<main>
    <div class="page-banner">
        <h2>Notre carte</h2>
        <p>Des plats préparés avec amour, comme à la maison.</p>
    </div>

    <section class="plats">
        <?php foreach ($plats_filtres as $plat): ?>
        <div class="aliment">
            <h3><?php echo htmlspecialchars($plat['nom']); ?></h3>
            <p><?php echo htmlspecialchars($plat['description']); ?></p>
            <?php if (!empty($plat['allergenes'])): ?>
                <p class="allergenes">Allergènes : <?php echo htmlspecialchars(implode(', ', $plat['allergenes'])); ?></p>
            <?php endif; ?>
            <span class="prix"><?php echo $plat['prix']; ?> €</span>
            <?php if (isset($_SESSION['user'])): ?>
                <a href="panier.php?ajouter=<?php echo $plat['id']; ?>" class="btn">Ajouter au panier</a>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </section>
</main>

</body>
</html>
