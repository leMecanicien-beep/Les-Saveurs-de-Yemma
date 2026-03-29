<?php
session_start();
require_once __DIR__ . '/../lib/users.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'livreur') {
    header('Location: connexion.php');
    exit();
}

$user      = $_SESSION['user'];
$commandes = json_decode(file_get_contents(__DIR__ . '/../data/commandes.json'), true);
$users     = lire_users();

// Actions livreur
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id_cmd = intval($_GET['id']);
    $action = $_GET['action'];
    foreach ($commandes as &$c) {
        if ($c['id'] === $id_cmd && $c['livreur_id'] === $user['id']) {
            if ($action === 'livree' && $c['statut'] === 'en_livraison') {
                $c['statut'] = 'livree';
            } elseif ($action === 'abandonnee' && $c['statut'] === 'en_livraison') {
                $c['statut'] = 'abandonnee';
            }
            break;
        }
    }
    unset($c);
    file_put_contents(
        __DIR__ . '/../data/commandes.json',
        json_encode($commandes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
    header('Location: livraison.php');
    exit();
}

// Trouver la commande en cours attribuée au livreur
$ma_commande = null;
foreach ($commandes as $commande) {
    if ($commande['livreur_id'] === $user['id'] && $commande['statut'] === 'en_livraison') {
        $ma_commande = $commande;
        break;
    }
}

// Historique des livraisons du livreur
$historique = array_filter($commandes, fn($c) =>
    $c['livreur_id'] === $user['id'] &&
    in_array($c['statut'], ['livree', 'abandonnee'])
);
usort($historique, fn($a, $b) => strcmp($b['date'], $a['date']));

$client = null;
if ($ma_commande) {
    foreach ($users as $u) {
        if ($u['id'] === $ma_commande['user_id']) { $client = $u; break; }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livraison - Les Saveurs de Yemma</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/livraison.css">
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
                <li><a href="livraison.php">MA LIVRAISON</a></li>
                <li><a href="deconnexion.php">DÉCONNEXION</a></li>
            </ul>
        </nav>
    </div>
</header>
<main>
    <h2>Espace livreur — <?php echo $user['prenom'] . ' ' . $user['nom']; ?></h2>

    <?php if ($ma_commande && $client): ?>
    <div class="livraison">
        <h3>Livraison en cours — Commande #<?php echo $ma_commande['id']; ?></h3>
        <p><strong>Client :</strong> <?php echo $client['prenom'] . ' ' . $client['nom']; ?></p>
        <p><strong>Adresse :</strong> <?php echo $ma_commande['adresse_livraison']; ?></p>
        <p><strong>Code interphone :</strong> <?php echo $client['code_interphone'] ?: 'Aucun'; ?></p>
        <p><strong>Téléphone :</strong>
            <a href="tel:<?php echo $client['telephone']; ?>"><?php echo $client['telephone']; ?></a>
        </p>
        <p><strong>Montant :</strong> <?php echo $ma_commande['montant']; ?>€</p>

        <div class="livraison-actions">
            <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($ma_commande['adresse_livraison']); ?>"
               target="_blank" class="btn">Ouvrir dans Maps</a>
            <a href="?action=livree&id=<?php echo $ma_commande['id']; ?>"
               class="btn done"
               onclick="return confirm('Confirmer la livraison de la commande #<?php echo $ma_commande['id']; ?> ?');">
                Livraison terminée
            </a>
            <a href="?action=abandonnee&id=<?php echo $ma_commande['id']; ?>"
               class="btn abandon"
               onclick="return confirm('Confirmer l\'abandon de la livraison (adresse introuvable) ?');">
                Abandonner
            </a>
        </div>
    </div>
    <?php else: ?>
    <div class="livraison">
        <p>Aucune livraison en cours pour le moment.</p>
        <a href="../index.php" class="btn">Retour accueil</a>
    </div>
    <?php endif; ?>

    <?php if (!empty($historique)): ?>
    <div style="margin-top:30px;">
        <h3>Historique de mes livraisons</h3>
        <table style="width:100%;border-collapse:collapse;background:white;border-radius:8px;overflow:hidden;">
            <thead style="background:#7b2cbf;color:white;">
                <tr>
                    <th style="padding:10px;">Commande</th>
                    <th style="padding:10px;">Date</th>
                    <th style="padding:10px;">Client</th>
                    <th style="padding:10px;">Statut</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($historique as $h): ?>
            <?php
                $c_client = null;
                foreach ($users as $u) { if ($u['id'] === $h['user_id']) { $c_client = $u; break; } }
            ?>
            <tr style="border-bottom:1px solid #eee;text-align:center;">
                <td style="padding:10px;">#<?php echo $h['id']; ?></td>
                <td style="padding:10px;"><?php echo $h['date']; ?></td>
                <td style="padding:10px;"><?php echo $c_client ? $c_client['prenom'] . ' ' . $c_client['nom'] : 'Inconnu'; ?></td>
                <td style="padding:10px;">
                    <?php if ($h['statut'] === 'livree'): ?>
                    <span style="color:green;font-weight:bold;">Livrée</span>
                    <?php else: ?>
                    <span style="color:#e74c3c;font-weight:bold;">Abandonnée</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</main>
</body>
</html>
