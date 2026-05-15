<?php
session_start();
require_once __DIR__ . '/../lib/users.php';
verifier_session_revoquee();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'livreur') {
    header('Location: connexion.php');
    exit();
}

$user      = $_SESSION['user'];
$commandes = json_decode(file_get_contents(__DIR__ . '/../data/commandes.json'), true);
$users     = lire_users();

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
        <div class="barre" style="text-align:right;">
            <button id="btn-theme" title="Changer le thème"
                style="cursor:pointer;border-radius:20px;padding:6px 14px;font-size:13px;border:2px solid #fff;background:rgba(255,255,255,.15);color:#fff;">
                🌕 Mode sombre
            </button>
        </div>
    </div>
</header>
<main>
    <div class="page-banner">
        <h2>Espace livreur</h2>
        <p><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></p>
    </div>

    <p id="livraison-msg" style="display:none;padding:12px 40px;border-radius:6px;margin:10px 40px;"></p>

    <?php if ($ma_commande && $client): ?>
    <div class="livraison">
        <h3>Livraison en cours — Commande #<?php echo $ma_commande['id']; ?></h3>
        <p><strong>Client :</strong> <?php echo htmlspecialchars($client['prenom'] . ' ' . $client['nom']); ?></p>
        <p><strong>Adresse :</strong> <?php echo htmlspecialchars($ma_commande['adresse_livraison']); ?></p>
        <p><strong>Code interphone :</strong> <?php echo htmlspecialchars($client['code_interphone'] ?: 'Aucun'); ?></p>
        <p><strong>Téléphone :</strong>
            <a href="tel:<?php echo $client['telephone']; ?>"><?php echo htmlspecialchars($client['telephone']); ?></a>
        </p>
        <p><strong>Montant :</strong> <?php echo $ma_commande['montant']; ?>€</p>

        <div class="livraison-actions" id="livraison-actions">
            <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($ma_commande['adresse_livraison']); ?>"
               target="_blank" class="btn">Ouvrir dans Maps</a>
            <button id="btn-livree-async"
                    class="btn done"
                    data-commande-id="<?php echo $ma_commande['id']; ?>"
                    data-confirm="Confirmer la livraison de la commande #<?php echo $ma_commande['id']; ?> ?">
                Livraison terminée
            </button>
            <button id="btn-abandonne-async"
                    class="btn abandon"
                    data-commande-id="<?php echo $ma_commande['id']; ?>"
                    data-confirm="Confirmer l'abandon de la livraison (adresse introuvable) ?">
                Abandonner
            </button>
        </div>
    </div>
    <?php else: ?>
    <div class="livraison">
        <p>Aucune livraison en cours pour le moment.</p>
        <a href="../index.php" class="btn">Retour accueil</a>
    </div>
    <?php endif; ?>

    <?php if (!empty($historique)): ?>
    <div class="historique">
        <h3>Historique de mes livraisons</h3>
        <table class="table-livraison">
            <thead>
                <tr>
                    <th>Commande</th>
                    <th>Date</th>
                    <th>Client</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($historique as $h): ?>
            <?php
                $c_client = null;
                foreach ($users as $u) { if ($u['id'] === $h['user_id']) { $c_client = $u; break; } }
            ?>
            <tr>
                <td>#<?php echo $h['id']; ?></td>
                <td><?php echo $h['date']; ?></td>
                <td><?php echo $c_client ? htmlspecialchars($c_client['prenom'] . ' ' . $c_client['nom']) : 'Inconnu'; ?></td>
                <td>
                    <?php if ($h['statut'] === 'livree'): ?>
                    <span class="statut-livree">Livrée</span>
                    <?php else: ?>
                    <span class="statut-abandonnee">Abandonnée</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</main>
<script src="../assets/js/theme.js"></script>
<script src="../assets/js/livraison.js"></script>
</body>
</html>
