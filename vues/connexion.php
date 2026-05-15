<?php
session_start();
require_once __DIR__ . '/../lib/users.php';

$erreur = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email        = $_POST['email'] ?? '';
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    $user = connecter_user($email, $mot_de_passe);

    if ($user === 'bloque') {
        $erreur = "Votre compte est bloqué. Contactez l'administrateur.";
    } elseif ($user) {
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
        $erreur = "Email ou mot de passe incorrect.";
    }
}
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

<div style="position:fixed;top:16px;right:16px;z-index:100;">
    <button id="btn-theme" title="Changer le thème"
        style="cursor:pointer;border-radius:20px;padding:6px 14px;font-size:13px;border:2px solid #b98acb;background:#b98acb;color:#fff;">
        🌕 Mode sombre
    </button>
</div>

<div class="card">
    <h2>Connexion</h2>
    <?php if ($erreur): ?>
        <p class="msg-erreur"><?php echo htmlspecialchars($erreur); ?></p>
    <?php endif; ?>
    <form id="form-connexion" action="" method="post" novalidate>
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

