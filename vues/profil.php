<?php
session_start();
require_once __DIR__ . '/../lib/users.php';
verifier_session_revoquee();

if (!isset($_SESSION['user'])) {
    header('Location: connexion.php');
    exit();
}

$user = $_SESSION['user'];

$toutes_commandes = json_decode(file_get_contents(__DIR__ . '/../data/commandes.json'), true);
$mes_commandes = array_filter($toutes_commandes, function($c) use ($user) {
    return $c['user_id'] === $user['id'];
});
usort($mes_commandes, fn($a, $b) => strcmp($b['date'], $a['date']));

$label_statut = [
    'en_attente'     => 'En attente',
    'en_preparation' => 'En préparation',
    'prete'          => 'Prête',
    'en_livraison'   => 'En livraison',
    'livree'         => 'Livrée',
    'abandonnee'     => 'Abandonnée',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Profil personnel - Les saveurs de Yemma</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/profil.css">
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
                    <a href="plat.php">CARTE</a>
                    <ul class="deroulant">
                        <li><a href="plat.php">FORMULES</a></li>
                        <li><a href="tradition.php">TRADITION DU JOUR</a></li>
                        <li><a href="offre.php">OFFRES</a></li>
                    </ul>
                </li>
                <li><a href="panier.php">COMMANDE</a></li>
                <li><a href="horaires.php">HORAIRES</a></li>
                <li class="propos">
                    <a href="#">A PROPOS</a>
                    <ul class="deroulant2">
                        <li><a href="apropos.php">RESTAURANT</a></li>
                        <li><a href="partenaires.php">PARTENAIRES</a></li>
                    </ul>
                </li>
                <li><a href="deconnexion.php">DÉCONNEXION</a></li>
            </ul>
        </nav>
        <div class="barre">
            <button id="btn-theme" title="Changer le thème"
                style="cursor:pointer;border-radius:20px;padding:6px 14px;font-size:13px;border:2px solid #fff;background:rgba(255,255,255,.15);color:#fff;margin-right:8px;vertical-align:middle;">
                🌕 Mode sombre
            </button>
            <input type="text" placeholder="Rechercher un plat...">
        </div>
    </div>
</header>

<main>
    <?php if (isset($_GET['note']) && $_GET['note'] === 'ok'): ?>
        <p class="notif-succes">Merci pour votre avis !</p>
    <?php endif; ?>

    <p id="profil-msg" style="display:none;margin:10px 40px;padding:10px;border-radius:6px;"></p>

    <section class="profil">
        <h2>Profil personnel</h2>

        <div class="profil2">

            <!-- Carte informations personnelles -->
            <div class="carteprofil">
                <h3>Informations personnelles</h3>

                <!-- Vue lecture seule -->
                <div id="view-profil">
                    <p><strong>Nom :</strong> <span id="view-nom"><?php echo htmlspecialchars($user['nom']); ?></span></p>
                    <p><strong>Prénom :</strong> <span id="view-prenom"><?php echo htmlspecialchars($user['prenom']); ?></span></p>
                    <p><strong>Email :</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Téléphone :</strong> <span id="view-tel"><?php echo htmlspecialchars($user['telephone']); ?></span></p>
                    <p><strong>Adresse :</strong> <span id="view-adresse"><?php echo $user['adresse'] ? htmlspecialchars($user['adresse']) : 'Non renseignée'; ?></span></p>
                </div>

                <!-- Formulaire d'édition (masqué par défaut) -->
                <form id="form-edit-profil" style="display:none;margin-top:12px;" novalidate>
                    <div style="margin-bottom:8px;">
                        <label style="display:block;font-weight:bold;margin-bottom:3px;">Nom</label>
                        <input type="text" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>" required maxlength="50" style="width:100%;padding:8px;">
                    </div>
                    <div style="margin-bottom:8px;">
                        <label style="display:block;font-weight:bold;margin-bottom:3px;">Prénom</label>
                        <input type="text" name="prenom" value="<?php echo htmlspecialchars($user['prenom']); ?>" required maxlength="50" style="width:100%;padding:8px;">
                    </div>
                    <div style="margin-bottom:8px;">
                        <label style="display:block;font-weight:bold;margin-bottom:3px;">Téléphone</label>
                        <input type="tel" name="telephone" value="<?php echo htmlspecialchars($user['telephone']); ?>" maxlength="20" style="width:100%;padding:8px;">
                    </div>
                    <div style="margin-bottom:8px;">
                        <label style="display:block;font-weight:bold;margin-bottom:3px;">Adresse de livraison</label>
                        <input type="text" name="adresse" value="<?php echo htmlspecialchars($user['adresse'] ?? ''); ?>" maxlength="200" style="width:100%;padding:8px;">
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="display:block;font-weight:bold;margin-bottom:3px;">Code interphone</label>
                        <input type="text" name="code_interphone" value="<?php echo htmlspecialchars($user['code_interphone'] ?? ''); ?>" maxlength="10" style="width:100%;padding:8px;">
                    </div>
                    <div style="display:flex;gap:10px;">
                        <button type="submit" class="btn">Enregistrer</button>
                        <button type="button" id="btn-annuler-profil" class="btn" style="background:#888;">Annuler</button>
                    </div>
                </form>

                <div class="info" style="margin-top:12px;">
                    <button id="btn-modifier-profil" class="btn" style="cursor:pointer;background:none;border:none;color:inherit;padding:0;font-size:inherit;">
                        <img src="../assets/images/crayon2.png" alt="" class="crayon" style="height:16px;vertical-align:middle;margin-right:4px;">
                        <span style="text-decoration:underline;">Modifier</span>
                    </button>
                </div>
            </div>

            <div class="carteprofil">
                <h3>Anciennes commandes</h3>
                <?php if (empty($mes_commandes)): ?>
                    <p>Aucune commande pour l'instant.</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($mes_commandes as $commande): ?>
                        <li class="commande-item">
                            <strong>Commande #<?php echo $commande['id']; ?></strong>
                            — <?php echo $commande['montant']; ?>€
                            — <em><?php echo $label_statut[$commande['statut']] ?? $commande['statut']; ?></em>
                            <br>
                            <small><?php echo $commande['date']; ?></small>
                            &nbsp;
                            <a href="suivi.php?id=<?php echo $commande['id']; ?>" class="lien-sm">Suivre</a>
                            <a href="panier.php?recommander=<?php echo $commande['id']; ?>" class="lien-sm" title="Recommander les mêmes articles">↺ Recommander</a>
                            <?php if ($commande['statut'] === 'livree' && !$commande['note']): ?>
                                &nbsp;<a href="notation.php?id=<?php echo $commande['id']; ?>" class="lien-noter">
                                    <img src="../assets/images/etoile.png" class="etoile" alt="Noter">Noter
                                </a>
                            <?php elseif ($commande['statut'] === 'livree' && $commande['note']): ?>
                                &nbsp;<span class="note-etoiles"><?php echo str_repeat('★', $commande['note']); ?></span>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="carteprofil">
                <h3>Compte fidélité</h3>
                <p><strong>Points obtenus :</strong> <?php echo $user['points_fidelite']; ?> pts</p>
                <p>Prochaine récompense à 500 points</p>
            </div>

        </div>
    </section>
</main>
<img src="../assets/images/tahia.png" class="tahia" alt="Décoration">
<script src="../assets/js/theme.js"></script>
<script src="../assets/js/profil.js"></script>
</body>
</html>

