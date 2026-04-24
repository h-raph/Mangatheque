/* --- BOUTON AJOUTER --- */
const ajout = document.getElementById("ajout");
const fermer = document.getElementById("fermer");
const overlayAjout = document.getElementById("overlayAjout");

ajout.addEventListener("click", function () {
    document.getElementById("overlayAjout").style.display = "block";
})

fermer.addEventListener("click", function () {
    document.getElementById("overlayAjout").style.display = "none";
})

// Fermer quand on clique en dehors du popup (sur l'overlay)
if (overlayAjout) {
    overlayAjout.addEventListener('click', (e) => {
        if (e.target === overlayAjout) {
            overlayAjout.style.display = 'none';
        }
    });
}

/* --- TITRE DES MANGAS --- */
let mangaTitre = [];

// remplir le tableau (au lieu d'écraser la variable) et l'exposer si besoin
document.querySelectorAll('.content h2').forEach(h2 => {
    mangaTitre.push(h2.textContent.trim());
});
console.log(mangaTitre)


/* --- TRI --- */
document.querySelectorAll('#tri option').forEach(tri => {
    const triTexte = tri.textContent.trim();
    if (triTexte === 'A-Z') {
    console.log(mangaTitre.sort())
    }

})


/* --- STATUS: overlays uniques par carte --- */
// Remplacer l'overlay par un menu contextuel `.statusMenu` local à chaque carte
function closeAllStatusMenusExcept(exceptMenu = null) {
    document.querySelectorAll('.statusMenu').forEach(m => {
        if (m !== exceptMenu) m.style.display = 'none';
    });
}

document.querySelectorAll('.status').forEach(status => {
    // On sélectionne la div avec la class 'card' puisque que c'est la plus proche
    const card = status.closest('.cards');
    if (!card) return;

    const menu = card.querySelector('.statusMenu');
    if (!menu) return;

    status.addEventListener('click', (e) => {
        e.stopPropagation();
        // fermer les autres menus
        closeAllStatusMenusExcept(menu);

        // positionner le menu juste sous le bouton status (position relative à .cards)
        const top = status.offsetTop + status.offsetHeight;
        const left = status.offsetLeft;
        menu.style.top = top + 'px';
        menu.style.left = left + 'px';

        menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
    });

    // Gérer la sélection d'un item du menu
    menu.querySelectorAll('li[data-status]').forEach(item => {
        // Quand un item est cliquer
        item.addEventListener('click', (ev) => {
            ev.stopPropagation();
            // Récupère la valeur de l'attribut data-status, et si elle est absente, prend le texte visible
            const chosen = item.dataset.status || item.textContent.trim();
            // Normaliser en classe css (supprimer diacritiques, espaces -> -, garder alphanum et '-')
            const cls = chosen.normalize('NFD').replace(/\p{Diacritic}/gu, '').toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');

            // Enlever la classe précédente si on l'a stockée
            const prev = card.dataset.statusClass;
            if (prev) card.classList.remove(prev);

            // On ajoute une class à la div '.card'
            card.classList.add(cls);
            card.dataset.statusClass = cls;
            card.dataset.status = chosen;

            const prevStats = status.dataset.statusClass;
            if (prevStats) status.classList.remove(prevStats);
            status.classList.add(cls);
            status.dataset.statusClass = cls;
            status.dataset.status = chosen;


            // fermer le menu
            menu.style.display = 'none';
        });
        // accessibilité clavier
        item.addEventListener('keydown', (ev) => {
            if (ev.key === 'Enter' || ev.key === ' ') {
                ev.preventDefault();
                item.click();
            }
        });
    });

    // permettre de fermer si on clique sur le fond du menu (rare ici)
    menu.addEventListener('click', (e) => {
        if (e.target === menu) menu.style.display = 'none';
    });
});

// fermer les menus quand on clique n'importe où en dehors
document.addEventListener('click', (e) => {
    if (!e.target.closest('.status') && !e.target.closest('.statusMenu')) {
        closeAllStatusMenusExcept(null);
    }
});

// Escape ferme également
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeAllStatusMenusExcept(null);
});