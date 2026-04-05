<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tradition du jour - Les Saveurs de Yemma</title>
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
            <input type="text" placeholder="Rechercher un plat...">
        </div>
    </div>
</header>

<main>
    <div class="page-banner">
        <h2>Tradition du jour</h2>
        <p>Chaque jour, Yemma vous prépare un plat traditionnel algérien fait maison.</p>
    </div>

    <section class="plats">

        <div class="aliment">
            <span class="badge-jour">Lundi</span>
            <h3>Berkoukes</h3>
            <p>Petites boulettes de semoule, bouillon de légumes, viande d'agneau, épices douces</p>
            <span class="prix">12,00 €</span>
        </div>

        <div class="aliment">
            <span class="badge-jour">Mardi</span>
            <h3>Trida</h3>
            <p>Feuilles de pâte maison, poulet en sauce blanche, pois chiches, beurre smen</p>
            <span class="prix">12,00 €</span>
        </div>

        <div class="aliment">
            <span class="badge-jour">Mercredi</span>
            <h3>Couscous royal</h3>
            <p>Semoule fine, merguez, agneau, poulet, légumes mijotés, sauce piquante maison</p>
            <span class="prix">14,00 €</span>
        </div>

        <div class="aliment">
            <span class="badge-jour">Jeudi</span>
            <h3>Rechta</h3>
            <p>Pâtes plates faites maison, poulet, navets, pois chiches, bouillon parfumé à la cannelle</p>
            <span class="prix">12,50 €</span>
        </div>

        <div class="aliment">
            <span class="badge-jour">Vendredi</span>
            <h3>Chakhchoukha</h3>
            <p>Galettes de pain émiettées, ragoût d'agneau, pois chiches, tomates, ras el hanout</p>
            <span class="prix">13,00 €</span>
        </div>

        <div class="aliment">
            <span class="badge-jour">Samedi</span>
            <h3>Marka hamra</h3>
            <p>Ragoût rouge de bœuf, pommes de terre, tomates fraîches, harissa douce, coriandre</p>
            <span class="prix">13,50 €</span>
        </div>

    </section>
</main>

</body>
</html>
