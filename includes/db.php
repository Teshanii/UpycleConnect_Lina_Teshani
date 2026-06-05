<?php
// Connexion PDO à la BDD MySQL
try {
    $pdo = new PDO(
        'mysql:host=database;dbname=upcycle_connect;charset=utf8',
        'root',
        'root',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Erreur base de données : " . $e->getMessage());
}