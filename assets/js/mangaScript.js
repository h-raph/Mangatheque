// ================== FORMULAIRE AJOUT MANGA ==================
const show = document.getElementById("show");
const block = document.getElementById("add");

show.addEventListener("click", function (e) {
    e.stopPropagation();
    block.style.display = "block";
    show.style.display = "none"
});


// Cache mon formulaire quand j'appui en dehors de la zone
document.addEventListener('click', (event) => {
    // Vérifie si le clic s'est fait en dehors de la zone
    if (!block.contains(event.target)) {
        block.style.display = 'none';
        if (show.style.display !== 'block' || show.style.display == ' ') {
            console.log('ça fonctionne toute de même')
            show.style.display = "block";
        }
    }
});

// ================== FILTRE ==================
const filter = document.querySelector('.filter');

// Cache mon formulaire quand j'appui en dehors de la zone
document.addEventListener('click', (event) => {
    // Vérifie si le clic s'est fait en dehors de la zone
    if (!filter.contains(event.target) && filter.open) {
        filter.open = false;
    }
});

/* ================== STATUS: overlays uniques par carte ================== */
// # Remplacer l'overlay par un menu contextuel `.show-manga__status-menu` local à chaque carte
function closeAllStatusMenusExcept(exceptMenu = null) {
    document.querySelectorAll('.show-manga__status-menu').forEach(m => {
        if (m !== exceptMenu) m.style.display = 'none';
    });
}

document.querySelectorAll('.show-manga__status').forEach(status => {
    // On sélectionne la div avec la class 'card' puisque que c'est la plus proche
    const card = status.closest('.show-manga__cards');
    if (!card) return;

    const menu = card.querySelector('.show-manga__status-menu');
    if (!menu) return;

    status.addEventListener('click', (e) => {
        e.stopPropagation();
        // fermer les autres menus
        closeAllStatusMenusExcept(menu);

        // positionner le menu juste sous le bouton status (position relative à .show-manga__cards)
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
            // Transforme le texte brute (ex : En cours) en un nom de classe CSS propre (ex : en-cours)
            const cls = chosen.normalize('NFD').replace(/\p{Diacritic}/gu, '').toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');

            // Grâce à l'attribut "dataset.statusClass" (qui correspond en HTML à "data-status-class="..."), le script va supprimer le nom de la précédente classe de statut
            const prev = card.dataset.statusClass;
            if (prev) card.classList.remove(prev);

            // On ajoute une class à la div '.card'
            card.classList.add(cls);
            card.dataset.statusClass = cls;
            card.dataset.status = chosen;

            const newClass = 'show-manga__status--' + cls
            const prevStats = status.dataset.statusClass;
            if (prevStats) status.classList.remove(prevStats);
            if ('show-manga__status--' + prevStats) {
                status.classList.replace('show-manga__status--' + prev, newClass)
            } else {
                status.classList.add(newClass);
            };

            status.dataset.statusClass = cls;
            status.dataset.status = chosen;


            // fermer le menu
            menu.style.display = 'none';
        });
    });

    // permettre de fermer si on clique sur le fond du menu (rare ici)
    menu.addEventListener('click', (e) => {
        if (e.target === menu) menu.style.display = 'none';
    });
});

// fermer les menus quand on clique n'importe où en dehors
document.addEventListener('click', (e) => {
    if (!e.target.closest('.show-manga__status') && !e.target.closest('.show-manga__status-menu')) {
        closeAllStatusMenusExcept(null);
    }
});