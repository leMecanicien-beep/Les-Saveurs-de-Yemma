<?php
session_start();
require_once __DIR__ . '/../lib/users.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: connexion.php');
    exit();
}

$id_cible = intval($_GET['id'] ?? 0);
$users = lire_users();

$cible = null;
foreach ($users as $u) {
    if ($u['id'] === $id_cible) { $cible = $u; break; }
}
if (!$cible) {
    header('Location: admin.php');
    exit();
}

// Commandes de cet utilisateur
$commandes = json_decode(file_get_contents(__DIR__ . '/../data/commandes.json'), true);
$ses_commandes = array_filter($commandes, fn($c) => $c['user_id'] === $id_cible);
usort($ses_commandes, fn($a, $b) => strcmp($b['date'], $a['date']));

$label_statut = [
    'en_attente'    => 'En attente',
    'en_preparation'=> 'En préparation',
    'prete'         => 'Prête',
    'en_livraison'  => 'En livraison',
    'livree'        => 'Livrée',
    'abandonnee'    => 'Abandonnée',
];

$statuts_disponibles = ['Standard', 'Premium', 'VIP'];
$remises_disponibles = [0, 5, 10, 15, 20];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Profil utilisateur — Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .info-card { background:white; border-radius:10px; padding:20px; margin-bottom:20px; box-shadow:0 2px 8px rgba(0,0,0,.08); }
        .info-card h3 { margin-top:0; color:#7b2cbf; }
        .badge-statut { display:inline-block; padding:4px 12px; border-radius:20px; font-size:.85em; font-weight:bold; }
        .badge-Standard { background:#e0e0e0; color:#555; }
        .badge-Premium  { background:#fff3cd; color:#856404; }
        .badge-VIP      { background:#d4edda; color:#155724; }
        .badge-bloque   { background:#f8d7da; color:#721c24; }
        .btn-admin { padding:8px 16px; border:none; border-radius:6px; cursor:not-allowed; opacity:.6; font-size:.9em; }
        .btn-bloquer  { background:#e74c3c; color:white; }
        .btn-statut   { background:#f39c12; color:white; }
        .btn-remise   { background:#27ae60; color:white; }
    </style>
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
                <li><a href="admin.php">UTILISATEURS</a></li>
                <li><a href="deconnexion.php">DÉCONNEXION</a></li>
            </ul>
        </nav>
    </div>
</header>
<main>
    <a href="admin.php" style="display:inline-block;margin-bottom:16px;color:#7b2cbf;">&larr; Retour à la liste</a>
    <h2>Profil de <?php echo $cible['prenom'] . ' ' . $cible['nom']; ?></h2>

    <div class="info-card">
        <h3>Informations personnelles</h3>
        <p><strong>Nom :</strong> <?php echo $cible['nom']; ?></p>
        <p><strong>Prénom :</strong> <?php echo $cible['prenom']; ?></p>
        <p><strong>Email :</strong> <?php echo $cible['email']; ?></p>
        <p><strong>Téléphone :</strong> <?php echo $cible['telephone']; ?></p>
        <p><strong>Adresse :</strong> <?php echo $cible['adresse'] ?: 'Non renseignée'; ?></p>
        <p><strong>Inscrit le :</strong> <?php echo $cible['date_inscription']; ?></p>
        <p><strong>Dernière connexion :</strong> <?php echo $cible['date_derniere_connexion'] ?? 'Inconnue'; ?></p>
        <p><strong>Rôle :</strong> <?php echo ucfirst($cible['role']); ?></p>
    </div>

    <div class="info-card">
        <h3>Statut du compte</h3>
        <p>
            <strong>Statut :</strong>
            <span class="badge-statut badge-<?php echo $cible['statut'] ?? 'Standard'; ?>">
                <?php echo $cible['statut'] ?? 'Standard'; ?>
            </span>
        </p>
        <p>
            <strong>État :</strong>
            <?php if ($cible['bloque'] ?? false): ?>
            <span class="badge-statut badge-bloque">Bloqué</span>
            <?php else: ?>
            <span style="color:green;font-weight:bold;">Actif</span>
            <?php endif; ?>
        </p>
        <p><strong>Taux de remise :</strong> <?php echo $cible['taux_remise'] ?? 0; ?>%</p>
        <p><strong>Points fidélité :</strong> <?php echo $cible['points_fidelite']; ?> pts</p>
    </div>

    <div class="info-card">
        <h3>Actions administrateur <span style="font-size:.75em;color:#999;">(effectives en Phase 3)</span></h3>
        <p style="color:#888;font-style:italic;margin-bottom:16px;">
            Les actions ci-dessous sont affichées mais non fonctionnelles jusqu'à la Phase 3.
        </p>

        <div style="display:flex;flex-wrap:wrap;gap:16px;align-items:flex-start;">

            <div>
                <label style="font-weight:bold;display:block;margin-bottom:6px;">
                    <?php echo ($cible['bloque'] ?? false) ? 'Débloquer le compte' : 'Bloquer le compte'; ?>
                </label>
                <button disabled class="btn-admin btn-bloquer">
                    <?php echo ($cible['bloque'] ?? false) ? 'Débloquer' : 'Bloquer'; ?>
                </button>
            </div>

            <div>
                <label style="font-weight:bold;display:block;margin-bottom:6px;">Modifier le statut</label>
                <select disabled style="padding:8px;border-radius:6px;border:1px solid #ccc;opacity:.6;">
                    <?php foreach ($statuts_disponibles as $s): ?>
                    <option <?php echo ($cible['statut'] ?? 'Standard') === $s ? 'selected' : ''; ?>>
                        <?php echo $s; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <button disabled class="btn-admin btn-statut" style="margin-left:6px;">Appliquer</button>
            </div>

            <div>
                <label style="font-weight:bold;display:block;margin-bottom:6px;">Remise accordée</label>
                <select disabled style="padding:8px;border-radius:6px;border:1px solid #ccc;opacity:.6;">
                    <?php foreach ($remises_disponibles as $r): ?>
                    <option value="<?php echo $r; ?>" <?php echo ($cible['taux_remise'] ?? 0) === $r ? 'selected' : ''; ?>>
                        <?php echo $r; ?>%
                    </option>
                    <?php endforeach; ?>
                </select>
                <button disabled class="btn-admin btn-remise" style="margin-left:6px;">Appliquer</button>
            </div>

        </div>
    </div>

    <?php if (!empty($ses_commandes)): ?>
    <div class="info-card">
        <h3>Historique des commandes (<?php echo count($ses_commandes); ?>)</h3>
        <table style="width:100%;border-collapse:collapse;">
            <thead style="background:#f5f5f5;">
                <tr>
                    <th style="padding:8px;text-align:left;">#</th>
                    <th style="padding:8px;text-align:left;">Date</th>
                    <th style="padding:8px;text-align:left;">Montant</th>
                    <th style="padding:8px;text-align:left;">Type</th>
                    <th style="padding:8px;text-align:left;">Statut</th>
                    <th style="padding:8px;text-align:left;">Note</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($ses_commandes as $cmd): ?>
            <tr style="border-bottom:1px solid #eee;">
                <td style="padding:8px;"><?php echo $cmd['id']; ?></td>
                <td style="padding:8px;"><?php echo $cmd['date']; ?></td>
                <td style="padding:8px;"><?php echo $cmd['montant']; ?>€</td>
                <td style="padding:8px;"><?php echo $cmd['type']; ?></td>
                <td style="padding:8px;"><?php echo $label_statut[$cmd['statut']] ?? $cmd['statut']; ?></td>
                <td style="padding:8px;"><?php echo $cmd['note'] ? $cmd['note'] . '/5' : '—'; ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</main>
</body>
</html>
