// profil.js - Modification du profil utilisateur sans rechargement de page

function afficherErreur(champ, message) {
    champ.style.borderColor = '#c0392b';
    var erreur = champ.parentElement.querySelector('.err-msg');
    if (!erreur) {
        erreur = document.createElement('span');
        erreur.className = 'err-msg';
        erreur.style.color = '#c0392b';
        erreur.style.fontSize = '13px';
        erreur.style.display = 'block';
        champ.parentElement.appendChild(erreur);
    }
    erreur.textContent = message;
}

function effacerErreur(champ) {
    champ.style.borderColor = '';
    var erreur = champ.parentElement.querySelector('.err-msg');
    if (erreur) {
        erreur.textContent = '';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    var btnModifier = document.getElementById('btn-modifier-profil');
    var formEdit    = document.getElementById('form-edit-profil');
    var btnAnnuler  = document.getElementById('btn-annuler-profil');
    var viewSection = document.getElementById('view-profil');
    var msgEl       = document.getElementById('profil-msg');

    if (!btnModifier || !formEdit) return;

    // Afficher le formulaire de modification
    btnModifier.addEventListener('click', function () {
        viewSection.style.display = 'none';
        formEdit.style.display    = 'block';
        btnModifier.style.display = 'none';
    });

    // Annuler et revenir à l'affichage
    if (btnAnnuler) {
        btnAnnuler.addEventListener('click', function () {
            viewSection.style.display = 'block';
            formEdit.style.display    = 'none';
            btnModifier.style.display = 'inline-block';
            if (msgEl) {
                msgEl.textContent = '';
                msgEl.className   = '';
            }
        });
    }

    // Soumission du formulaire de modification
    formEdit.addEventListener('submit', function (e) {
        e.preventDefault();

        var champNom     = formEdit.querySelector('[name="nom"]');
        var champPrenom  = formEdit.querySelector('[name="prenom"]');
        var champTel     = formEdit.querySelector('[name="telephone"]');
        var champAdresse = formEdit.querySelector('[name="adresse"]');

        var ok = true;

        if (!champNom.value.trim() || champNom.value.trim().length < 2) {
            afficherErreur(champNom, 'Le nom doit contenir au moins 2 caractères.');
            ok = false;
        } else {
            effacerErreur(champNom);
        }

        if (!champPrenom.value.trim() || champPrenom.value.trim().length < 2) {
            afficherErreur(champPrenom, 'Le prénom doit contenir au moins 2 caractères.');
            ok = false;
        } else {
            effacerErreur(champPrenom);
        }

        if (!ok) return;

        // Lire tous les champs du formulaire
        var donnees = {};
        var champsDuFormulaire = formEdit.querySelectorAll('input, select, textarea');
        champsDuFormulaire.forEach(function (champ) {
            if (champ.name) {
                donnees[champ.name] = champ.value;
            }
        });

        var btnSubmit = formEdit.querySelector('[type="submit"]');
        btnSubmit.disabled    = true;
        btnSubmit.textContent = 'Enregistrement...';

        fetch('api/update_profil.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(donnees)
        })
        .then(function (reponse) {
            return reponse.json();
        })
        .then(function (resultat) {
            if (resultat.success) {
                // Mettre à jour les informations affichées
                document.getElementById('view-nom').textContent     = resultat.user.nom;
                document.getElementById('view-prenom').textContent  = resultat.user.prenom;
                document.getElementById('view-tel').textContent     = resultat.user.telephone || '—';
                document.getElementById('view-adresse').textContent = resultat.user.adresse || 'Non renseignée';

                if (msgEl) {
                    msgEl.textContent = resultat.message;
                    msgEl.className   = 'notif-succes';
                }
                viewSection.style.display = 'block';
                formEdit.style.display    = 'none';
                btnModifier.style.display = 'inline-block';
            } else {
                if (msgEl) {
                    msgEl.textContent = resultat.message;
                    msgEl.className   = 'msg-erreur';
                }
            }
            btnSubmit.disabled    = false;
            btnSubmit.textContent = 'Enregistrer';
        })
        .catch(function () {
            if (msgEl) {
                msgEl.textContent = 'Erreur réseau. Veuillez réessayer.';
                msgEl.className   = 'msg-erreur';
            }
            btnSubmit.disabled    = false;
            btnSubmit.textContent = 'Enregistrer';
        });
    });
});
