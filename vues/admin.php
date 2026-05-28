<?php
session_start();
require_once __DIR__ . '/../lib/users.php';
require_once __DIR__ . '/../lib/logs.php';
verifier_session_revoquee();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: connexion.php');
    exit();
}

$users = lire_users();
$logs  = lire_logs(50);

$roles_ordre = ['client', 'restaurateur', 'livreur', 'admin'];
usort($users, function($a, $b) use ($roles_ordre) {
    $ia = array_search($a['role'], $roles_ordre);
    $ib = array_search($b['role'], $roles_ordre);
    return $ia - $ib;
});
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Les Saveurs de Yemma</title>
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
                <li><a href="admin.php#logs">LOGS</a></li>
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
        <h2>Espace administrateur</h2>
        <p><?php echo count($users); ?> utilisateurs enregistrés</p>
    </div>

    <div class="utilisateurs">
        <?php foreach ($users as $user): ?>
        <div class="utilisateur">
            <h3><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></h3>
            <p><strong>Email :</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Rôle :</strong> <?php echo ucfirst($user['role']); ?></p>
            <p><strong>Téléphone :</strong> <?php echo htmlspecialchars($user['telephone']); ?></p>
            <p><strong>Points :</strong> <?php echo $user['points_fidelite']; ?> pts</p>
            <p><strong>Inscrit le :</strong> <?php echo $user['date_inscription']; ?></p>
            <p>
                <strong>Statut :</strong>
                <span class="statut-badges">
                    <span class="badge-statut badge-<?php echo $user['statut'] ?? 'Standard'; ?>">
                        <?php echo $user['statut'] ?? 'Standard'; ?>
                    </span>
                    <?php if ($user['bloque'] ?? false): ?>
                    <span class="badge-statut badge-bloque badge-bloque-dyn">Bloqué</span>
                    <?php endif; ?>
                </span>
            </p>
            <?php if (($user['taux_remise'] ?? 0) > 0): ?>
            <p><strong>Remise :</strong> <?php echo $user['taux_remise']; ?>%</p>
            <?php endif; ?>

            <div class="user-actions">
                <a href="profil_admin.php?id=<?php echo $user['id']; ?>" class="btn-profil">Voir profil</a>
                <?php if ($user['role'] !== 'admin'): ?>
                <button class="btn-bloquer"
                        data-user-id="<?php echo $user['id']; ?>"
                        data-bloque="<?php echo ($user['bloque'] ?? false) ? '1' : '0'; ?>"
                        data-nom="<?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?>">
                    <?php echo ($user['bloque'] ?? false) ? 'Débloquer' : 'Bloquer'; ?>
                </button>
                <?php else: ?>
                <button class="btn-bloquer" disabled title="Impossible de bloquer un administrateur">
                    Bloquer
                </button>
                <?php endif; ?>
                <button class="btn-statut"
                        data-user-id="<?php echo $user['id']; ?>"
                        data-statut="<?php echo htmlspecialchars($user['statut'] ?? 'Standard'); ?>">
                    Modifier statut
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <!-- Section logs d'incidents -->
    <div class="page-banner" id="logs" style="margin-top:40px;">
        <h2>Logs d'incidents</h2>
        <p>50 événements les plus récents</p>
    </div>

    <?php
    $labels_logs = [
        'mauvais_mdp'              => ['label' => 'Mauvais MDP',          'couleur' => '#e74c3c'],
        'compte_bloque_tentative'  => ['label' => 'Tentative compte bloqué', 'couleur' => '#e67e22'],
        'connexion_reussie'        => ['label' => 'Connexion',             'couleur' => '#27ae60'],
        'inscription'              => ['label' => 'Inscription',           'couleur' => '#2980b9'],
        'blocage_compte'           => ['label' => 'Blocage',               'couleur' => '#c0392b'],
        'deblocage_compte'         => ['label' => 'Déblocage',             'couleur' => '#16a085'],
    ];
    ?>
    <div style="overflow-x:auto;margin:0 24px 40px;">
        <table style="width:100%;border-collapse:collapse;font-size:14px;">
            <thead>
                <tr style="background:#b98acb;color:#fff;text-align:left;">
                    <th style="padding:10px;">Date</th>
                    <th style="padding:10px;">Type</th>
                    <th style="padding:10px;">Message</th>
                    <th style="padding:10px;">IP</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                <tr><td colspan="4" style="padding:16px;text-align:center;color:#888;">Aucun log pour l'instant.</td></tr>
                <?php else: ?>
                <?php foreach ($logs as $log): ?>
                <?php $info = $labels_logs[$log['type']] ?? ['label' => $log['type'], 'couleur' => '#888']; ?>
                <tr style="border-bottom:1px solid #eee;">
                    <td style="padding:8px 10px;white-space:nowrap;"><?php echo htmlspecialchars($log['date']); ?></td>
                    <td style="padding:8px 10px;">
                        <span style="background:<?php echo $info['couleur']; ?>;color:#fff;padding:2px 8px;border-radius:12px;font-size:12px;">
                            <?php echo htmlspecialchars($info['label']); ?>
                        </span>
                    </td>
                    <td style="padding:8px 10px;"><?php echo htmlspecialchars($log['message']); ?></td>
                    <td style="padding:8px 10px;font-family:monospace;"><?php echo htmlspecialchars($log['ip']); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
<script src="../assets/js/theme.js"></script>
<script src="../assets/js/admin.js"></script>
</body>
</html>

