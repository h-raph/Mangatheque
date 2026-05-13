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

/* --- AJOUT DES CHAMPS DE TEXTES POUR LES GENRES --- */
document.getElementById('add-genre-btn').addEventListener('click', () => {
    const container = document.getElementById('container-genres');
    const newInput = document.createElement('div');
    newInput.className = 'genre-input-group';
    newInput.innerHTML = `<input type="text" name="genre[]" placeholder="Autre genre">`;
    container.appendChild(newInput);

    if (newInput) {
        const popup = document.getElementById('popup');
        // Crée le bouton uniquement s'il n'existe pas déjà
        if (popup && !popup.querySelector('#suppr-genre-btn')) {
            const btnSuppr = document.createElement('button');
            btnSuppr.id = 'suppr-genre-btn';
            btnSuppr.type = 'button';
            btnSuppr.textContent = '- Supprimer un genre';
            popup.appendChild(btnSuppr);

            // Supprime le dernier champ ajouté (garde au moins un champ)
            btnSuppr.addEventListener('click', () => {
                const container = document.getElementById('container-genres');
                const inputs = container.querySelectorAll('.genre-input-group');
                if (inputs.length > 1) {
                    inputs[inputs.length - 1].remove();
                } else if (inputs.length === 1) {
                    inputs[0].querySelector('input').value = '';
                }
            });
        }
    }
});
/* ================================================== */

/* --- FILTRE GENRE --- */
document.querySelectorAll('.filtre-liste').forEach(liste => {
    // On colore en vert les genres qu'on a selectionné
    liste.addEventListener('click', (li) => {
        li.stopPropagation();
        const target = li.currentTarget || liste;
        if (target) {
            target.style.color = (target.style.color === "") ? 'green' : '';
            target.style.borderColor = (target.style.borderColor === "") ? 'green' : '';
            target.classList.toggle('selected-genre')
        }
    })

    // On envoie les genres selectionnés dans l'url
    const search = document.getElementById('search');
    if (search) {
        search.addEventListener('click', (sli) => {
            sli.preventDefault()
            // 1. On récupère l'URL actuelle
            const url = new URL(window.location.href);
            // 2. On nettoie les anciens paramètres "genre" pour éviter les doublons
            url.searchParams.delete('genre[]');
            // 3. On récupère tous nos éléments de genre
            document.querySelectorAll('.filtre-liste.selected-genre').forEach(gli => {
                if (gli.textContent.trim() === "Tout") {
                    url.searchParams.replace('genre[]', 'Tout')
                }
                url.searchParams.append('genre[]', gli.textContent.trim());
            })
            // 4. On met à jour l'URL (sans recharger la page)
            window.history.replaceState({}, '', url);
            console.log("Nouvelle URL : ", url.toString());
        })
    }

    // On supprimer tous les genres envoyés et selectionnés avec le bouton reset
    const reset = document.getElementById('reset');
    reset.addEventListener('click', (rli) => {
        rli.stopPropagation;
        const url = new URL(window.location.href);
        document.querySelectorAll('.filtre-liste').forEach(resetli => {
            resetli.style.color = (resetli.classList.contains === "selected-genre") ? 'black' : '';
            resetli.style.borderColor = (resetli.classList.contains === "selected-genre") ? 'black' : '';
            url.searchParams.delete('genre[]');
        })
        window.history.replaceState({}, '', url);
    })
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