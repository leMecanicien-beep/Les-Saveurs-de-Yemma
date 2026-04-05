<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Offres - Les Saveurs de Yemma</title>
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
        <h2>Nos offres spéciales</h2>
        <p>Des promotions pensées pour vous faire découvrir le meilleur de notre cuisine à petits prix.</p>
    </div>

    <section class="plats">

        <div class="aliment offre-card">
            <span class="badge-offre">Midi uniquement</span>
            <h3>Menu étudiant</h3>
            <p>1 plat du jour + 1 boisson au choix + 1 café. Du lundi au vendredi.</p>
            <span class="prix">9,90 €</span>
        </div>

        <div class="aliment offre-card">
            <span class="badge-offre">Découverte</span>
            <h3>Plateau découverte</h3>
            <p>Assortiment de 3 entrées (briques, samosa, chorba) pour découvrir nos spécialités.</p>
            <span class="prix">14,90 €</span>
        </div>

        <div class="aliment offre-card">
            <span class="badge-offre">Pour deux</span>
            <h3>Duo Yemma</h3>
            <p>2 plats traditionnels au choix + 2 boissons. Idéal pour partager un bon moment.</p>
            <span class="prix">22,00 €</span>
        </div>

        <div class="aliment offre-card">
            <span class="badge-offre">Tous les vendredis</span>
            <h3>Vendredi grillades</h3>
            <p>Brochette bœuf ou agneau + salade maison + pain maison.</p>
            <span class="prix">11,50 €</span>
        </div>

        <div class="aliment offre-card">
            <span class="badge-offre">En famille</span>
            <h3>Pack famille</h3>
            <p>1 grand couscous + 4 briques maison + 4 boissons. Pour régaler toute la famille !</p>
            <span class="prix">38,00 €</span>
        </div>

        <div class="aliment offre-card">
            <span class="badge-offre">15h – 17h</span>
            <h3>Happy hour</h3>
            <p>Thé à la menthe + pâtisseries orientales (baklava, cornes de gazelle).</p>
            <span class="prix">4,50 €</span>
        </div>

    </section>
</main>

</body>
</html>
