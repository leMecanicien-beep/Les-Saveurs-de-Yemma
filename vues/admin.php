<?php
session_start();
require_once __DIR__ . '/../lib/users.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: connexion.php');
    exit();
}

$users = lire_users();

// Regrouper par rôle pour l'affichage
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
                <li><a href="deconnexion.php">DÉCONNEXION</a></li>
            </ul>
        </nav>
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
            <h3><?php echo $user['prenom'] . ' ' . $user['nom']; ?></h3>
            <p><strong>Email :</strong> <?php echo $user['email']; ?></p>
            <p><strong>Rôle :</strong> <?php echo ucfirst($user['role']); ?></p>
            <p><strong>Téléphone :</strong> <?php echo $user['telephone']; ?></p>
            <p><strong>Points :</strong> <?php echo $user['points_fidelite']; ?> pts</p>
            <p><strong>Inscrit le :</strong> <?php echo $user['date_inscription']; ?></p>
            <p>
                <strong>Statut :</strong>
                <span class="badge-statut badge-<?php echo $user['statut'] ?? 'Standard'; ?>">
                    <?php echo $user['statut'] ?? 'Standard'; ?>
                </span>
                <?php if ($user['bloque'] ?? false): ?>
                <span class="badge-statut badge-bloque">Bloqué</span>
                <?php endif; ?>
            </p>
            <?php if (($user['taux_remise'] ?? 0) > 0): ?>
            <p><strong>Remise :</strong> <?php echo $user['taux_remise']; ?>%</p>
            <?php endif; ?>

            <div class="user-actions">
                <a href="profil_admin.php?id=<?php echo $user['id']; ?>" class="btn-profil">Voir profil</a>
                <button class="btn-bloquer" disabled title="Disponible en Phase 3">
                    <?php echo ($user['bloque'] ?? false) ? 'Débloquer' : 'Bloquer'; ?>
                </button>
                <button class="btn-statut" disabled title="Disponible en Phase 3">Modifier statut</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</main>
</body>
</html>
