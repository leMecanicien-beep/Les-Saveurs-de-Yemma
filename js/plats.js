var platsCourants = []; // Stocke les plats chargés depuis le serveur

// Protéger le texte contre les injections HTML
function securiserTexte(texte) {
    var div = document.createElement('div');
    div.textContent = texte;
    return div.innerHTML;
}

// Générer le HTML d'une carte plat
function genererHtmlPlat(plat, estConnecte) {
    var html = '<div class="aliment" data-id="' + plat.id + '">';
    html += '<h3>' + securiserTexte(plat.nom) + '</h3>';
    html += '<p>' + securiserTexte(plat.description) + '</p>';

    if (plat.allergenes && plat.allergenes.length > 0) {
        html += '<p class="allergenes">Allergènes : ' + securiserTexte(plat.allergenes.join(', ')) + '</p>';
    }

    html += '<span class="prix">' + plat.prix.toFixed(2) + ' €</span>';

    if (estConnecte) {
        html += '<a href="panier.php?ajouter=' + plat.id + '" class="btn">Ajouter au panier</a>';
    }

    html += '</div>';
    return html;
}

// Afficher la liste des plats dans la page
function afficherPlats(plats) {
    var container = document.getElementById('plats-container');
    if (!container) return;

    var estConnecte = document.body.dataset.loggedIn === '1';

    if (plats.length === 0) {
        container.innerHTML = '<p class="aucun-plat">Aucun plat ne correspond à vos critères.</p>';
        return;
    }

    var html = '';
    for (var i = 0; i < plats.length; i++) {
        html += genererHtmlPlat(plats[i], estConnecte);
    }
    container.innerHTML = html;
}

// Trier un tableau de plats selon le critère choisi
function trierPlats(plats) {
    var selectTri = document.getElementById('select-tri');
    var tri = selectTri ? selectTri.value : '';

    var copie = plats.slice(); // copie du tableau pour ne pas modifier l'original

    if (tri === 'prix_asc') {
        copie.sort(function (a, b) { return a.prix - b.prix; });
    } else if (tri === 'prix_desc') {
        copie.sort(function (a, b) { return b.prix - a.prix; });
    } else if (tri === 'nom_az') {
        copie.sort(function (a, b) { return a.nom.localeCompare(b.nom); });
    } else if (tri === 'nom_za') {
        copie.sort(function (a, b) { return b.nom.localeCompare(a.nom); });
    }

    return copie;
}

// Charger les plats depuis le serveur selon les filtres actifs
function chargerPlats() {
    var url = 'api/plats.php?';

    // Filtre par catégorie
    var btnActif = document.querySelector('.filtre-cat.actif');
    if (btnActif) {
        url += 'categorie=' + btnActif.dataset.val + '&';
    }

    // Filtre par texte de recherche
    var inputRecherche = document.getElementById('input-recherche');
    if (inputRecherche && inputRecherche.value.trim()) {
        url += 'recherche=' + encodeURIComponent(inputRecherche.value.trim()) + '&';
    }

    // Filtre par allergènes à exclure
    var allergenesExclus = [];
    var checkboxes = document.querySelectorAll('.cb-allergene:checked');
    checkboxes.forEach(function (cb) {
        allergenesExclus.push(cb.value);
    });
    if (allergenesExclus.length > 0) {
        url += 'allergenes=' + allergenesExclus.join(',');
    }

    var msgEl = document.getElementById('plats-msg');
    if (msgEl) msgEl.textContent = 'Chargement...';

    fetch(url)
        .then(function (reponse) {
            return reponse.json();
        })
        .then(function (plats) {
            platsCourants = plats;
            var platsTriés = trierPlats(platsCourants);
            afficherPlats(platsTriés);
            if (msgEl) msgEl.textContent = platsTriés.length + ' plat(s) affiché(s)';
        })
        .catch(function () {
            if (msgEl) msgEl.textContent = 'Erreur lors du chargement des plats.';
        });
}

document.addEventListener('DOMContentLoaded', function () {

    // Boutons de filtre par catégorie
    var boutonsCategorie = document.querySelectorAll('.filtre-cat');
    boutonsCategorie.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            boutonsCategorie.forEach(function (b) {
                b.classList.remove('actif');
            });
            btn.classList.add('actif');
            chargerPlats();
        });
    });

    // Barre de recherche textuelle
    var inputRecherche = document.getElementById('input-recherche');
    if (inputRecherche) {
        inputRecherche.addEventListener('input', function () {
            chargerPlats();
        });
        var formRecherche = inputRecherche.closest('form');
        if (formRecherche) {
            formRecherche.addEventListener('submit', function (e) {
                e.preventDefault();
            });
        }
    }

    // Filtres allergènes
    var checkboxesAllergenes = document.querySelectorAll('.cb-allergene');
    checkboxesAllergenes.forEach(function (cb) {
        cb.addEventListener('change', chargerPlats);
    });

    // Tri (s'applique sur les plats déjà chargés)
    var selectTri = document.getElementById('select-tri');
    if (selectTri) {
        selectTri.addEventListener('change', function () {
            var platsTriés = trierPlats(platsCourants);
            afficherPlats(platsTriés);
            var msgEl = document.getElementById('plats-msg');
            if (msgEl) msgEl.textContent = platsTriés.length + ' plat(s) affiché(s)';
        });
    }

    // Chargement initial de la page
    chargerPlats();
});
