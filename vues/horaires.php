<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Horaires - Les Saveurs de Yemma</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/accueil.css">
    <link rel="stylesheet" href="../assets/admin.css">
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
            <button id="btn-theme" title="Changer le thème">
                🌕 Mode sombre
            </button>
            <input type="text" placeholder="Rechercher un plat...">
        </div>
    </div>
</header>

<main>
    <div class="page-banner">
        <h2>Nos horaires</h2>
        <p>Retrouvez-nous du lundi au samedi pour partager un repas fait maison.</p>
    </div>

    <section class="presentation">
        <div class="texte texte-full">
            <table class="table-admin table-horaires">
                <thead>
                    <tr>
                        <th>Jour</th>
                        <th>Midi</th>
                        <th>Soir</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>Lundi</td><td>12h00 – 14h30</td><td>19h00 – 22h30</td></tr>
                    <tr><td>Mardi</td><td>12h00 – 14h30</td><td>19h00 – 22h30</td></tr>
                    <tr><td>Mercredi</td><td>12h00 – 14h30</td><td>19h00 – 22h30</td></tr>
                    <tr><td>Jeudi</td><td>12h00 – 14h30</td><td>19h00 – 22h30</td></tr>
                    <tr><td>Vendredi</td><td>12h00 – 15h00</td><td>19h00 – 23h00</td></tr>
                    <tr><td>Samedi</td><td>12h00 – 15h00</td><td>19h00 – 23h00</td></tr>
                    <tr><td>Dimanche</td><td colspan="2" class="td-center">Fermé</td></tr>
                </tbody>
            </table>
        </div>
    </section>
</main>

<script src="../assets/js/theme.js"></script>
</body>
</html>
