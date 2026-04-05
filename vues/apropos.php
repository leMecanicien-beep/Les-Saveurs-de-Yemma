<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>À propos - Les Saveurs de Yemma</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
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
        <h2>Notre restaurant</h2>
        <p>L'histoire derrière Les Saveurs de Yemma.</p>
    </div>

    <section class="presentation">
        <div class="image">
            <img src="../assets/images/couscous.jpg" alt="Les Saveurs de Yemma">
        </div>
        <div class="texte">
            <p>
                Les Saveurs de Yemma est né d'un amour profond pour la cuisine algérienne
                et du désir de la partager avec le plus grand nombre. Yemma, c'est la maman
                en arabe — celle qui cuisine avec amour, patience et générosité.
            </p>
            <p>
                Ici, chaque plat est préparé selon des recettes familiales transmises de
                génération en génération. Pas de compromis sur la qualité : des ingrédients
                frais, des épices soigneusement sélectionnées, et une cuisine faite maison
                chaque jour.
            </p>
            <p>
                Que vous veniez seul, en famille ou entre amis, vous trouverez ici la chaleur
                d'un repas partagé et le goût authentique de l'Algérie.
            </p>
        </div>
    </section>
</main>
<img src="../assets/images/tahia.png" class="tahia" alt="Décoration">
</body>
</html>
