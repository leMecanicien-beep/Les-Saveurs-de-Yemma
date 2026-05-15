<?php
session_start();
require_once __DIR__ . '/../lib/users.php';
verifier_session_revoquee();

// La page initiale garde une valeur de catégorie depuis GET (pour l'état par défaut)
$categorie = isset($_GET['categorie']) ? $_GET['categorie'] : 'Tous';
$recherche = isset($_GET['recherche']) ? $_GET['recherche'] : '';
$is_logged_in = isset($_SESSION['user']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nos plats - Les Saveurs de Yemma</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/plat.css">
    <style>
        .filtres-avances { padding: 16px 40px; background: #f4f4f4; border-bottom: 1px solid #ddd; }
        .filtres-avances h3 { margin-bottom: 10px; font-size: 15px; }
        .filtres-row { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; margin-bottom: 10px; }
        .filtres-row label { font-size: 14px; display: flex; align-items: center; gap: 5px; }
        .filtre-cat { cursor: pointer; padding: 6px 14px; border: 2px solid #b98acb; border-radius: 20px;
                      background: white; color: #b98acb; font-size: 13px; transition: all .2s; }
        .filtre-cat.actif, .filtre-cat:hover { background: #b98acb; color: white; }
        .select-tri { padding: 6px 12px; border: 2px solid #b98acb; border-radius: 20px; font-size: 13px; background: white; }
        #plats-msg { padding: 8px 40px; font-size: 14px; color: #666; }
        .aucun-plat { padding: 40px; text-align: center; color: #666; font-style: italic; }
        .plats-section-title { padding: 0 40px; margin: 16px 0 8px; }
    </style>
</head>
<body data-logged-in="<?php echo $is_logged_in ? '1' : '0'; ?>">

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
                <?php if ($is_logged_in): ?>
                    <li><a href="profil.php">PROFIL</a></li>
                    <li><a href="deconnexion.php">DÉCONNEXION</a></li>
                <?php else: ?>
                    <li><a href="connexion.php">CONNEXION</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="barre">
            <button id="btn-theme" title="Changer le thème"
                style="cursor:pointer;border-radius:20px;padding:6px 14px;font-size:13px;border:2px solid #fff;background:rgba(255,255,255,.15);color:#fff;margin-right:8px;vertical-align:middle;">
                🌕 Mode sombre
            </button>
            <form id="form-recherche" action="" method="get" style="display:inline;">
                <input id="input-recherche" type="text" name="recherche"
                       placeholder="Rechercher un plat..."
                       value="<?php echo htmlspecialchars($recherche); ?>">
            </form>
        </div>
    </div>
</header>

<!-- Filtres et tris -->
<div class="filtres-avances">
    <div class="filtres-row">
        <strong>Catégorie :</strong>
        <button class="filtre-cat <?php echo $categorie === 'Tous' ? 'actif' : ''; ?>" data-val="Tous">Tous</button>
        <button class="filtre-cat <?php echo $categorie === 'Entrée' ? 'actif' : ''; ?>" data-val="Entrée">Entrées</button>
        <button class="filtre-cat <?php echo $categorie === 'Traditionnel' ? 'actif' : ''; ?>" data-val="Traditionnel">Plats traditionnels</button>
        <button class="filtre-cat <?php echo $categorie === 'Grillade' ? 'actif' : ''; ?>" data-val="Grillade">Grillades</button>
        <button class="filtre-cat <?php echo $categorie === 'Dessert' ? 'actif' : ''; ?>" data-val="Dessert">Desserts</button>
        <button class="filtre-cat <?php echo $categorie === 'Boisson' ? 'actif' : ''; ?>" data-val="Boisson">Boissons</button>
    </div>

    <div class="filtres-row">
        <strong>Exclure les allergènes :</strong>
        <label><input type="checkbox" class="cb-allergene" value="gluten"> Sans gluten</label>
        <label><input type="checkbox" class="cb-allergene" value="oeuf"> Sans œuf</label>
        <label><input type="checkbox" class="cb-allergene" value="poisson"> Sans poisson</label>
        <label><input type="checkbox" class="cb-allergene" value="fruits à coque"> Sans fruits à coque</label>
        <label><input type="checkbox" class="cb-allergene" value="céleri"> Sans céleri</label>
    </div>

    <div class="filtres-row">
        <strong>Trier par :</strong>
        <select id="select-tri" class="select-tri">
            <option value="">— Ordre par défaut —</option>
            <option value="prix_asc">Prix croissant</option>
            <option value="prix_desc">Prix décroissant</option>
            <option value="nom_az">Nom A → Z</option>
            <option value="nom_za">Nom Z → A</option>
        </select>
    </div>
</div>

<main>
    <div class="page-banner">
        <h2>Notre carte</h2>
        <p>Des plats préparés avec amour, comme à la maison.</p>
    </div>

    <p id="plats-msg">Chargement…</p>

    <section class="plats" id="plats-container">
        <!-- Contenu chargé dynamiquement via plats.js -->
    </section>
</main>

<script src="../assets/js/theme.js"></script>
<script src="../assets/js/plats.js"></script>
</body>
</html>
