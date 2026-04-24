<?php
// Ouverture session et connexion à la base de donnée
require_once '../partials/_config.php';

// Liste des status
$status = ['Vide', 'En cours', 'Terminer', 'En pause', 'À lire', 'Abandonner'];

// Liste des tri
$triList = ['A-Z', 'Z-A', '+ récent', '+ ancient'];

// Liste des drapeaux
$listFlag = ['jp', 'kr', 'cn'];



// ==================== Traitement du formulaire POST ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = htmlspecialchars(trim($_POST['titre'] ?? ''));
    $auteur = htmlspecialchars(trim($_POST['auteur'] ?? ''));
    $demographie = htmlspecialchars(trim($_POST['demographie'] ?? ''));
    $langage = htmlspecialchars(trim($_POST['langages'] ?? ''));
    $genre1 = htmlspecialchars(trim($_POST['genre1'] ?? ''));
    $genre2 = htmlspecialchars(trim($_POST['genre2'] ?? ''));
    $genre3 = htmlspecialchars(trim($_POST['genre3'] ?? ''));
    $theme = htmlspecialchars(trim($_POST['theme'] ?? ''));

    // Requête préparée pour éviter les injections
    $sql = "INSERT INTO `manga` (`titre`, `auteur`, `demographie`, `langage`, `genre_1`, `genre_2`, `genre_3`, `theme`)
            VALUES (:titre, :auteur, :demographie, :langage, :g1, :g2, :g3, :theme)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':titre' => $titre,
        ':auteur' => $auteur,
        ':demographie' => $demographie,
        ':langage' => $langage,
        ':g1' => $genre1,
        ':g2' => $genre2,
        ':g3' => $genre3,
        ':theme' => $theme,
    ]);

    // Redirection PRG pour empêcher la double soumission au rafraîchissement
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

// Récupération de toutes les datas
$resManga = $pdo->prepare("SELECT * FROM manga");
$resManga->execute();
$allManga = $resManga->fetchAll(PDO::FETCH_ASSOC);

// Langage filtre
$filtreLangage = $_GET['langage'] ?? '';

foreach ($allManga as $m) {
    if ($filtreLangage === 'Tout' || $filtreLangage === '') {
        $reqManga = $pdo->prepare("SELECT * FROM manga");
        $reqManga->execute();
        $mangas = $reqManga->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $reqManga = $pdo->prepare("SELECT * FROM manga WHERE langage = :langage");
        $reqManga->bindValue(':langage', $filtreLangage, PDO::PARAM_STR);
        $reqManga->execute();
        $mangas = $reqManga->fetchAll(PDO::FETCH_ASSOC);
    }
}


// Récupération des genres (sans affichage de debug)
$genres = $pdo->query("SELECT genre_1, genre_2, genre_3 FROM manga");
$resGenres = $genres->fetchAll(PDO::FETCH_ASSOC);

// Extraire les genres uniques dans un tableau simple
$genresArray = [];
foreach ($resGenres as $row) {
    foreach (['genre_1', 'genre_2', 'genre_3'] as $col) {
        $g = trim($row[$col] ?? '');
        if ($g !== '') {
            $genresArray[] = $g;
        }
    }
}

// Récupération des langages (sans affichage de debug)
$langages = $pdo->prepare("SELECT langage FROM manga");
$langages->execute();
$resLangages = $langages->fetchAll(PDO::FETCH_ASSOC);

// Extraire les langages uniques dans un tableau simple et les normaliser
$langagesArray = [];
foreach ($resLangages as $row) {
    $l = trim($row['langage'] ?? '');
    if ($l !== '') {
        $langagesArray[] = $l;
    }
}

// dédupliquer et trier (faire avant tout affichage)
if (!empty($langagesArray)) {
    natcasesort($langagesArray);
    $langagesArray = array_values(array_unique($langagesArray));
} else {
    $langagesArray = [];
}

// dédupliquer et réindexer
if (!empty($genresArray)) {
    natcasesort($genresArray); // tri sans tenir compte de la casse
    $genresArray = array_values(array_unique($genresArray));
} else {
    $genresArray = [];
}
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
        <!-- Ajouter un livre -->
        <button id="ajout">Ajouter un livre</button>

        <div id="overlayAjout">
            <form id="popup" method="POST">
                <input type="text" name="titre" id="titre" placeholder="Titre">
                <input type="text" name="auteur" id="auteur" placeholder="Auteur">

                <select name="demographie" id="demographie">
                    <option value="Shounen">Shounen</option>
                    <option value="Shojo">Shojo</option>
                    <option value="Seinen">Seinen</option>
                    <option value="Josei">Josei</option>
                </select>

                <select name="langages" id="langages">
                    <option value="Manga">Manga</option>
                    <option value="Manhua">Manhua</option>
                    <option value="Manhwa">Manhwa</option>
                </select>

                <input type="text" name="genre1" id="genre1" placeholder="Genre n°1">
                <input type="text" name="genre2" id="genre2" placeholder="Genre n°2">
                <input type="text" name="genre3" id="genre3" placeholder="Genre n°3">

                <input type="text" name="theme" id="theme" placeholder="Thême">

                <button type="submit" id="fermer">Ajouter</button>
            </form>
        </div>
    </header>

    <main>
        <!-- Filtre -->
        <section id="filtre">
            <!-- Langage -->
            <div id="filtre-langage">
                <p for="langage">Langages</p>
                <?php if (!empty($langagesArray)): ?>
                    <a href="?langage=<?= "Tout" ?>" class="filtre-link">Tout</a>
                    <?php foreach ($langagesArray as $l): ?>
                        <?php
                        if ($l === "Manga") {
                            $flag = "<span class='fi fi-jp'></span>";
                        } elseif ($l === "Manhwa") {
                            $flag = "<span class='fi fi-kr'></span>";
                        } elseif ($l === "Manhua") {
                            $flag = "<span class='fi fi-cn'></span>";
                        }
                        ?>
                        <a href="?langage=<?= $l ?>" class="filtre-link"><?= $flag . $l ?></a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="">Aucun langage</option>
                <?php endif; ?>
            </div>

            <!-- Genre -->
            <div id="filtre-genre">
                <form method="GET">
                    <label for="genre">Genres</label>
                    <select name="genre" id="genre" onchange="this.form.submit()">
                        <?php if (!empty($genresArray)): ?>
                            <option value="Tout">-- Tout --</option>
                            <?php foreach ($genresArray as $g): ?>
                                <?php $select = $selectGenre === $g ? 'selected' : '' ?>
                                <option value="<?php echo $g; ?>" <?= $select ?>>
                                    <?php echo $g; ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">Aucun genre</option>
                        <?php endif; ?>
                    </select>
            </div>

            <!-- Tri -->
            <label for="tri">Tri</label>
            <select name="tri" id="tri" onchange="this.form.submit()">
                <option value="">-- --</option>
                <?php foreach ($triList as $t): ?>
                    <?php $select = $tri === $t ? 'selected' : '' ?>
                    <a><option value="<?php echo $t; ?>" <?= $select ?>>
                        <?php echo $t; ?>
                    </option></a>
                <?php endforeach; ?>
            </select>
            </form>
        </section>

        <!-- Tableau -->
        <section id="tableau">
            <?php foreach ($mangas as $m): ?>
                <div id="<?= strtolower(str_replace(' ', '-', $m['titre'])); ?>" class="<?= strtolower($m['langage']); ?> cards">
                    <!-- <img src="" alt=""> -->

                    <div class="statusMenu" aria-hidden="true" role="menu">
                        <ul>
                            <?php foreach ($status as $s): ?>
                                <li role="menuitem" tabindex="0" data-status="<?php echo htmlspecialchars($s); ?>" class="<?= strtolower(str_replace(' ', '-', htmlspecialchars($s))); ?>"><?php echo htmlspecialchars($s); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <div class="status" data-status="<?php echo htmlspecialchars($s); ?>"></div>

                    <div class="content">
                        <h2><?= $m['titre'] ?></h2>
                        <h3><?= $m['auteur'] ?></h3>
                        <div class="genre">
                            <p class="<?= strtolower($m['genre_1']) ?? ''; ?>"> <?= $m['genre_1'] ?? '' ?></p>
                            <p class="<?= strtolower($m['genre_2']) ?? ''; ?>"> <?= $m['genre_2'] ?? '' ?></p>
                            <p class="<?= strtolower($m['genre_3']) ?? ''; ?>"> <?= $m['genre_3'] ?? '' ?></p>
                        </div>
                        <div class="theme">
                            <p class="<?= strtolower($m['theme']) ?? ''; ?>"><?= $m['theme'] ?? '' ?></p>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </section>
    </main>
</body>

</html>