<?php
session_start();
require_once __DIR__ . '/../lib/users.php';

$erreur = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $mot_de_passe = $_POST['mot_de_passe'];
    
    $user = connecter_user($email, $mot_de_passe);
    
    if ($user) {
        // Connexion réussie : on stocke l'user dans la session
        $_SESSION['user'] = $user;
        
        // Redirection selon le rôle
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
    <link rel="stylesheet" href="../assets/choix_connexion_inscription.css">
</head>
<body>
<div class="card">
    <h2>Connexion</h2>
    <?php if ($erreur): ?>
        <p style="color:red;"><?php echo $erreur; ?></p>
    <?php endif; ?>
    <form action="" method="post">
        <input type="email" name="email" placeholder="Adresse e-mail" required>
        <input type="password" name="mot_de_passe" placeholder="Mot de passe" required>
        <button type="submit" class="btn">Se connecter</button>
    </form>
    <p><a href="inscription.php">Pas encore inscrit ?</a></p>
</div>
</body>
</html>
