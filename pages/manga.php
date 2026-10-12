<?php
// Ouverture session et connexion à la base de donnée
require_once '../partials/_config.php';

// ==================== Traitement du formulaire POST ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // # ============= Traitement de l'envoie des mangas =============
    // ## Récupère les données dans le formualire POST
    $titre = htmlspecialchars(trim($_POST['titre'] ?? ''));
    $auteur = htmlspecialchars(trim($_POST['auteur'] ?? ''));
    $demographie = htmlspecialchars(trim($_POST['demographie'] ?? ''));
    $langage = htmlspecialchars(trim($_POST['langage'] ?? ''));
    $genresSaisis = array_map('trim', explode(',', $_POST['genre'] ?? ''));
    print_r($genresSaisis);

    // ## Envoie les données dans la table manga
    $stmt_manga = $pdo->prepare("INSERT INTO `manga` (`titre`, `auteur`, `demographie`, `langage`) VALUES (:titre, :auteur, :demographie, :langage)");
    $stmt_manga->bindValue(':titre', $titre, PDO::PARAM_STR);
    $stmt_manga->bindValue(':auteur', $auteur, PDO::PARAM_STR);
    $stmt_manga->bindValue(':demographie', $demographie, PDO::PARAM_STR);
    $stmt_manga->bindValue(':langage', $langage, PDO::PARAM_STR);
    $stmt_manga->execute();
    $manga_id = $pdo->lastInsertId();

    // # ============= Traitement des genres =============
    // ## Récupère l'id correspondant au genre
    $genresArray_id = [];
    foreach ($genresSaisis as $genre_nom) {
        $stmt_genres = $pdo->prepare("SELECT id_genre FROM genre WHERE genres = :genres");
        $stmt_genres->bindValue(':genres', $genre_nom, PDO::PARAM_STR);
        $stmt_genres->execute();
        $genre_id = $stmt_genres->fetch(PDO::FETCH_ASSOC);
        $genresArray_id[] = $genre_id;
    }

    // ============= Traitement de la liaison entre les mangas et les genres =============
    // ## Fait la liaison entre le mangas et les genres
    $stmt_liaison = $pdo->prepare("INSERT INTO `association_manga_genre` (`id_manga`, `id_genre`) VALUES (:id_manga, :id_genre)");

    // ## Insère une ligne pour chaque genre du manga
    foreach ($genresArray_id as $id_du_genre) {
        $stmt_liaison->bindValue(':id_manga', $manga_id, PDO::PARAM_INT);
        $stmt_liaison->bindValue(':id_genre', $id_du_genre['id_genre'], PDO::PARAM_INT);
        $stmt_liaison->execute();
    }

    // # Redirection PRG pour empêcher la double soumission au rafraîchissement
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

// # Récupère les genres pour l'input dans le formulaire
$reqGenre = $pdo->prepare("SELECT genres FROM genre");
$reqGenre->execute();
$listGenre = $reqGenre->fetchAll(PDO::FETCH_ASSOC);

// # Récupère tous les mangas sans les conditions de filtres
$stmt_manga = $pdo->prepare("SELECT * FROM manga");
$stmt_manga->execute();
$allMangas = $stmt_manga->fetchAll(PDO::FETCH_ASSOC);

// ** Récupère les filtre de langages dans l'url
$filtreLangage = $_GET['langage'] ?? [];
$strLangage = implode(', ', $filtreLangage);
echo ($strLangage);

// ** Récupère les filtre de genres dans l'url
$filtreGenre = $_GET['genres'] ?? [];

// ** Récupère les filtre de sort dans l'url
$filtreSort = $_GET['sort'] ?? '';

// * Récupère les mangas avec les conditions de filtres
foreach ($allMangas as $manga) {
    if (empty($filtreLangage) && empty($filtreGenre) && $filtreSort === '') {
        $reqManga = $pdo->prepare("SELECT * FROM manga");
        $reqManga->execute();
        $mangas = $reqManga->fetchAll(PDO::FETCH_ASSOC);
    } // Initialisation de la requête de base et des tableaux
    $sql = "SELECT DISTINCT m.* FROM manga m";
    $joins = "";
    $conditions = [];
    $params = [];

    // 1. Si le filtre langage est actif
    if (!empty($filtreLangage)) {
        $placeholdersLang = [];
        foreach ($filtreLangage as $index => $lang) {
            $key = ":lang_" . $index;
            $placeholdersLang[] = $key;
            $params[$key] = $lang;
        }
        $conditions[] = "m.langage IN (" . implode(', ', $placeholdersLang) . ")";
    }

    // 2. Si le filtre genre est actif
    if (!empty($filtreGenre)) {
        // On ajoute les jointures vers la table de liaison et de genre uniquement si nécessaire
        $joins .= " INNER JOIN association_manga_genre amg ON m.id_manga = amg.id_manga";
        $joins .= " INNER JOIN genre g ON amg.id_genre = g.id_genre";

        $placeholdersGenre = [];
        foreach ($filtreGenre as $index => $genre) {
            $key = ":genre_" . $index;
            $placeholdersGenre[] = $key;
            $params[$key] = $genre;
        }
        $conditions[] = "g.genres IN (" . implode(', ', $placeholdersGenre) . ")";
    }

    // 3. Assemblage des jointures
    $sql .= $joins;

    // 4. Assemblage des conditions WHERE s'il y en a
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }

    // 5. Préparation et exécution de la requête finale
    $reqManga = $pdo->prepare($sql);
    $reqManga->execute($params);
    $mangas = $reqManga->fetchAll(PDO::FETCH_ASSOC);
}

// # Liste des status
$listStatus = ['Vide', 'En cours', 'Terminer', 'En pause', 'A lire', 'Abandonner'];

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manga</title>

    <link rel="stylesheet" href="../assets/css/mangaStyle.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.3.2/css/flag-icons.min.css" /> <!-- importation pour les drapeaux -->

    <script src="../assets/js/mangaScript.js" defer></script>
</head>

<body>
    <header>
        <section id="add" class="add" style="display: none;">
            <form action="" method="post" class="add__form">
                <input type="text" placeholder="Titre" name="titre" class="add__form--title" required>

                <input type="text" placeholder="Auteur" name="auteur" class="add__form--autor" required>

                <select name="demographie" id="demographie" name="demographie" class="add__form--demo">
                    <option value="Shōnen">Shōnen</option>
                    <option value="Shōjo">Shōjo</option>
                    <option value="Seinen">Seinen</option>
                </select>

                <select name="langage" id="langage" name="langage" class="add__form--lang">
                    <option value="Manga">Manga</option>
                    <option value="Manhua">Manhua</option>
                    <option value="Manhwa">Manhwa</option>
                </select>

                <input type="text" name="genre" id="genre" class="add__form--genre" list="listGenre">
                <datalist id="listGenre">
                    <?php foreach ($listGenre as $genre): ?>
                        <option><?= $genre['genres']; ?></option>
                    <?php endforeach; ?>
                </datalist>

                <button class="add__form--new" type="submit">Ajouter</button>
            </form>
        </section>

        <button id="show">Nouveau manga</button>

        <section>
            <details class="filter">
                <summary class="filter__trigger">Filtrer les mangas</summary>

                <div class="filter__overlay">
                    <form action="" method="get" class="filter__form">
                        <fieldset class="filter__group">
                            <legend class="filter__title">Langage</legend>

                            <div class="filter__grid filter__grid--langage">
                                <label class="filter__item">
                                    <input type="checkbox" name="langage[]" value="manga"> Manga
                                </label>
                                <label class="filter__item">
                                    <input type="checkbox" name="langage[]" value="manhua"> Manhua
                                </label>
                                <label class="filter__item">
                                    <input type="checkbox" name="langage[]" value="manhwa"> Manhwa
                                </label>
                            </div>
                        </fieldset>

                        <fieldset class="filter__group">
                            <legend class="filter__title">Genre</legend>

                            <div class="filter__grid filter__grid--genres">
                                <?php foreach ($listGenre as $genre): ?>
                                    <label class="filter__item">
                                        <input type="checkbox" name="genres[]" value="<?= htmlspecialchars($genre['genres']); ?>">
                                        <?= htmlspecialchars($genre['genres']); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </fieldset>

                        <fieldset class="filter__group">
                            <legend class="filter__title">Tri</legend>

                            <div class="filter__grid filter__grid--sort">
                                <label class="filter__item">
                                    <input type="radio" name="sort" value="a-z"> A-Z
                                </label>
                                <label class="filter__item">
                                    <input type="radio" name="sort" value="z-a"> Z-A
                                </label>
                                <label class="filter__item">
                                    <input type="radio" name="sort" value="recent"> + Récent
                                </label>
                                <label class="filter__item">
                                    <input type="radio" name="sort" value="ancient"> + Ancient
                                </label>
                            </div>
                        </fieldset>

                        <button type="submit" class="filter__submit">Appliquer</button>
                        <button type="reset" class="filter__reset">Réinitialiser</button>
                    </form>
                </div>
            </details>
        </section>
    </header>

    <main class="show-manga">
        <?php foreach ($mangas as $manga): ?>
            <div id="<?= strtolower(str_replace(' ', '-', $manga['titre'])); ?>" class="<?= strtolower($manga['langage']); ?> show-manga__cards">

                <div class="show-manga__status-menu" aria-hidden="true" role="menu">
                    <ul class="show-manga__status-menu--list">
                        <?php foreach ($listStatus as $status): ?>
                            <li role="menuitem" tabindex="0" data-status="<?php echo htmlspecialchars($status); ?>" class="<?= strtolower(str_replace(' ', '-', htmlspecialchars($status))); ?>" class="show-manga__status-menu--list-content"><?php echo htmlspecialchars($status); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="show-manga__status" data-status="<?php echo htmlspecialchars($status); ?>"></div>

                <div class="show-manga__info">
                    <h2><?= $manga['titre'] ?></h2>
                    <h3><?= $manga['auteur'] ?></h3>
                </div>

            </div>
        <?php endforeach; ?>
    </main>
</body>

</html>