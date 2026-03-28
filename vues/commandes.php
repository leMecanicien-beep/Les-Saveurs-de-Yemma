<?php
session_start();
require_once __DIR__ . '/../lib/users.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'restaurateur') {
    header('Location: connexion.php');
    exit();
}

$commandes = json_decode(file_get_contents(__DIR__ . '/../data/commandes.json'), true);
$plats     = json_decode(file_get_contents(__DIR__ . '/../data/plats.json'), true);
$menus     = json_decode(file_get_contents(__DIR__ . '/../data/menus.json'), true);
$users     = lire_users();

function trouver_plat($plats, $id) {
    foreach ($plats as $plat) {
        if ($plat['id'] === $id) return $plat;
    }
    return null;
}

function trouver_user($users, $id) {
    foreach ($users as $user) {
        if ($user['id'] === $id) return $user;
    }
    return null;
}

$label_statut = [
    'en_attente'    => 'En attente',
    'en_preparation'=> 'En préparation',
    'prete'         => 'Prête',
    'en_livraison'  => 'En livraison',
    'livree'        => 'Livrée',
    'abandonnee'    => 'Abandonnée',
];
$classe_statut = [
    'en_attente'    => 'attente',
    'en_preparation'=> 'preparation',
    'prete'         => 'prete',
    'en_livraison'  => 'en-livraison',
    'livree'        => 'fini',
    'abandonnee'    => 'abandonnee',
];

// Filtre par statut
$filtre = $_GET['filtre'] ?? 'toutes';
$commandes_affichees = $commandes;
if ($filtre !== 'toutes') {
    $commandes_affichees = array_filter($commandes, fn($c) => $c['statut'] === $filtre);
}
// Plus récentes en premier
usort($commandes_affichees, fn($a, $b) => strcmp($b['date'], $a['date']));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Commandes - Les Saveurs de Yemma</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/commande.css">
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
                <li><a href="commandes.php">COMMANDES</a></li>
                <li><a href="deconnexion.php">DÉCONNEXION</a></li>
            </ul>
        </nav>
    </div>
</header>
<main>
    <h2>Espace restaurateur — Liste des commandes</h2>

    <div class="filtres">
        <a href="?filtre=toutes" class="filtre-btn <?php echo $filtre === 'toutes' ? 'actif' : ''; ?>">Toutes</a>
        <a href="?filtre=en_attente" class="filtre-btn <?php echo $filtre === 'en_attente' ? 'actif' : ''; ?>">En attente</a>
        <a href="?filtre=en_preparation" class="filtre-btn <?php echo $filtre === 'en_preparation' ? 'actif' : ''; ?>">En préparation</a>
        <a href="?filtre=prete" class="filtre-btn <?php echo $filtre === 'prete' ? 'actif' : ''; ?>">Prêtes</a>
        <a href="?filtre=en_livraison" class="filtre-btn <?php echo $filtre === 'en_livraison' ? 'actif' : ''; ?>">En livraison</a>
        <a href="?filtre=livree" class="filtre-btn <?php echo $filtre === 'livree' ? 'actif' : ''; ?>">Livrées</a>
    </div>

    <?php foreach ($commandes_affichees as $commande): ?>
    <?php $client = trouver_user($users, $commande['user_id']); ?>
    <div class="commande">
        <div class="commande-info">
            <h3>Commande #<?php echo $commande['id']; ?></h3>
            <p><strong>Client :</strong> <?php echo $client ? $client['prenom'] . ' ' . $client['nom'] : 'Inconnu'; ?></p>
            <p><strong>Type :</strong>
                <?php
                $types = ['livraison' => 'Livraison', 'emporter' => 'À emporter', 'sur_place' => 'Sur place'];
                echo $types[$commande['type']] ?? $commande['type'];
                ?>
            </p>
            <p><strong>Date commande :</strong> <?php echo $commande['date']; ?></p>
            <?php if ($commande['heure_souhaitee']): ?>
            <p><strong>Livraison souhaitée :</strong> <span style="color:#e67e22;font-weight:bold;"><?php echo $commande['heure_souhaitee']; ?></span></p>
            <?php endif; ?>
            <p><strong>Montant :</strong> <?php echo $commande['montant']; ?>€</p>
            <p><strong>Plats :</strong>
                <?php foreach ($commande['plats_ids'] as $plat_id): ?>
                <?php $plat = trouver_plat($plats, $plat_id); ?>
                <?php $qte = $commande['quantites'][(string)$plat_id] ?? 1; ?>
                <?php if ($plat): ?><span><?php echo $plat['nom']; ?> ×<?php echo $qte; ?></span>&nbsp; <?php endif; ?>
                <?php endforeach; ?>
            </p>
        </div>
        <div class="commande-actions">
            <?php $statut = $commande['statut']; ?>
            <span class="badge <?php echo $classe_statut[$statut] ?? ''; ?>">
                <?php echo $label_statut[$statut] ?? $statut; ?>
            </span>
            <a href="detail_commande.php?id=<?php echo $commande['id']; ?>" class="btn-detail">Voir le détail →</a>
        </div>
    </div>
    <?php endforeach; ?>
</main>
</body>
</html>
