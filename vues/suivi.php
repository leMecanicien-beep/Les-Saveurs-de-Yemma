<?php
session_start();
require_once __DIR__ . '/../lib/users.php';

if (!isset($_SESSION['user'])) {
    header('Location: connexion.php');
    exit();
}

$user = $_SESSION['user'];
$id_commande = intval($_GET['id'] ?? 0);
$commandes   = json_decode(file_get_contents(__DIR__ . '/../data/commandes.json'), true);
$plats       = json_decode(file_get_contents(__DIR__ . '/../data/plats.json'), true);
$users_all   = lire_users();

$commande = null;
foreach ($commandes as $c) {
    if ($c['id'] === $id_commande && $c['user_id'] === $user['id']) {
        $commande = $c;
        break;
    }
}
if (!$commande) {
    header('Location: profil.php');
    exit();
}

// Étapes de suivi selon le type
if ($commande['type'] === 'livraison') {
    $etapes = [
        'en_attente'    => ['Commande reçue',    'En attente de préparation (commande programmée)'],
        'en_preparation'=> ['En préparation',     'Le restaurant prépare votre commande'],
        'prete'         => ['Prête',              'Votre commande est prête, en attente du livreur'],
        'en_livraison'  => ['En livraison',       'Le livreur est en route vers vous'],
        'livree'        => ['Livrée',             'Votre commande a été livrée. Bon appétit !'],
        'abandonnee'    => ['Abandonnée',         'La livraison n\'a pas pu être effectuée.'],
    ];
} else {
    $etapes = [
        'en_attente'    => ['Commande reçue',    'En attente de préparation (commande programmée)'],
        'en_preparation'=> ['En préparation',     'Le restaurant prépare votre commande'],
        'prete'         => ['Prête à récupérer', 'Votre commande est prête, vous pouvez venir la chercher'],
        'livree'        => ['Récupérée',          'Commande récupérée. Bon appétit !'],
        'abandonnee'    => ['Annulée',            'La commande a été annulée.'],
    ];
}

$ordre = array_keys($etapes);
$statut_actuel = $commande['statut'];
$idx_actuel = array_search($statut_actuel, $ordre);
if ($idx_actuel === false) $idx_actuel = 0;

// Livreur si affecté
$livreur = null;
if ($commande['livreur_id']) {
    foreach ($users_all as $u) {
        if ($u['id'] === $commande['livreur_id']) { $livreur = $u; break; }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Suivi commande #<?php echo $commande['id']; ?> - Les Saveurs de Yemma</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/suivi.css">
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
                <li><a href="plat.php">CARTE</a></li>
                <li><a href="profil.php">PROFIL</a></li>
                <li><a href="deconnexion.php">DÉCONNEXION</a></li>
            </ul>
        </nav>
    </div>
</header>
<main>
    <div class="suivi-card">
        <h2>Suivi — Commande #<?php echo $commande['id']; ?></h2>
        <p><strong>Type :</strong>
            <?php
            $types = ['livraison' => 'Livraison à domicile', 'emporter' => 'À emporter', 'sur_place' => 'Sur place'];
            echo $types[$commande['type']] ?? $commande['type'];
            ?>
        </p>
        <p><strong>Date :</strong> <?php echo $commande['date']; ?></p>
        <?php if ($commande['heure_souhaitee']): ?>
        <p><strong>Livraison souhaitée :</strong>
            <span class="heure-souhaitee"><?php echo $commande['heure_souhaitee']; ?></span>
        </p>
        <?php endif; ?>
        <p><strong>Montant :</strong> <?php echo $commande['montant']; ?>€</p>

        <?php if ($commande['type'] === 'livraison' && $commande['adresse_livraison']): ?>
        <p><strong>Adresse de livraison :</strong> <?php echo $commande['adresse_livraison']; ?></p>
        <?php endif; ?>

        <?php if ($livreur): ?>
        <p><strong>Livreur :</strong> <?php echo $livreur['prenom'] . ' ' . $livreur['nom']; ?>
            — <a href="tel:<?php echo $livreur['telephone']; ?>"><?php echo $livreur['telephone']; ?></a>
        </p>
        <?php endif; ?>

        <div class="etapes">
        <?php foreach ($etapes as $code => $info): ?>
        <?php
            $idx = array_search($code, $ordre);
            $is_abandon = ($code === 'abandonnee');
            if ($is_abandon && $statut_actuel !== 'abandonnee') continue; // masquer sauf si abandon réel
            if ($statut_actuel === 'abandonnee' && $code !== 'abandonnee') {
                $classe = 'attente'; // tout grisé sauf abandon
            } elseif ($idx < $idx_actuel) {
                $classe = 'fait';
            } elseif ($idx === $idx_actuel) {
                $classe = $is_abandon ? 'erreur' : 'actuel';
            } else {
                $classe = 'attente';
            }
            $icone = $is_abandon ? '✕' : ($idx < $idx_actuel ? '✓' : ($idx === $idx_actuel ? '●' : $idx + 1));
            $last = (array_key_last($etapes) === $code);
        ?>
        <div class="etape">
            <?php if (!$last): ?>
            <div class="etape-ligne <?php echo $idx < $idx_actuel ? 'fait' : ''; ?>"></div>
            <?php endif; ?>
            <div class="etape-icon <?php echo $classe; ?>"><?php echo $icone; ?></div>
            <div class="etape-texte">
                <strong><?php echo $info[0]; ?></strong>
                <span><?php echo $info[1]; ?></span>
            </div>
        </div>
        <?php endforeach; ?>
        </div>

        <div class="plats-liste">
            <h4>Récapitulatif de votre commande</h4>
            <?php foreach ($commande['plats_ids'] as $plat_id): ?>
            <?php
                $plat = null;
                foreach ($plats as $p) { if ($p['id'] === $plat_id) { $plat = $p; break; } }
                $qte = intval($commande['quantites'][(string)$plat_id] ?? 1);
            ?>
            <?php if ($plat): ?>
            <div class="plat-ligne">
                <span><?php echo $plat['nom']; ?> ×<?php echo $qte; ?></span>
                <span><?php echo round($plat['prix'] * $qte, 2); ?>€</span>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
            <div class="total-ligne">
                Total : <?php echo $commande['montant']; ?>€
            </div>
        </div>

        <?php if ($statut_actuel === 'livree' && !$commande['note']): ?>
        <div class="action-center">
            <a href="notation.php?id=<?php echo $commande['id']; ?>" class="btn">
                Donner mon avis
            </a>
        </div>
        <?php elseif ($statut_actuel === 'livree' && $commande['note']): ?>
        <p class="note-donnee">
            Vous avez noté cette commande : <?php echo str_repeat('★', $commande['note']) . str_repeat('☆', 5 - $commande['note']); ?>
        </p>
        <?php endif; ?>

        <div class="action-center">
            <a href="profil.php" class="lien-retour">← Retour à mes commandes</a>
        </div>
    </div>
</main>
</body>
</html>
