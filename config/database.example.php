<?php
// pour les membre du groupe :
// Copie ce fichier dans "database.php" et remplis avec les identifiants Railway
// Ne jamais mettre database.php sur GitHub (met ajoute config/database.php a ton .gitignore)

$host   = getenv("DB_HOST")     ?: "";  // ex: acela.proxy.rlwy.net
$port   = getenv("DB_PORT")     ?: "";  // ex: 51877
$dbname = getenv("DB_NAME")     ?: "";  // ex: railway
$user   = getenv("DB_USER")     ?: "";  // ex: root
$pass   = getenv("DB_PASSWORD") ?: "";  // le mot de passe Railway

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
