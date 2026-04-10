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
                <li><a href="admin.php">UTILISATEURS</a></li>
                <li><a href="deconnexion.php">DÉCONNEXION</a></li>
            </ul>
        </nav>
    </div>
</header>
<main>
    <a href="admin.php" class="lien-retour">&larr; Retour à la liste</a>
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
            <span class="actif-text">Actif</span>
            <?php endif; ?>
        </p>
        <p><strong>Taux de remise :</strong> <?php echo $cible['taux_remise'] ?? 0; ?>%</p>
        <p><strong>Points fidélité :</strong> <?php echo $cible['points_fidelite']; ?> pts</p>
    </div>

    <div class="info-card">
        <h3>Actions administrateur <span class="mention-phase">(effectives en Phase 3)</span></h3>
        <p class="info-phase">
            Les actions ci-dessous sont affichées mais non fonctionnelles jusqu'à la Phase 3.
        </p>

        <div class="admin-actions-grid">

            <div>
                <label class="action-label">
                    <?php echo ($cible['bloque'] ?? false) ? 'Débloquer le compte' : 'Bloquer le compte'; ?>
                </label>
                <button disabled class="btn-admin btn-bloquer">
                    <?php echo ($cible['bloque'] ?? false) ? 'Débloquer' : 'Bloquer'; ?>
                </button>
            </div>

            <div>
                <label class="action-label">Modifier le statut</label>
                <select disabled class="select-admin">
                    <?php foreach ($statuts_disponibles as $s): ?>
                    <option <?php echo ($cible['statut'] ?? 'Standard') === $s ? 'selected' : ''; ?>>
                        <?php echo $s; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <button disabled class="btn-admin btn-statut btn-admin-ml">Appliquer</button>
            </div>

            <div>
                <label class="action-label">Remise accordée</label>
                <select disabled class="select-admin">
                    <?php foreach ($remises_disponibles as $r): ?>
                    <option value="<?php echo $r; ?>" <?php echo ($cible['taux_remise'] ?? 0) === $r ? 'selected' : ''; ?>>
                        <?php echo $r; ?>%
                    </option>
                    <?php endforeach; ?>
                </select>
                <button disabled class="btn-admin btn-remise btn-admin-ml">Appliquer</button>
            </div>

        </div>
    </div>

    <?php if (!empty($ses_commandes)): ?>
    <div class="info-card">
        <h3>Historique des commandes (<?php echo count($ses_commandes); ?>)</h3>
        <table class="table-admin">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Montant</th>
                    <th>Type</th>
                    <th>Statut</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($ses_commandes as $cmd): ?>
            <tr>
                <td><?php echo $cmd['id']; ?></td>
                <td><?php echo $cmd['date']; ?></td>
                <td><?php echo $cmd['montant']; ?>€</td>
                <td><?php echo $cmd['type']; ?></td>
                <td><?php echo $label_statut[$cmd['statut']] ?? $cmd['statut']; ?></td>
                <td><?php echo $cmd['note'] ? $cmd['note'] . '/5' : '—'; ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</main>
</body>
</html>
