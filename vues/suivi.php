<?php
session_start();
require_once __DIR__ . '/../lib/users.php';
verifier_session_revoquee();

if (!isset($_SESSION['user'])) {
    header('Location: connexion.php');
    exit();
}

$user        = $_SESSION['user'];
$id_commande = intval($_GET['id'] ?? 0);
$commandes   = json_decode(file_get_contents(__DIR__ . '/../data/commandes.json'), true);
$plats       = json_decode(file_get_contents(__DIR__ . '/../data/plats.json'), true);
$users_all   = lire_users();

$commande = null;
foreach ($commandes as $c) {
    if ($c['id'] === $id_commande && $c['user_id'] === $user['id']) {
        $commande = $c;
        break;
    }
}
if (!$commande) {
    header('Location: profil.php');
    exit();
}

// Étapes de suivi selon le type
if ($commande['type'] === 'livraison') {
    $etapes = [
        'en_attente'    => ['Commande reçue',    'En attente de préparation (commande programmée)'],
        'en_preparation'=> ['En préparation',     'Le restaurant prépare votre commande'],
        'prete'         => ['Prête',              'Votre commande est prête, en attente du livreur'],
        'en_livraison'  => ['En livraison',       'Le livreur est en route vers vous'],
        'livree'        => ['Livrée',             'Votre commande a été livrée. Bon appétit !'],
        'abandonnee'    => ['Abandonnée',         'La livraison n\'a pas pu être effectuée.'],
    ];
} else {
    $etapes = [
        'en_attente'    => ['Commande reçue',    'En attente de préparation (commande programmée)'],
        'en_preparation'=> ['En préparation',     'Le restaurant prépare votre commande'],
        'prete'         => ['Prête à récupérer', 'Votre commande est prête, vous pouvez venir la chercher'],
        'livree'        => ['Récupérée',          'Commande récupérée. Bon appétit !'],
        'abandonnee'    => ['Annulée',            'La commande a été annulée.'],
    ];
}

$ordre      = array_keys($etapes);
$statut_actuel = $commande['statut'];
$idx_actuel = array_search($statut_actuel, $ordre);
if ($idx_actuel === false) $idx_actuel = 0;

$livreur = null;
if ($commande['livreur_id']) {
    foreach ($users_all as $u) {
        if ($u['id'] === $commande['livreur_id']) { $livreur = $u; break; }
    }
}

// Construire un index des plats par id
$plats_index = [];
foreach ($plats as $p) { $plats_index[$p['id']] = $p; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Suivi commande #<?php echo $commande['id']; ?> - Les Saveurs de Yemma</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/suivi.css">
    <style>
        .section-modif { margin: 20px 0; padding: 16px; background: #f9f9f9; border: 2px solid #b98acb; border-radius: 8px; }
        .section-modif h4 { margin-bottom: 12px; color: #b98acb; }
        .ligne-plat-modif { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; gap: 10px; }
        .ligne-plat-modif .nom-plat { flex: 1; }
        .qte-controls { display: flex; align-items: center; gap: 6px; }
        .btn-moins, .btn-plus { width: 28px; height: 28px; border: 2px solid #b98acb; background: white;
                                 color: #b98acb; border-radius: 50%; cursor: pointer; font-size: 16px; line-height: 1; }
        .btn-moins:hover, .btn-plus:hover { background: #b98acb; color: white; }
        .qte-input { width: 50px; text-align: center; padding: 4px; border: 2px solid #ddd; border-radius: 4px; }
        .total-modif-ligne { font-weight: bold; margin-top: 12px; font-size: 16px; }
        #zone-paiement-suppl { display: none; margin-top: 12px; padding: 12px; background: #fff3cd; border: 2px solid #ffc107; border-radius: 6px; }
    </style>
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
                <li><a href="../index.php">ACCUEIL</a></li>
                <li><a href="plat.php">CARTE</a></li>
                <li><a href="profil.php">PROFIL</a></li>
                <li><a href="deconnexion.php">DÉCONNEXION</a></li>
            </ul>
        </nav>
        <div class="barre">
            <button id="btn-theme" title="Changer le thème"
                style="cursor:pointer;border-radius:20px;padding:6px 14px;font-size:13px;border:2px solid #fff;background:rgba(255,255,255,.15);color:#fff;margin-right:8px;vertical-align:middle;">
                🌕 Mode sombre
            </button>
        </div>
    </div>
</header>
<main>
    <div class="suivi-card">
        <h2>Suivi — Commande #<?php echo $commande['id']; ?></h2>
        <p><strong>Type :</strong>
            <?php
            $types = ['livraison' => 'Livraison à domicile', 'emporter' => 'À emporter', 'sur_place' => 'Sur place'];
            echo $types[$commande['type']] ?? $commande['type'];
            ?>
        </p>
        <p><strong>Date :</strong> <?php echo $commande['date']; ?></p>
        <?php if ($commande['heure_souhaitee']): ?>
        <p><strong>Livraison souhaitée :</strong>
            <span class="heure-souhaitee"><?php echo $commande['heure_souhaitee']; ?></span>
        </p>
        <?php endif; ?>
        <p><strong>Montant :</strong> <span id="montant-affiche"><?php echo $commande['montant']; ?>€</span></p>

        <?php if ($commande['type'] === 'livraison' && $commande['adresse_livraison']): ?>
        <p><strong>Adresse de livraison :</strong> <?php echo $commande['adresse_livraison']; ?></p>
        <?php endif; ?>

        <?php if ($livreur): ?>
        <p><strong>Livreur :</strong> <?php echo $livreur['prenom'] . ' ' . $livreur['nom']; ?>
            — <a href="tel:<?php echo $livreur['telephone']; ?>"><?php echo $livreur['telephone']; ?></a>
        </p>
        <?php endif; ?>

        <!-- Étapes -->
        <div class="etapes">
        <?php foreach ($etapes as $code => $info): ?>
        <?php
            $idx      = array_search($code, $ordre);
            $is_abandon = ($code === 'abandonnee');
            if ($is_abandon && $statut_actuel !== 'abandonnee') continue;
            if ($statut_actuel === 'abandonnee' && $code !== 'abandonnee') {
                $classe = 'attente';
            } elseif ($idx < $idx_actuel) {
                $classe = 'fait';
            } elseif ($idx === $idx_actuel) {
                $classe = $is_abandon ? 'erreur' : 'actuel';
            } else {
                $classe = 'attente';
            }
            $icone = $is_abandon ? '✕' : ($idx < $idx_actuel ? '✓' : ($idx === $idx_actuel ? '●' : $idx + 1));
            $last  = (array_key_last($etapes) === $code);
        ?>
        <div class="etape">
            <?php if (!$last): ?>
            <div class="etape-ligne <?php echo $idx < $idx_actuel ? 'fait' : ''; ?>"></div>
            <?php endif; ?>
            <div class="etape-icon <?php echo $classe; ?>"><?php echo $icone; ?></div>
            <div class="etape-texte">
                <strong><?php echo $info[0]; ?></strong>
                <span><?php echo $info[1]; ?></span>
            </div>
        </div>
        <?php endforeach; ?>
        </div>

        <!-- Récapitulatif -->
        <div class="plats-liste">
            <h4>Récapitulatif de votre commande</h4>
            <?php foreach ($commande['plats_ids'] as $plat_id): ?>
            <?php
                $plat = $plats_index[$plat_id] ?? null;
                $qte  = intval($commande['quantites'][(string)$plat_id] ?? 1);
            ?>
            <?php if ($plat): ?>
            <div class="plat-ligne">
                <span><?php echo $plat['nom']; ?> ×<?php echo $qte; ?></span>
                <span><?php echo round($plat['prix'] * $qte, 2); ?>€</span>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
            <div class="total-ligne">
                Total : <?php echo $commande['montant']; ?>€
            </div>
        </div>

        <?php if ($statut_actuel === 'livree' && !$commande['note']): ?>
        <div class="action-center">
            <a href="notation.php?id=<?php echo $commande['id']; ?>" class="btn">
                Donner mon avis
            </a>
        </div>
        <?php elseif ($statut_actuel === 'livree' && $commande['note']): ?>
        <p class="note-donnee">
            Vous avez noté cette commande : <?php echo str_repeat('★', $commande['note']) . str_repeat('☆', 5 - $commande['note']); ?>
        </p>
        <?php endif; ?>

        <!-- ═══ Modification de commande (uniquement si en_attente) ═══ -->
        <?php if ($statut_actuel === 'en_attente'): ?>
        <div style="margin-top:20px;border-top:2px solid #b98acb;padding-top:16px;">
            <p id="modif-msg" style="display:none;padding:10px;border-radius:6px;margin-bottom:10px;"></p>

            <button id="btn-toggle-modif" class="btn" style="margin-bottom:10px;">
                Modifier la commande
            </button>

            <div id="section-modif" style="display:none;">
                <div class="section-modif">
                    <h4>Modifier les quantités</h4>
                    <p style="font-size:13px;color:#666;margin-bottom:12px;">
                        Vous pouvez modifier votre commande tant qu'elle n'est pas encore en préparation.
                        Mettez une quantité à 0 pour retirer un plat.
                    </p>

                    <form id="form-modifier-commande"
                          data-commande-id="<?php echo $commande['id']; ?>"
                          novalidate>

                        <?php foreach ($commande['plats_ids'] as $plat_id): ?>
                        <?php
                            $plat = $plats_index[$plat_id] ?? null;
                            $qte  = intval($commande['quantites'][(string)$plat_id] ?? 1);
                        ?>
                        <?php if ($plat): ?>
                        <div class="ligne-plat-modif">
                            <span class="nom-plat">
                                <?php echo htmlspecialchars($plat['nom']); ?>
                                <small style="color:#888;">(<?php echo number_format($plat['prix'], 2); ?>€/u)</small>
                            </span>
                            <div class="qte-controls">
                                <button type="button" class="btn-moins">−</button>
                                <input type="number" class="qte-input"
                                       data-plat-id="<?php echo $plat['id']; ?>"
                                       data-prix="<?php echo $plat['prix']; ?>"
                                       value="<?php echo $qte; ?>" min="0" max="99">
                                <button type="button" class="btn-plus">+</button>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php endforeach; ?>

                        <!-- Ajouter des plats supplémentaires (plats non encore dans la commande) -->
                        <?php
                        $plats_deja_ids = $commande['plats_ids'];
                        $plats_autres   = array_filter($plats, fn($p) => !in_array($p['id'], $plats_deja_ids) && $p['disponible']);
                        if (!empty($plats_autres)):
                        ?>
                        <h4 style="margin-top:16px;margin-bottom:8px;font-size:14px;">Ajouter d'autres plats :</h4>
                        <?php foreach ($plats_autres as $p): ?>
                        <div class="ligne-plat-modif">
                            <span class="nom-plat">
                                <?php echo htmlspecialchars($p['nom']); ?>
                                <small style="color:#888;">(<?php echo number_format($p['prix'], 2); ?>€/u)</small>
                            </span>
                            <div class="qte-controls">
                                <button type="button" class="btn-moins">−</button>
                                <input type="number" class="qte-input"
                                       data-plat-id="<?php echo $p['id']; ?>"
                                       data-prix="<?php echo $p['prix']; ?>"
                                       value="0" min="0" max="99">
                                <button type="button" class="btn-plus">+</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>

                        <div class="total-modif-ligne">
                            Nouveau total : <span id="total-modif">0.00 €</span>
                        </div>

                        <button type="submit" class="btn" style="margin-top:14px;">Valider les modifications</button>
                    </form>

                    <!-- Zone paiement supplémentaire -->
                    <div id="zone-paiement-suppl">
                        <p><strong>Paiement supplémentaire requis : <span class="diff-montant"></span></strong></p>
                        <p>Le supplément sera traité lors de votre prochain passage en caisse.</p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="action-center">
            <a href="profil.php" class="lien-retour">← Retour à mes commandes</a>
        </div>
    </div>
</main>
<script src="../assets/js/theme.js"></script>
<script src="../assets/js/modifier_commande.js"></script>
</body>
</html>

