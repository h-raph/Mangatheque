<?php
// Ouverture session et connexion à la base de donnée
require_once '../partials/_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupère les données dans le formualire POST
    $titre = htmlspecialchars(trim($_POST['titre'] ?? ''));
    $auteur = htmlspecialchars(trim($_POST['auteur'] ?? ''));
    $demographie = htmlspecialchars(trim($_POST['demographie'] ?? ''));
    $langage = htmlspecialchars(trim($_POST['langage'] ?? ''));

    // Envoie les données dans la table manga
    $stmt_manga = $pdo->prepare("INSERT INTO `manga` (`titre`, `auteur`, `demographie`, `langage`) VALUES (:titre, :auteur, :demographie, :langage)");
    $stmt_manga->bindValue(':titre', $titre, PDO::PARAM_STR);
    $stmt_manga->bindValue(':auteur', $auteur, PDO::PARAM_STR);
    $stmt_manga->bindValue(':demographie', $demographie, PDO::PARAM_STR);
    $stmt_manga->bindValue(':langage', $langage, PDO::PARAM_STR);
    $stmt_manga->execute();
    $manga_id = $pdo->lastInsertId();
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
    <article id="add" class="add" style="display: none;">
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

            <button class="add__form--new" type="submit">Ajouter</button>
        </form>
    </article>

    <button id="show">Nouveau manga</button>

    <!-- <form action="" method="get" class="filter">
        <select name="filter" id="filter" multiple>
            <optgroup label="fhrhjq">
                <option value="abc">cou</option>
                <option value="abg">c</option>
                <option value="abh">c</option>
                <option value="abv">c</option>
                <option value="abd">c</option>
                <option value="abc">cou</option>
                <option value="abg">c</option>
                <option value="abh">c</option>
                <option value="abv">c</option>
                <option value="abd">c</option>
            </optgroup>

            <optgroup label="fhrhjq">
                <option value="abc">cou</option>
                <option value="abg">c</option>
                <option value="abh">c</option>
                <option value="abv">c</option>
                <option value="abd">c</option>
            </optgroup>
        </select>

        <button type="submit">Click me</button>
    </form> -->
</body>

</html>