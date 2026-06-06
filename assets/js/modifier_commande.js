// modifier_commande.js - Modification d'une commande en attente
// Permet de changer les quantités et met à jour le total en temps réel.

document.addEventListener('DOMContentLoaded', function () {
    var formMod   = document.getElementById('form-modifier-commande');
    var totalEl   = document.getElementById('total-modif');
    var msgEl     = document.getElementById('modif-msg');
    var btnToggle = document.getElementById('btn-toggle-modif');
    var section   = document.getElementById('section-modif');

    if (!formMod) return;

    // Afficher ou masquer le formulaire de modification
    if (btnToggle && section) {
        btnToggle.addEventListener('click', function () {
            if (section.style.display === 'none' || section.style.display === '') {
                section.style.display  = 'block';
                btnToggle.textContent  = 'Annuler la modification';
            } else {
                section.style.display  = 'none';
                btnToggle.textContent  = 'Modifier la commande';
            }
        });
    }

    // Recalculer le total selon les quantités saisies
    function mettreAJourTotal() {
        var total = 0;
        var inputs = formMod.querySelectorAll('.qte-input');
        inputs.forEach(function (input) {
            var qte  = parseInt(input.value) || 0;
            if (qte < 0) qte = 0;
            var prix = parseFloat(input.dataset.prix) || 0;
            total += qte * prix;
        });
        if (totalEl) {
            totalEl.textContent = total.toFixed(2) + ' €';
        }
    }

    // Boutons + et - pour chaque ligne de plat
    var inputs = formMod.querySelectorAll('.qte-input');
    inputs.forEach(function (input) {
        var btnMoins = input.previousElementSibling;
        var btnPlus  = input.nextElementSibling;

        if (btnMoins && btnMoins.classList.contains('btn-moins')) {
            btnMoins.addEventListener('click', function () {
                var valeur = parseInt(input.value) || 0;
                if (valeur > 0) {
                    input.value = valeur - 1;
                }
                mettreAJourTotal();
            });
        }

        if (btnPlus && btnPlus.classList.contains('btn-plus')) {
            btnPlus.addEventListener('click', function () {
                var valeur = parseInt(input.value) || 0;
                input.value = valeur + 1;
                mettreAJourTotal();
            });
        }

        input.addEventListener('input', mettreAJourTotal);
    });

    mettreAJourTotal();

    // Envoyer la modification au serveur
    formMod.addEventListener('submit', function (e) {
        e.preventDefault();

        var commandeId = parseInt(formMod.dataset.commandeId);
        var quantites  = {};

        formMod.querySelectorAll('.qte-input').forEach(function (input) {
            var qte = parseInt(input.value) || 0;
            if (qte < 0) qte = 0;
            quantites[input.dataset.platId] = qte;
        });

        var btnSubmit = formMod.querySelector('[type="submit"]');
        btnSubmit.disabled    = true;
        btnSubmit.textContent = 'Enregistrement...';

        fetch('api/modifier_commande.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ commande_id: commandeId, quantites: quantites })
        })
        .then(function (reponse) {
            return reponse.json();
        })
        .then(function (resultat) {
            if (msgEl) {
                msgEl.textContent = resultat.message;
                if (resultat.success) {
                    msgEl.className = 'notif-succes';
                } else {
                    msgEl.className = 'msg-erreur';
                }
            }

            if (resultat.success) {
                // Mettre à jour le montant affiché
                var montantEl = document.getElementById('montant-affiche');
                if (montantEl && resultat.nouveau_montant !== undefined) {
                    montantEl.textContent = resultat.nouveau_montant.toFixed(2) + ' €';
                }

                // Afficher la zone de paiement supplémentaire si besoin
                if (resultat.paiement_supplementaire) {
                    var payEl = document.getElementById('zone-paiement-suppl');
                    if (payEl) {
                        payEl.style.display = 'block';
                        var diffEl = payEl.querySelector('.diff-montant');
                        if (diffEl) {
                            diffEl.textContent = resultat.paiement_supplementaire.toFixed(2) + ' €';
                        }
                    }
                }

                // Masquer le formulaire après succès
                if (section) section.style.display = 'none';
                if (btnToggle) btnToggle.textContent = 'Modifier la commande';
            }

            btnSubmit.disabled    = false;
            btnSubmit.textContent = 'Valider les modifications';
        })
        .catch(function () {
            if (msgEl) {
                msgEl.textContent = 'Erreur réseau. Veuillez réessayer.';
                msgEl.className   = 'msg-erreur';
            }
            btnSubmit.disabled    = false;
            btnSubmit.textContent = 'Valider les modifications';
        });
    });
});
