<?php
session_start();
require_once __DIR__ . '/../lib/users.php';
verifier_session_revoquee();

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
        <div class="barre" style="text-align:right;">
            <button id="btn-theme" title="Changer le thème"
                style="cursor:pointer;border-radius:20px;padding:6px 14px;font-size:13px;border:2px solid #fff;background:rgba(255,255,255,.15);color:#fff;">
                🌕 Mode sombre
            </button>
        </div>
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
        <h3>Actions administrateur</h3>

        <div class="admin-actions-grid">

            <div>
                <label class="action-label">
                    <?php echo ($cible['bloque'] ?? false) ? 'Débloquer le compte' : 'Bloquer le compte'; ?>
                </label>
                <button id="btn-bloquer-profil" class="btn-admin btn-bloquer"
                    data-user-id="<?php echo $cible['id']; ?>"
                    data-bloque="<?php echo ($cible['bloque'] ?? false) ? '1' : '0'; ?>"
                    data-nom="<?php echo htmlspecialchars($cible['prenom'] . ' ' . $cible['nom']); ?>">
                    <?php echo ($cible['bloque'] ?? false) ? 'Débloquer' : 'Bloquer'; ?>
                </button>
            </div>

            <div>
                <label class="action-label">Modifier le statut</label>
                <select id="select-statut-profil" class="select-admin">
                    <?php foreach ($statuts_disponibles as $s): ?>
                    <option value="<?php echo $s; ?>" <?php echo ($cible['statut'] ?? 'Standard') === $s ? 'selected' : ''; ?>>
                        <?php echo $s; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <button id="btn-statut-profil" class="btn-admin btn-admin-ml"
                    data-user-id="<?php echo $cible['id']; ?>">
                    Appliquer
                </button>
            </div>

            <div>
                <label class="action-label">Remise accordée</label>
                <select id="select-remise-profil" class="select-admin">
                    <?php foreach ($remises_disponibles as $r): ?>
                    <option value="<?php echo $r; ?>" <?php echo ($cible['taux_remise'] ?? 0) === $r ? 'selected' : ''; ?>>
                        <?php echo $r; ?>%
                    </option>
                    <?php endforeach; ?>
                </select>
                <button id="btn-remise-profil" class="btn-admin btn-admin-ml"
                    data-user-id="<?php echo $cible['id']; ?>">
                    Appliquer
                </button>
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
<script src="../assets/js/theme.js"></script>
<script>
(function () {
    function toast(msg, ok) {
        var t = document.getElementById('pa-toast');
        if (!t) {
            t = document.createElement('div');
            t.id = 'pa-toast';
            t.style.cssText = 'position:fixed;bottom:30px;right:30px;padding:14px 22px;border-radius:8px;font-size:15px;z-index:9999;color:#fff;display:none;';
            document.body.appendChild(t);
        }
        t.textContent = msg;
        t.style.backgroundColor = ok ? '#27ae60' : '#c0392b';
        t.style.display = 'block';
        setTimeout(function () { t.style.display = 'none'; }, 3500);
    }

    // Bloquer / Débloquer
    var btnBloquer = document.getElementById('btn-bloquer-profil');
    if (btnBloquer) {
        btnBloquer.addEventListener('click', function () {
            var userId = parseInt(btnBloquer.dataset.userId);
            var bloque = btnBloquer.dataset.bloque === '1';
            var nom    = btnBloquer.dataset.nom || 'cet utilisateur';
            if (!confirm('Voulez-vous ' + (bloque ? 'débloquer' : 'bloquer') + ' ' + nom + ' ?')) return;
            btnBloquer.disabled = true;
            fetch('api/bloquer_user.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId })
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    btnBloquer.dataset.bloque = res.bloque ? '1' : '0';
                    btnBloquer.textContent    = res.bloque ? 'Débloquer' : 'Bloquer';
                    btnBloquer.previousElementSibling.textContent = res.bloque ? 'Débloquer le compte' : 'Bloquer le compte';
                    toast(res.message, true);
                } else {
                    toast(res.message, false);
                }
                btnBloquer.disabled = false;
            })
            .catch(function () { toast('Erreur réseau.', false); btnBloquer.disabled = false; });
        });
    }

    // Modifier statut
    var btnStatut = document.getElementById('btn-statut-profil');
    if (btnStatut) {
        btnStatut.addEventListener('click', function () {
            var userId = parseInt(btnStatut.dataset.userId);
            var statut = document.getElementById('select-statut-profil').value;
            fetch('api/modifier_statut.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId, statut: statut })
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    toast('Statut mis à jour : ' + res.statut, true);
                } else {
                    toast(res.message, false);
                }
            })
            .catch(function () { toast('Erreur réseau.', false); });
        });
    }

    // Modifier remise
    var btnRemise = document.getElementById('btn-remise-profil');
    if (btnRemise) {
        btnRemise.addEventListener('click', function () {
            var userId = parseInt(btnRemise.dataset.userId);
            var remise = parseInt(document.getElementById('select-remise-profil').value);
            fetch('api/modifier_remise.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId, taux_remise: remise })
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    toast('Remise mise à jour : ' + remise + '%', true);
                } else {
                    toast(res.message, false);
                }
            })
            .catch(function () { toast('Erreur réseau.', false); });
        });
    }
})();
</script>
</body>
</html>

