<?php
// config/init.php sera dans git 

// ── Créer database.php si inexistant (Railway) ──────────────
if (!file_exists(__DIR__ . '/database.php')) {
  file_put_contents(
    __DIR__ . '/database.php',
    '<?php
$host   = getenv("DB_HOST");
$port   = getenv("DB_PORT");
$dbname = getenv("DB_NAME");
$user   = getenv("DB_USER");
$pass   = getenv("DB_PASSWORD");

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
?>'
  );
}


// ── tmdb.php ─────────────────────────────────────────────────

if (getenv("TMDB_TOKEN")) {
  file_put_contents(
    __DIR__ . '/tmdb.php',
    '<?php
define("TMDB_READ_TOKEN", getenv("TMDB_TOKEN") ?: "");
?>'
  );
}
