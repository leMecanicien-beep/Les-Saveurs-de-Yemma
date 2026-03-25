<?php
session_start();
require_once __DIR__ . '/../lib/users.php';

$erreur = "";
$succes = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            // Connexion automatique après inscription
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
    <link rel="stylesheet" href="../assets/choix_connexion_inscription.css">
</head>
<body>
<div class="card">
    <h2>Inscription</h2>
    <?php if ($erreur): ?>
        <p style="color:red;"><?php echo $erreur; ?></p>
    <?php endif; ?>
    <form action="" method="post">
        <input type="text" name="nom" placeholder="Nom" required>
        <input type="text" name="prenom" placeholder="Prénom" required>
        <input type="tel" name="telephone" placeholder="Numéro de téléphone" required>
        <input type="email" name="email" placeholder="Adresse e-mail" required>
        <input type="password" name="mot_de_passe" placeholder="Mot de passe" required>
        <input type="password" name="confirmer" placeholder="Confirmer mot de passe" required>
        <button type="submit" class="btn">S'inscrire</button>
    </form>
    <p><a href="connexion.php">Déjà inscrit ?</a></p>
</div>
</body>
</html>
