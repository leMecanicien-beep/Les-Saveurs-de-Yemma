<?php
session_start();
require_once __DIR__ . '/../lib/users.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'restaurateur') {
    header('Location: connexion.php');
    exit();
}

$id_commande = intval($_GET['id'] ?? 0);
$commandes   = json_decode(file_get_contents(__DIR__ . '/../data/commandes.json'), true);
$plats       = json_decode(file_get_contents(__DIR__ . '/../data/plats.json'), true);
$menus       = json_decode(file_get_contents(__DIR__ . '/../data/menus.json'), true);
$users       = lire_users();

$commande = null;
foreach ($commandes as $c) {
    if ($c['id'] === $id_commande) { $commande = $c; break; }
}
if (!$commande) {
    header('Location: commandes.php');
    exit();
}

$client = null;
foreach ($users as $u) {
    if ($u['id'] === $commande['user_id']) { $client = $u; break; }
}

$livreurs = array_filter($users, fn($u) => $u['role'] === 'livreur');

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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détail commande #<?php echo $commande['id']; ?> - Les Saveurs de Yemma</title>
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
    <a href="commandes.php" class="retour">&larr; Retour à la liste</a>
    <h2>Détail de la commande #<?php echo $commande['id']; ?></h2>

    <div class="detail-bloc">
        <h3>Informations générales</h3>
        <p><strong>Statut actuel :</strong>
            <span class="badge <?php echo $classe_statut[$commande['statut']] ?? ''; ?>">
                <?php echo $label_statut[$commande['statut']] ?? $commande['statut']; ?>
            </span>
        </p>
        <p><strong>Date de commande :</strong> <?php echo $commande['date']; ?></p>
        <?php if ($commande['heure_souhaitee']): ?>
        <p><strong>Livraison / récupération souhaitée :</strong>
            <span style="color:#e67e22;font-weight:bold;"><?php echo $commande['heure_souhaitee']; ?></span>
        </p>
        <?php else: ?>
        <p><strong>Préparation :</strong> Immédiate</p>
        <?php endif; ?>
        <p><strong>Type :</strong>
            <?php
            $types = ['livraison' => 'Livraison à domicile', 'emporter' => 'À emporter', 'sur_place' => 'Sur place'];
            echo $types[$commande['type']] ?? $commande['type'];
            ?>
        </p>
        <?php if ($commande['adresse_livraison']): ?>
        <p><strong>Adresse de livraison :</strong> <?php echo $commande['adresse_livraison']; ?></p>
        <?php endif; ?>
        <p><strong>Montant total :</strong> <strong><?php echo $commande['montant']; ?>€</strong></p>
    </div>

    <div class="detail-bloc">
        <h3>Client</h3>
        <?php if ($client): ?>
        <p><strong>Nom :</strong> <?php echo $client['prenom'] . ' ' . $client['nom']; ?></p>
        <p><strong>Téléphone :</strong> <?php echo $client['telephone']; ?></p>
        <p><strong>Code interphone :</strong> <?php echo $client['code_interphone'] ?: 'Aucun'; ?></p>
        <?php else: ?>
        <p>Client inconnu</p>
        <?php endif; ?>
    </div>

    <div class="detail-bloc">
        <h3>Plats commandés</h3>
        <table class="table-plats">
            <thead>
                <tr><th>Plat</th><th>Qté</th><th>Prix unitaire</th><th>Sous-total</th></tr>
            </thead>
            <tbody>
            <?php foreach ($commande['plats_ids'] as $plat_id): ?>
            <?php
                $plat = null;
                foreach ($plats as $p) { if ($p['id'] === $plat_id) { $plat = $p; break; } }
                $qte = intval($commande['quantites'][(string)$plat_id] ?? 1);
            ?>
            <?php if ($plat): ?>
            <tr>
                <td><?php echo $plat['nom']; ?></td>
                <td><?php echo $qte; ?></td>
                <td><?php echo $plat['prix']; ?>€</td>
                <td><?php echo round($plat['prix'] * $qte, 2); ?>€</td>
            </tr>
            <?php endif; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (!empty($commande['menus_ids'])): ?>
        <p style="margin-top:8px;font-size:0.9em;color:#666;">
            <em>Menus inclus :
            <?php
            foreach ($commande['menus_ids'] as $menu_id) {
                foreach ($menus as $m) {
                    if ($m['id'] === $menu_id) { echo $m['nom'] . ' '; break; }
                }
            }
            ?>
            </em>
        </p>
        <?php endif; ?>
    </div>

    <div class="detail-bloc">
        <h3>Actions (disponibles en Phase 3)</h3>
        <p style="color:#888;font-style:italic;margin-bottom:12px;">
            La modification du statut et l'attribution du livreur seront effectives en Phase 3.
        </p>

        <div class="actions-form">
            <div>
                <label><strong>Changer le statut :</strong></label><br>
                <select disabled style="padding:8px;border-radius:6px;border:1px solid #ccc;margin-top:6px;opacity:0.6;">
                    <?php foreach ($label_statut as $val => $lib): ?>
                    <option value="<?php echo $val; ?>" <?php echo $commande['statut'] === $val ? 'selected' : ''; ?>>
                        <?php echo $lib; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <button disabled style="margin-left:8px;padding:8px 16px;background:#7b2cbf;color:white;border:none;border-radius:6px;opacity:0.5;cursor:not-allowed;">
                    Valider
                </button>
            </div>

            <?php if ($commande['type'] === 'livraison'): ?>
            <div style="margin-top:16px;">
                <label><strong>Attribuer un livreur :</strong></label><br>
                <select disabled style="padding:8px;border-radius:6px;border:1px solid #ccc;margin-top:6px;opacity:0.6;">
                    <option value="">-- Choisir un livreur --</option>
                    <?php foreach ($livreurs as $livreur): ?>
                    <option value="<?php echo $livreur['id']; ?>"
                        <?php echo $commande['livreur_id'] === $livreur['id'] ? 'selected' : ''; ?>>
                        <?php echo $livreur['prenom'] . ' ' . $livreur['nom']; ?>
                        <?php echo ($commande['livreur_id'] === $livreur['id']) ? ' (attribué)' : ''; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <button disabled style="margin-left:8px;padding:8px 16px;background:#27ae60;color:white;border:none;border-radius:6px;opacity:0.5;cursor:not-allowed;">
                    Attribuer
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>
</body>
</html>
