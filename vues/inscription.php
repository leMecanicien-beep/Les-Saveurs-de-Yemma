<?php
session_start();
require_once __DIR__ . '/../lib/users.php';
require_once __DIR__ . '/../lib/csrf.php';
require_once __DIR__ . '/../lib/logs.php';

$erreur = "";
$succes = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifier_token_csrf();
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $mot_de_passe = $_POST['mot_de_passe'];
    $confirmer = $_POST['confirmer'];

    if ($mot_de_passe !== $confirmer) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($mot_de_passe) < 6) {
        $erreur = "Le mot de passe doit faire au moins 6 caractères.";
    } else {
        $user = inscrire_user($nom, $prenom, $email, $telephone, $mot_de_passe);
        if ($user === false) {
            $erreur = "Cet email est déjà utilisé.";
        } else {
            ajouter_log('inscription', "Nouvel utilisateur inscrit : $email", $user['id']);
            $_SESSION['user'] = $user;
            header('Location: ../index.php');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - Les Saveurs de Yemma</title>
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
    <h2>Inscription</h2>
    <?php if ($erreur): ?>
        <p class="msg-erreur"><?php echo htmlspecialchars($erreur); ?></p>
    <?php endif; ?>
    <form id="form-inscription" action="" method="post" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generer_token_csrf()); ?>">
        <div>
            <input type="text" name="nom" placeholder="Nom" required maxlength="50"
                   value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>">
        </div>
        <div>
            <input type="text" name="prenom" placeholder="Prénom" required maxlength="50"
                   value="<?php echo isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : ''; ?>">
        </div>
        <div>
            <input type="tel" name="telephone" placeholder="Numéro de téléphone (ex : 06 12 34 56 78)" required
                   value="<?php echo isset($_POST['telephone']) ? htmlspecialchars($_POST['telephone']) : ''; ?>">
        </div>
        <div>
            <input type="email" name="email" placeholder="Adresse e-mail" required maxlength="100"
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        </div>
        <div>
            <input type="password" name="mot_de_passe" placeholder="Mot de passe (min. 6 caractères)" required maxlength="100">
        </div>
        <div>
            <input type="password" name="confirmer" placeholder="Confirmer mot de passe" required maxlength="100">
        </div>
        <button type="submit" class="btn">S'inscrire</button>
    </form>
    <p><a href="connexion.php">Déjà inscrit ?</a></p>
</div>
<script src="../assets/js/theme.js"></script>
<script src="../assets/js/validation.js"></script>
</body>
</html>
