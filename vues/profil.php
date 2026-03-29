<?php
session_start();
require_once __DIR__ . '/../lib/users.php';

// Si pas connecté on redirige vers connexion
if (!isset($_SESSION['user'])) {
    header('Location: connexion.php');
    exit();
}

$user = $_SESSION['user'];

// Récupérer les commandes de l'utilisateur
$toutes_commandes = json_decode(file_get_contents(__DIR__ . '/../data/commandes.json'), true);
$mes_commandes = array_filter($toutes_commandes, function($c) use ($user) {
    return $c['user_id'] === $user['id'];
});
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Profil - Les Saveurs de Yemma</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
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
                <li><a href="profil.php">PROFIL</a></li>
                <li class="carte">
                    <a href="#">CARTE</a>
                    <ul class="deroulant">
                        <li><a href="#">FORMULES</a></li>
                        <li><a href="#">TRADITION DU JOUR</a></li>
                        <li><a href="#">OFFRES</a></li>
                    </ul>
                </li>
                <li><a href="panier.php">COMMANDE</a></li>
                <li><a href="#">HORAIRES</a></li>
                <li><a href="deconnexion.php">DÉCONNEXION</a></li>
            </ul>
        </nav>
        <div class="barre">
            <input type="text" placeholder="Rechercher un plat...">
        </div>
    </div>
</header>
<main>
    <?php if (isset($_GET['note']) && $_GET['note'] === 'ok'): ?>
        <p style="text-align:center;background:#f0e6ff;color:#7b2cbf;padding:12px;border-radius:8px;margin-bottom:20px;font-weight:bold;">
            Merci pour votre avis ! ⭐
        </p>
    <?php endif; ?>
    <section class="profil">
        <h2>Profil personnel</h2>
        <div class="profil2">
            <div class="carteprofil">
                <h3>Informations personnelles</h3>
                <p><strong>Nom :</strong> <?php echo $user['nom']; ?></p>
                <p><strong>Prénom :</strong> <?php echo $user['prenom']; ?></p>
                <p><strong>Email :</strong> <?php echo $user['email']; ?></p>
                <p><strong>Téléphone :</strong> <?php echo $user['telephone']; ?></p>
                <p><strong>Adresse :</strong> <?php echo $user['adresse'] ?: 'Non renseignée'; ?></p>
                <div class="info">
                    <a href="#" class="edit">✏️ Modifier</a>
                </div>
            </div>

            <div class="carteprofil">
                <h3>Anciennes commandes</h3>
                <?php if (empty($mes_commandes)): ?>
                    <p>Aucune commande pour l'instant.</p>
                <?php else: ?>
                    <ul>
                        <?php
                        $label_statut = [
                            'en_attente'    => 'En attente',
                            'en_preparation'=> 'En préparation',
                            'prete'         => 'Prête',
                            'en_livraison'  => 'En livraison',
                            'livree'        => 'Livrée',
                            'abandonnee'    => 'Abandonnée',
                        ];
                        usort($mes_commandes, fn($a, $b) => strcmp($b['date'], $a['date']));
                        ?>
                        <?php foreach ($mes_commandes as $commande): ?>
                            <li style="padding:8px 0;border-bottom:1px solid #f0e6ff;">
                                <strong>Commande #<?php echo $commande['id']; ?></strong>
                                — <?php echo $commande['montant']; ?>€
                                — <em><?php echo $label_statut[$commande['statut']] ?? $commande['statut']; ?></em>
                                <br>
                                <small><?php echo $commande['date']; ?></small>
                                &nbsp;
                                <a href="suivi.php?id=<?php echo $commande['id']; ?>" style="font-size:.85em;">Suivre</a>
                                <?php if ($commande['statut'] === 'livree' && !$commande['note']): ?>
                                &nbsp;<a href="notation.php?id=<?php echo $commande['id']; ?>" style="font-size:.85em;color:#f39c12;">⭐ Noter</a>
                                <?php elseif ($commande['statut'] === 'livree' && $commande['note']): ?>
                                &nbsp;<span style="font-size:.85em;color:#7b2cbf;"><?php echo str_repeat('★', $commande['note']); ?></span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="carteprofil">
                <h3>Compte fidélité</h3>
                <p><strong>Points :</strong> <?php echo $user['points_fidelite']; ?> pts</p>
                <p>Prochaine récompense à 500 points</p>
            </div>
        </div>
    </section>
</main>
</body>
</html>
