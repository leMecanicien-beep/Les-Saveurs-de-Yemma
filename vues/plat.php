<?php
session_start();
$plats = json_decode(file_get_contents(__DIR__ . '/../data/plats.json'), true);

// Filtrage par catégorie
$categorie = isset($_GET['categorie']) ? $_GET['categorie'] : 'Tous';
$recherche = isset($_GET['recherche']) ? strtolower($_GET['recherche']) : '';

$plats_filtres = array_filter($plats, function($plat) use ($categorie, $recherche) {
    $match_cat = ($categorie === 'Tous' || $plat['categorie'] === $categorie);
    $match_rech = ($recherche === '' || strpos(strtolower($plat['nom']), $recherche) !== false);
    return $match_cat && $match_rech;
});
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nos plats - Les Saveurs de Yemma</title>
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
                <input type="text" name="recherche" placeholder="Rechercher un plat..." value="<?php echo htmlspecialchars($recherche); ?>">
            </form>
        </div>
    </div>
</header>
<main>
    <h2>Un plat en particulier ...</h2>

    <div class="filtres">
        <a href="?categorie=Tous"><button <?php echo $categorie==='Tous'?'style="background:#7b2cbf;color:white;"':''; ?>>Tous</button></a>
        <a href="?categorie=Traditionnel"><button <?php echo $categorie==='Traditionnel'?'style="background:#7b2cbf;color:white;"':''; ?>>Traditionnels</button></a>
        <a href="?categorie=Entrée"><button <?php echo $categorie==='Entrée'?'style="background:#7b2cbf;color:white;"':''; ?>>Entrées</button></a>
        <a href="?categorie=Grillade"><button <?php echo $categorie==='Grillade'?'style="background:#7b2cbf;color:white;"':''; ?>>Grillades</button></a>
        <a href="?categorie=Dessert"><button <?php echo $categorie==='Dessert'?'style="background:#7b2cbf;color:white;"':''; ?>>Desserts</button></a>
        <a href="?categorie=Boisson"><button <?php echo $categorie==='Boisson'?'style="background:#7b2cbf;color:white;"':''; ?>>Boissons</button></a>
    </div>

    <section class="plats">
        <?php foreach ($plats_filtres as $plat): ?>
        <div class="aliment">
            <h3><?php echo $plat['nom']; ?></h3>
            <p><?php echo $plat['description']; ?></p>
            <p><strong><?php echo $plat['prix']; ?>€</strong></p>
            <?php if (!empty($plat['allergenes'])): ?>
                <p style="color:#999;font-size:12px;">Allergènes : <?php echo implode(', ', $plat['allergenes']); ?></p>
            <?php endif; ?>
            <?php if (isset($_SESSION['user'])): ?>
                <a href="panier.php?ajouter=<?php echo $plat['id']; ?>" 
                   style="display:inline-block;margin-top:8px;padding:6px 12px;background:#7b2cbf;color:white;border-radius:6px;text-decoration:none;">
                    Ajouter au panier
                </a>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </section>
</main>
</body>
</html>
