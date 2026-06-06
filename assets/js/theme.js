// theme.js - Gestion du thème visuel (mode défaut / sombre / accessibilité)
// Le thème choisi est sauvegardé dans le localStorage du navigateur.

function getCheminBase() {
    if (window.location.pathname.indexOf('/vues/') !== -1) {
        return '../';
    }
    return '';
}

function appliquerTheme(theme) {
    var lienActuel = document.getElementById('theme-link');
    if (lienActuel) {
        lienActuel.remove();
    }

    var base = getCheminBase();

    if (theme === 'sombre') {
        var lienSombre = document.createElement('link');
        lienSombre.id   = 'theme-link';
        lienSombre.rel  = 'stylesheet';
        lienSombre.href = base + 'assets/dark.css';
        document.head.appendChild(lienSombre);
    }

    var btn = document.getElementById('btn-theme');
    if (btn) {
        btn.textContent = theme === 'sombre' ? '☀️ Mode normal' : '🌕 Mode sombre';
    }
}

function prochainTheme(courant) {
    return courant === 'sombre' ? 'defaut' : 'sombre';
}

document.addEventListener('DOMContentLoaded', function () {
    var theme = localStorage.getItem('theme_yemma');
    if (theme !== 'sombre') {
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
