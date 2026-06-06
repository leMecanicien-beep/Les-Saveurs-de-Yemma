// validation.js - Vérification des formulaires (connexion et inscription)

// Afficher un message d'erreur sous un champ
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

// Effacer le message d'erreur d'un champ
function effacerErreur(champ) {
    champ.style.borderColor = '';
    var erreur = champ.parentElement.querySelector('.err-msg');
    if (erreur) {
        erreur.textContent = '';
    }
}

// Vérification basique du format email
function emailEstValide(email) {
    return email.indexOf('@') !== -1 && email.indexOf('.') !== -1 && email.length > 5;
}

// Ajouter un bouton pour afficher / masquer le mot de passe
function ajouterBoutonMdp(champMdp) {
    var bouton = document.createElement('button');
    bouton.type = 'button';
    bouton.textContent = '👁';
    bouton.title = 'Afficher / masquer';
    bouton.style.marginLeft = '8px';
    bouton.style.background = 'none';
    bouton.style.border = 'none';
    bouton.style.cursor = 'pointer';
    bouton.style.fontSize = '18px';
    champMdp.parentElement.appendChild(bouton);

    bouton.addEventListener('click', function () {
        if (champMdp.type === 'password') {
            champMdp.type = 'text';
        } else {
            champMdp.type = 'password';
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {

    // ---- Formulaire de connexion ----
    var formConnexion = document.getElementById('form-connexion');
    if (formConnexion) {
        var emailConnexion = formConnexion.querySelector('[name="email"]');
        var mdpConnexion   = formConnexion.querySelector('[name="mot_de_passe"]');

        if (mdpConnexion) {
            ajouterBoutonMdp(mdpConnexion);
        }

        formConnexion.addEventListener('submit', function (e) {
            var ok = true;

            if (!emailConnexion.value.trim()) {
                afficherErreur(emailConnexion, "L'adresse e-mail est requise.");
                ok = false;
            } else if (!emailEstValide(emailConnexion.value.trim())) {
                afficherErreur(emailConnexion, "Format d'e-mail invalide.");
                ok = false;
            } else {
                effacerErreur(emailConnexion);
            }

            if (!mdpConnexion.value) {
                afficherErreur(mdpConnexion, 'Le mot de passe est requis.');
                ok = false;
            } else {
                effacerErreur(mdpConnexion);
            }

            if (!ok) {
                e.preventDefault();
            }
        });
    }

    // ---- Formulaire d'inscription ----
    var formInscription = document.getElementById('form-inscription');
    if (formInscription) {
        var champNom     = formInscription.querySelector('[name="nom"]');
        var champPrenom  = formInscription.querySelector('[name="prenom"]');
        var champTel     = formInscription.querySelector('[name="telephone"]');
        var champEmail   = formInscription.querySelector('[name="email"]');
        var champMdp     = formInscription.querySelector('[name="mot_de_passe"]');
        var champConfirm = formInscription.querySelector('[name="confirmer"]');

        if (champMdp)     ajouterBoutonMdp(champMdp);
        if (champConfirm) ajouterBoutonMdp(champConfirm);

        formInscription.addEventListener('submit', function (e) {
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

            if (!champTel.value.trim()) {
                afficherErreur(champTel, 'Le numéro de téléphone est requis.');
                ok = false;
            } else {
                effacerErreur(champTel);
            }

            if (!champEmail.value.trim()) {
                afficherErreur(champEmail, "L'adresse e-mail est requise.");
                ok = false;
            } else if (!emailEstValide(champEmail.value.trim())) {
                afficherErreur(champEmail, "Format d'e-mail invalide.");
                ok = false;
            } else {
                effacerErreur(champEmail);
            }

            if (!champMdp.value || champMdp.value.length < 6) {
                afficherErreur(champMdp, 'Le mot de passe doit contenir au moins 6 caractères.');
                ok = false;
            } else {
                effacerErreur(champMdp);
            }

            if (!champConfirm.value || champConfirm.value !== champMdp.value) {
                afficherErreur(champConfirm, 'Les mots de passe ne correspondent pas.');
                ok = false;
            } else {
                effacerErreur(champConfirm);
            }

            if (!ok) {
                e.preventDefault();
            }
        });
    }
});
