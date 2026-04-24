<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost'; // adresse du serveur de base de données
$db_name = 'mangatheque_db'; // nom de la base de données
$user = 'root'; // nom d'utilisateur
$pass = 'root'; // mot de passe
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING, // affiche les erreurs sous forme d'exceptions
];

try {
    // Data Source Name
    $dsn = "mysql:host=$host;dbname=$db_name;charset=utf8";
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "<p>Connexion à la base de données réussie</p>";
} catch (PDOException $e) {
    echo "<p>Erreur de connexion à la base de données : " . $e->getMessage() . "</p>";
}