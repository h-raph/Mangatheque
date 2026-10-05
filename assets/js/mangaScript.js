// ################## FORMULAIRE AJOUT MANGA ##################
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
        show.style.display = "block"
    }
});