// detail_commande.js - Changement de statut et attribution d'un livreur (restaurateur)

function afficherMsg(msgEl, message, type) {
    if (!msgEl) return;
    msgEl.textContent   = message;
    msgEl.className     = type === 'succes' ? 'notif-succes' : 'msg-erreur';
    msgEl.style.display = 'block';
}

// Retourner la classe CSS correspondant à un statut
function classeStatut(statut) {
    if (statut === 'en_attente')     return 'attente';
    if (statut === 'en_preparation') return 'preparation';
    if (statut === 'prete')          return 'prete';
    if (statut === 'en_livraison')   return 'en-livraison';
    if (statut === 'livree')         return 'fini';
    if (statut === 'abandonnee')     return 'abandonnee';
    return '';
}

document.addEventListener('DOMContentLoaded', function () {
    var msgEl = document.getElementById('actions-msg');

    // ---- Changement de statut de la commande ----
    var btnStatut    = document.getElementById('btn-valider-statut');
    var selectStatut = document.getElementById('select-statut');

    if (btnStatut && selectStatut) {
        btnStatut.removeAttribute('disabled');
        selectStatut.removeAttribute('disabled');

        btnStatut.addEventListener('click', function () {
            var commandeId = parseInt(document.getElementById('commande-id').value);
            var statut     = selectStatut.value;

            btnStatut.disabled    = true;
            btnStatut.textContent = '...';

            fetch('api/statut_commande.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ commande_id: commandeId, statut: statut })
            })
            .then(function (reponse) {
                return reponse.json();
            })
            .then(function (resultat) {
                if (resultat.success) {
                    afficherMsg(msgEl, resultat.message, 'succes');
                    // Mettre à jour le badge de statut affiché
                    var badgeEl = document.getElementById('badge-statut-courant');
                    if (badgeEl) {
                        badgeEl.textContent = resultat.label;
                        badgeEl.className   = 'badge ' + classeStatut(resultat.statut);
                    }
                } else {
                    afficherMsg(msgEl, resultat.message, 'erreur');
                }
                btnStatut.disabled    = false;
                btnStatut.textContent = 'Valider le statut';
            })
            .catch(function () {
                afficherMsg(msgEl, 'Erreur réseau. Veuillez réessayer.', 'erreur');
                btnStatut.disabled    = false;
                btnStatut.textContent = 'Valider le statut';
            });
        });
    }

    // ---- Attribution d'un livreur à la commande ----
    var btnLivreur    = document.getElementById('btn-attribuer-livreur');
    var selectLivreur = document.getElementById('select-livreur');

    if (btnLivreur && selectLivreur) {
        btnLivreur.removeAttribute('disabled');
        selectLivreur.removeAttribute('disabled');

        btnLivreur.addEventListener('click', function () {
            var commandeId = parseInt(document.getElementById('commande-id').value);
            var livreurId  = parseInt(selectLivreur.value);

            if (!livreurId) {
                afficherMsg(msgEl, 'Veuillez sélectionner un livreur.', 'erreur');
                return;
            }

            btnLivreur.disabled    = true;
            btnLivreur.textContent = '...';

            fetch('api/attribuer_livreur.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ commande_id: commandeId, livreur_id: livreurId })
            })
            .then(function (reponse) {
                return reponse.json();
            })
            .then(function (resultat) {
                if (resultat.success) {
                    afficherMsg(msgEl, resultat.message, 'succes');
                    // Marquer le livreur attribué dans la liste
                    var options = selectLivreur.querySelectorAll('option');
                    options.forEach(function (opt) {
                        opt.textContent = opt.textContent.replace(' (attribué)', '');
                    });
                    var optChoisie = selectLivreur.querySelector('option[value="' + livreurId + '"]');
                    if (optChoisie) {
                        optChoisie.textContent += ' (attribué)';
                    }
                } else {
                    afficherMsg(msgEl, resultat.message, 'erreur');
                }
                btnLivreur.disabled    = false;
                btnLivreur.textContent = 'Attribuer';
            })
            .catch(function () {
                afficherMsg(msgEl, 'Erreur réseau. Veuillez réessayer.', 'erreur');
                btnLivreur.disabled    = false;
                btnLivreur.textContent = 'Attribuer';
            });
        });
    }
});
