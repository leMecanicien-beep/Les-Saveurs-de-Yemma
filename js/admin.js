// admin.js - Blocage et déblocage des utilisateurs (espace admin)

// Afficher un message temporaire en bas de l'écran
function afficherMessage(message, type) {
    var toast = document.getElementById('admin-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'admin-toast';
        toast.style.position      = 'fixed';
        toast.style.bottom        = '30px';
        toast.style.right         = '30px';
        toast.style.padding       = '14px 22px';
        toast.style.borderRadius  = '8px';
        toast.style.fontSize      = '15px';
        toast.style.zIndex        = '9999';
        toast.style.color         = '#fff';
        document.body.appendChild(toast);
    }

    toast.textContent = message;
    if (type === 'succes') {
        toast.style.backgroundColor = '#27ae60';
    } else {
        toast.style.backgroundColor = '#c0392b';
    }
    toast.style.display = 'block';

    setTimeout(function () {
        toast.style.display = 'none';
    }, 3500);
}

document.addEventListener('DOMContentLoaded', function () {
    var boutons = document.querySelectorAll('.btn-bloquer');

    // Modifier statut (Standard / VIP / Premium)
    document.querySelectorAll('.btn-statut').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var userId  = parseInt(btn.dataset.userId);
            var actuel  = btn.dataset.statut || 'Standard';
            var statuts = ['Standard', 'VIP', 'Premium'];
            var choix   = prompt('Choisir un statut : Standard, VIP, Premium\n(actuel : ' + actuel + ')', actuel);
            if (!choix || !statuts.includes(choix)) return;

            fetch('api/modifier_statut.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ user_id: userId, statut: choix })
            })
            .then(r => r.json())
            .then(function (res) {
                if (res.success) {
                    btn.dataset.statut = res.statut;
                    var card = btn.closest('.utilisateur');
                    if (card) {
                        var badgeZone = card.querySelector('.statut-badges');
                        if (badgeZone) {
                            var oldBadge = badgeZone.querySelector('[class*="badge-"]:not(.badge-bloque-dyn)');
                            if (oldBadge) { oldBadge.className = 'badge-statut badge-' + res.statut; oldBadge.textContent = res.statut; }
                        }
                    }
                    afficherMessage('Statut mis à jour : ' + res.statut, 'succes');
                } else {
                    afficherMessage(res.message, 'erreur');
                }
            })
            .catch(() => afficherMessage('Erreur réseau.', 'erreur'));
        });
    });

    boutons.forEach(function (btn) {
        btn.removeAttribute('disabled');

        btn.addEventListener('click', function () {
            var userId = parseInt(btn.dataset.userId);
            var bloque = btn.dataset.bloque === '1';
            var nom    = btn.dataset.nom || 'cet utilisateur';
            var action = bloque ? 'débloquer' : 'bloquer';

            if (!confirm('Voulez-vous ' + action + ' ' + nom + ' ?')) {
                return;
            }

            btn.disabled    = true;
            btn.textContent = '...';

            fetch('api/bloquer_user.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ user_id: userId })
            })
            .then(function (reponse) {
                return reponse.json();
            })
            .then(function (resultat) {
                if (resultat.success) {
                    var card = btn.closest('.utilisateur') || btn.closest('tr');

                    if (resultat.bloque) {
                        btn.textContent    = 'Débloquer';
                        btn.dataset.bloque = '1';
                        // Ajouter le badge "Bloqué" si pas déjà présent
                        if (card) {
                            var badgeZone     = card.querySelector('.statut-badges');
                            var badgeExistant = card.querySelector('.badge-bloque-dyn');
                            if (!badgeExistant && badgeZone) {
                                var badge = document.createElement('span');
                                badge.className   = 'badge-statut badge-bloque badge-bloque-dyn';
                                badge.textContent = 'Bloqué';
                                badgeZone.appendChild(badge);
                            }
                        }
                    } else {
                        btn.textContent    = 'Bloquer';
                        btn.dataset.bloque = '0';
                        // Supprimer le badge "Bloqué"
                        if (card) {
                            var badgeDyn = card.querySelector('.badge-bloque-dyn');
                            if (badgeDyn) {
                                badgeDyn.remove();
                            }
                        }
                    }

                    afficherMessage(resultat.message, 'succes');
                } else {
                    afficherMessage(resultat.message, 'erreur');
                }
                btn.disabled = false;
            })
            .catch(function () {
                afficherMessage('Erreur réseau. Veuillez réessayer.', 'erreur');
                btn.disabled = false;
            });
        });
    });
});
