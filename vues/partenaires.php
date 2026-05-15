<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Partenaires - Les Saveurs de Yemma</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/plat.css">
    <link rel="stylesheet" href="../assets/accueil.css">
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
            <input type="text" placeholder="Rechercher un plat...">
        </div>
    </div>
</header>

<main>
    <div class="page-banner">
        <h2>Nos partenaires</h2>
        <p>Des acteurs locaux qui partagent nos valeurs de qualité et d'authenticité.</p>
    </div>

    <section class="plats">

        <div class="aliment">
            <h3>Épicerie El Baraka</h3>
            <p>Fournisseur d'épices et de produits orientaux sélectionnés avec soin.</p>
        </div>

        <div class="aliment">
            <h3>Boulangerie Maison Kader</h3>
            <p>Pains et galettes artisanales préparées chaque matin pour notre restaurant.</p>
        </div>

        <div class="aliment">
            <h3>Marché de Cergy</h3>
            <p>Légumes frais et de saison, approvisionnement trois fois par semaine.</p>
        </div>

    </section>
</main>
<img src="../assets/images/tahia.png" class="tahia" alt="Décoration">
</body>
</html>
