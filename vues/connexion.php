<?php
session_start();
require_once __DIR__ . '/../lib/users.php';
require_once __DIR__ . '/../lib/logs.php';
require_once __DIR__ . '/../lib/csrf.php';

$erreur = "";
$ip = $_SERVER['REMOTE_ADDR'] ?? 'inconnue';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifier_token_csrf();

    if (compter_tentatives_ip($ip) >= 5) {
        $erreur = "Trop de tentatives. Réessayez dans 15 minutes.";
    } else {
        $email        = trim($_POST['email'] ?? '');
        $mot_de_passe = $_POST['mot_de_passe'] ?? '';

        $user = connecter_user($email, $mot_de_passe);

        if ($user === 'bloque') {
            ajouter_log('compte_bloque_tentative', "Tentative de connexion sur un compte bloqué : $email");
            $erreur = "Votre compte est bloqué. Contactez l'administrateur.";
        } elseif ($user) {
            ajouter_log('connexion_reussie', "Connexion réussie : $email", $user['id']);
            $_SESSION['user'] = $user;
            if ($user['role'] === 'admin') {
                header('Location: admin.php');
            } elseif ($user['role'] === 'restaurateur') {
                header('Location: commandes.php');
            } elseif ($user['role'] === 'livreur') {
                header('Location: livraison.php');
            } else {
                header('Location: ../index.php');
            }
            exit();
        } else {
            ajouter_log('mauvais_mdp', "Mauvais mot de passe pour : $email");
            $erreur = "Email ou mot de passe incorrect.";
        }
    }
}
$csrf = generer_token_csrf();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Les Saveurs de Yemma</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="body-centered">

<div class="btn-theme-fixed">
    <button id="btn-theme" class="btn-theme-purple" title="Changer le thème">
        🌕 Mode sombre
    </button>
</div>

<div class="card">
    <h2>Connexion</h2>
    <?php if ($erreur): ?>
        <p class="msg-erreur"><?php echo htmlspecialchars($erreur); ?></p>
    <?php endif; ?>
    <form id="form-connexion" action="" method="post" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
        <div>
            <input type="email" name="email" placeholder="Adresse e-mail" required
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        </div>
        <div>
            <input type="password" name="mot_de_passe" placeholder="Mot de passe" required>
        </div>
        <button type="submit" class="btn">Se connecter</button>
    </form>
    <p><a href="inscription.php">Pas encore inscrit ?</a></p>
</div>
<script src="../assets/js/theme.js"></script>
<script src="../assets/js/validation.js"></script>
</body>
</html>


