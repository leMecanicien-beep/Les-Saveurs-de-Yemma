<?php
session_start();
require_once __DIR__ . '/lib/users.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Les saveurs de Yemma</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
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
                    <li>
                        <?php if (isset($_SESSION['user'])): ?>
                            <a href="vues/profil.php">PROFIL</a>
                        <?php else: ?>
                            <a href="vues/connexion.php">CONNEXION</a>
                        <?php endif; ?>
                    </li>
                    <li class="carte">
                        <a href="#">CARTE</a>
                        <ul class="deroulant">
                            <li><a href="#">FORMULES</a></li>
                            <li><a href="#">TRADITION DU JOUR</a></li>
                            <li><a href="#">OFFRES</a></li>
                        </ul>
                    </li>
                    <li><a href="vues/panier.php">COMMANDE</a></li>
                    <li><a href="#">HORAIRES</a></li>
                    <li class="propos">
                        <a href="#">A PROPOS</a>
                        <ul class="deroulant2">
                            <li><a href="#">RESTAURANT</a></li>
                            <li><a href="#">PARTENAIRES</a></li>
                        </ul>
                    </li>
                    <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                        <li><a href="vues/admin.php">ADMIN</a></li>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['user'])): ?>
                        <li><a href="vues/deconnexion.php">DÉCONNEXION</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="barre">
                <input type="text" placeholder="Rechercher un plat...">
            </div>
        </div>
    </header>
    <main>
        <section class="presentation">
            <div class="image">
                <img src="assets/images/couscous.jpg" alt="Plat traditionnel algérien">
            </div>
            <div class="texte">
                <?php if (isset($_SESSION['user'])): ?>
                    <p>Bienvenue <strong><?php echo $_SESSION['user']['prenom']; ?></strong> !</p>
                <?php endif; ?>
                <p>
                    Aux saveurs de Yemma, nous ne servons pas simplement des plats, 
                    nous partageons une tradition. Ici, chaque recette raconte une histoire, 
                    celle des cuisines algériennes où la maman règne avec amour et savoir-faire.
                </p>
                <p>
                    Nos plats sont préparés comme à la maison, avec des ingrédients frais, 
                    des épices soigneusement choisies et ce petit secret que seule "Yemma" maîtrise : 
                    la générosité.
                </p>
            </div>
        </section>
    </main>
</body>
</html>
