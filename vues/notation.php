<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: connexion.php');
    exit();
}

$commande_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id  = (int)$_POST['commande_id'];
    $q1  = isset($_POST['q1']) ? (int)$_POST['q1'] : 0;
    $q2  = isset($_POST['q2']) ? (int)$_POST['q2'] : 0;
    $q3  = isset($_POST['q3']) ? (int)$_POST['q3'] : 0;
    $q4  = isset($_POST['q4']) ? (int)$_POST['q4'] : 0;

    $note_moyenne = round(($q1 + $q2 + $q3 + $q4) / 4);

    $path = __DIR__ . '/../data/commandes.json';
    $commandes = json_decode(file_get_contents($path), true);

    foreach ($commandes as &$c) {
        if ($c['id'] === $id) {
            $c['note'] = $note_moyenne;
            break;
        }
    }

    file_put_contents($path, json_encode($commandes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    header('Location: profil.php?note=ok');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Notation - Les Saveurs de Yemma</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/notation.css">
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
                <li><a href="profil.php">PROFIL</a></li>
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
                <li><a href="deconnexion.php">DÉCONNEXION</a></li>
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
        <h2>Donnez votre avis</h2>
        <p>Votre retour nous aide à améliorer notre service.</p>
    </div>

    <div class="notation-box">
        <form method="POST" action="notation.php">
            <input type="hidden" name="commande_id" value="<?php echo $commande_id; ?>">

            <div class="question">
                <p>Qualité des plats</p>
                <div class="stars">
                    <input type="radio" id="q1-5" name="q1" value="5"><label for="q1-5">★</label>
                    <input type="radio" id="q1-4" name="q1" value="4"><label for="q1-4">★</label>
                    <input type="radio" id="q1-3" name="q1" value="3"><label for="q1-3">★</label>
                    <input type="radio" id="q1-2" name="q1" value="2"><label for="q1-2">★</label>
                    <input type="radio" id="q1-1" name="q1" value="1"><label for="q1-1">★</label>
                </div>
            </div>

            <div class="question">
                <p>Rapidité de livraison</p>
                <div class="stars">
                    <input type="radio" id="q2-5" name="q2" value="5"><label for="q2-5">★</label>
                    <input type="radio" id="q2-4" name="q2" value="4"><label for="q2-4">★</label>
                    <input type="radio" id="q2-3" name="q2" value="3"><label for="q2-3">★</label>
                    <input type="radio" id="q2-2" name="q2" value="2"><label for="q2-2">★</label>
                    <input type="radio" id="q2-1" name="q2" value="1"><label for="q2-1">★</label>
                </div>
            </div>

            <div class="question">
                <p>Amabilité du livreur</p>
                <div class="stars">
                    <input type="radio" id="q3-5" name="q3" value="5"><label for="q3-5">★</label>
                    <input type="radio" id="q3-4" name="q3" value="4"><label for="q3-4">★</label>
                    <input type="radio" id="q3-3" name="q3" value="3"><label for="q3-3">★</label>
                    <input type="radio" id="q3-2" name="q3" value="2"><label for="q3-2">★</label>
                    <input type="radio" id="q3-1" name="q3" value="1"><label for="q3-1">★</label>
                </div>
            </div>

            <div class="question">
                <p>Rapport qualité / prix</p>
                <div class="stars">
                    <input type="radio" id="q4-5" name="q4" value="5"><label for="q4-5">★</label>
                    <input type="radio" id="q4-4" name="q4" value="4"><label for="q4-4">★</label>
                    <input type="radio" id="q4-3" name="q4" value="3"><label for="q4-3">★</label>
                    <input type="radio" id="q4-2" name="q4" value="2"><label for="q4-2">★</label>
                    <input type="radio" id="q4-1" name="q4" value="1"><label for="q4-1">★</label>
                </div>
            </div>

            <textarea name="commentaire" placeholder="Votre commentaire (optionnel)..."></textarea>

            <button type="submit">Envoyer mon avis</button>
        </form>
    </div>
</main>
<script src="../assets/js/theme.js"></script>
</body>
</html>
