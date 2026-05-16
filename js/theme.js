// theme.js - Gestion du thème visuel (mode défaut / sombre / accessibilité)
// Le thème choisi est sauvegardé dans le localStorage du navigateur.

function getCheminBase() {
    if (window.location.pathname.indexOf('/vues/') !== -1) {
        return '../';
    }
    return '';
}

function appliquerTheme(theme) {
    // Supprimer l'ancien thème s'il existe
    var lienActuel = document.getElementById('theme-link');
    if (lienActuel) {
        lienActuel.remove();
    }

    var base = getCheminBase();

    // Injecter la feuille de style du thème choisi
    if (theme === 'sombre') {
        var lienSombre = document.createElement('link');
        lienSombre.id   = 'theme-link';
        lienSombre.rel  = 'stylesheet';
        lienSombre.href = base + 'assets/dark.css';
        document.head.appendChild(lienSombre);
    } else if (theme === 'accessibilite') {
        var lienAccess = document.createElement('link');
        lienAccess.id   = 'theme-link';
        lienAccess.rel  = 'stylesheet';
        lienAccess.href = base + 'assets/accessibilite.css';
        document.head.appendChild(lienAccess);
    }

    // Mettre à jour le texte du bouton
    var btn = document.getElementById('btn-theme');
    if (btn) {
        if (theme === 'defaut') {
            btn.textContent = 'Mode sombre';
        } else if (theme === 'sombre') {
            btn.textContent = 'Accessibilité';
        } else {
            btn.textContent = 'Mode défaut';
        }
    }
}

function prochainTheme(courant) {
    if (courant === 'defaut') {
        return 'sombre';
    } else if (courant === 'sombre') {
        return 'accessibilite';
    } else {
        return 'defaut';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    // Lire le thème sauvegardé
    var theme = localStorage.getItem('theme_yemma');
    if (theme !== 'sombre' && theme !== 'accessibilite') {
        theme = 'defaut';
    }

    appliquerTheme(theme);

    // Clic sur le bouton : passer au thème suivant
    var btn = document.getElementById('btn-theme');
    if (btn) {
        btn.addEventListener('click', function () {
            var courant = localStorage.getItem('theme_yemma');
            if (courant !== 'sombre' && courant !== 'accessibilite') {
                courant = 'defaut';
            }
            var suivant = prochainTheme(courant);
            localStorage.setItem('theme_yemma', suivant);
            appliquerTheme(suivant);
        });
    }
});
