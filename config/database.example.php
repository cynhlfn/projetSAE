<?php
// config/database.example.php
// Copie ce fichier en "database.php" et remplis avec tes identifiants Railway
// (trouvables dans Railway → service MySQL → Public URL)


// ceci est un fichier exemple a ne pas utilisé dans le prjet, quand tu colone le depo cree ton propre database.php et contacte moi (cyndia) pour avoir les infos de la base de données deployer sur railwat
$host   = ""; // a extraire de l'URL
$port   = ""; // a extraire de l'URL
$dbname = "railway";
$user   = "root";
$pass   = "";  // MYSQL_PASSWORD dans Railway → Variables

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
