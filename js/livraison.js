// livraison.js - Confirmation de livraison ou d'abandon (espace livreur)

document.addEventListener('DOMContentLoaded', function () {
    var btnLivree    = document.getElementById('btn-livree-async');
    var btnAbandonne = document.getElementById('btn-abandonne-async');
    var msgEl        = document.getElementById('livraison-msg');

    // Envoyer la confirmation au serveur
    function confirmerAction(btn, action, label) {
        var commandeId = parseInt(btn.dataset.commandeId);

        if (!confirm('Confirmer : ' + label + ' ?')) {
            return;
        }

        btn.disabled    = true;
        btn.textContent = '...';

        fetch('api/livraison_terminee.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ commande_id: commandeId, action: action })
        })
        .then(function (reponse) {
            return reponse.json();
        })
        .then(function (resultat) {
            if (resultat.success) {
                // Masquer les boutons d'action
                var actionsEl = document.getElementById('livraison-actions');
                if (actionsEl) {
                    actionsEl.style.display = 'none';
                }
                if (msgEl) {
                    msgEl.textContent   = resultat.message;
                    msgEl.className     = 'notif-succes';
                    msgEl.style.display = 'block';
                }
                // Recharger la page après 2 secondes pour afficher l'historique mis à jour
                setTimeout(function () {
                    location.reload();
                }, 2000);
            } else {
                if (msgEl) {
                    msgEl.textContent   = resultat.message;
                    msgEl.className     = 'msg-erreur';
                    msgEl.style.display = 'block';
                }
                btn.disabled    = false;
                btn.textContent = label;
            }
        })
        .catch(function () {
            if (msgEl) {
                msgEl.textContent   = 'Erreur réseau. Veuillez réessayer.';
                msgEl.className     = 'msg-erreur';
                msgEl.style.display = 'block';
            }
            btn.disabled    = false;
            btn.textContent = label;
        });
    }

    if (btnLivree) {
        btnLivree.addEventListener('click', function () {
            confirmerAction(btnLivree, 'livree', 'Livraison terminée');
        });
    }

    if (btnAbandonne) {
        btnAbandonne.addEventListener('click', function () {
            confirmerAction(btnAbandonne, 'abandonnee', 'Abandonner');
        });
    }
});
