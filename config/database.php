<?php
$host   = getenv("DB_HOST")     ?: "localhost";
$port   = getenv("DB_PORT")     ?: "3307";        // ← 3307 pour XAMPP
$dbname = getenv("DB_NAME")     ?: "movielens";
$user   = getenv("DB_USER")     ?: "root";
$pass   = getenv("DB_PASSWORD") ?: "";

try {
  $pdo = new PDO(
    "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
    $user,
    $pass
  );
  $pdo->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("Erreur de connexion : " . $e->getMessage());
}
