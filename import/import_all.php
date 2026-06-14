<?php

//dans 'http://localhost/Projet_SAE/import/import_all.php' en locale
//ne pas lancer ce fichier une autre fois cela a deja été fait et la base de données est deja cree 

require __DIR__ . '/../config/database.php';

$dossier = __DIR__ . '/datasetCSV/';
set_time_limit(0); // 0 = pas de limite
echo "<pre>";

// ============================================================
// ÉTAPE 1 — movies.csv → annee, film, genre, film_genre
// ============================================================
echo "=== ÉTAPE 1 : movies.csv ===\n";

$fichier = fopen($dossier . "movies.csv", "r");
fgetcsv($fichier);

$stmtAnnee    = $pdo->prepare("INSERT IGNORE INTO annee (an) VALUES (:an)");
$stmtGetAnnee = $pdo->prepare("SELECT idAn FROM annee WHERE an = :an");
$stmtFilm     = $pdo->prepare("INSERT IGNORE INTO film (idFilm, titre, idAn) VALUES (:idFilm, :titre, :idAn)");
$stmtGenre    = $pdo->prepare("INSERT IGNORE INTO genre (nomGenre) VALUES (:nomGenre)");
$stmtGetGenre = $pdo->prepare("SELECT idGenre FROM genre WHERE nomGenre = :nomGenre");
$stmtFG       = $pdo->prepare("INSERT IGNORE INTO film_genre (idFilm, idGenre) VALUES (:idFilm, :idGenre)");

$pdo->beginTransaction();
$compteur = 0;

while (($ligne = fgetcsv($fichier)) !== false) {
  $idFilm  = (int)$ligne[0];
  $titre   = $ligne[1];
  $idAn    = null;

  // Extraire l'année du titre "Toy Story (1995)"
  if (preg_match('/\((\d{4})\)/', $titre, $match)) {
    $an    = (int)$match[1];
    $titre = trim(preg_replace('/\s*\(\d{4}\)/', '', $titre));

    $stmtAnnee->execute([":an" => $an]);
    $stmtGetAnnee->execute([":an" => $an]);
    $idAn = $stmtGetAnnee->fetchColumn();
  }

  $stmtFilm->execute([
    ":idFilm" => $idFilm,
    ":titre"  => $titre,
    ":idAn"   => $idAn
  ]);

  foreach (explode("|", $ligne[2]) as $nomGenre) {
    if ($nomGenre === "(no genres listed)") continue;
    $stmtGenre->execute([":nomGenre" => $nomGenre]);
    $stmtGetGenre->execute([":nomGenre" => $nomGenre]);
    $idGenre = $stmtGetGenre->fetchColumn();
    $stmtFG->execute([":idFilm" => $idFilm, ":idGenre" => $idGenre]);
  }

  $compteur++;
  if ($compteur % 1000 === 0) {
    $pdo->commit();
    $pdo->beginTransaction();
    echo "$compteur films traités...\n";
    ob_flush();
    flush();
  }
}
$pdo->commit();
fclose($fichier);
echo "✓ $compteur films importés\n\n";

// ============================================================
// ÉTAPE 2 — links.csv → fichefilm
// ============================================================
echo "=== ÉTAPE 2 : links.csv ===\n";

$fichier   = fopen($dossier . "links.csv", "r");
fgetcsv($fichier);
$stmtFiche = $pdo->prepare("
    INSERT IGNORE INTO fichefilm (idFilm, imdbid, tmdbid)
    VALUES (:idFilm, :imdbid, :tmdbid)
");

$pdo->beginTransaction();
$compteur = 0;

while (($ligne = fgetcsv($fichier)) !== false) {
  $stmtFiche->execute([
    ":idFilm" => (int)$ligne[0],
    ":imdbid" => $ligne[1] ?? null,
    ":tmdbid" => !empty($ligne[2]) ? (int)$ligne[2] : null
  ]);
  $compteur++;
}
$pdo->commit();
fclose($fichier);
echo "✓ $compteur fiches importées\n\n";

// ============================================================
// ÉTAPE 3 — ratings.csv → utilisateur, noter
// ============================================================
echo "=== ÉTAPE 3 : ratings.csv ===\n";

$fichier  = fopen($dossier . "ratings.csv", "r");
fgetcsv($fichier);
$stmtUser = $pdo->prepare("INSERT IGNORE INTO utilisateur (idUtilisateur) VALUES (:id)");
$stmtNote = $pdo->prepare("
    INSERT IGNORE INTO noter (idUtilisateur, idFilm, note, dateRating)
    VALUES (:idUtilisateur, :idFilm, :note, :dateRating)
");

$pdo->beginTransaction();
$compteur = 0;

while (($ligne = fgetcsv($fichier)) !== false) {
  $stmtUser->execute([":id" => (int)$ligne[0]]);
  $stmtNote->execute([
    ":idUtilisateur" => (int)$ligne[0],
    ":idFilm"        => (int)$ligne[1],
    ":note"          => (float)$ligne[2],
    ":dateRating"    => (int)$ligne[3]
  ]);
  $compteur++;
  if ($compteur % 5000 === 0) {
    $pdo->commit();
    $pdo->beginTransaction();
    echo "$compteur notes traitées...\n";
    ob_flush();
    flush();
  }
}
$pdo->commit();
fclose($fichier);
echo "✓ $compteur notes importées\n\n";

// ============================================================
// ÉTAPE 4 — tags.csv → tag, taguer
// ============================================================
echo "=== ÉTAPE 4 : tags.csv ===\n";

$fichier    = fopen($dossier . "tags.csv", "r");
fgetcsv($fichier);
$stmtTag    = $pdo->prepare("INSERT IGNORE INTO tag (tagText) VALUES (:tagText)");
$stmtGetTag = $pdo->prepare("SELECT idTag FROM tag WHERE tagText = :tagText");
$stmtTaguer = $pdo->prepare("
    INSERT IGNORE INTO taguer (idUtilisateur, idFilm, idTag, dateT)
    VALUES (:idUtilisateur, :idFilm, :idTag, :dateT)
");

$pdo->beginTransaction();
$compteur = 0;

while (($ligne = fgetcsv($fichier)) !== false) {
  $tagText = trim($ligne[2]);
  if (empty($tagText)) continue;

  $stmtUser->execute([":id" => (int)$ligne[0]]);
  $stmtTag->execute([":tagText" => $tagText]);
  $stmtGetTag->execute([":tagText" => $tagText]);
  $idTag = $stmtGetTag->fetchColumn();

  $stmtTaguer->execute([
    ":idUtilisateur" => (int)$ligne[0],
    ":idFilm"        => (int)$ligne[1],
    ":idTag"         => $idTag,
    ":dateT"         => (int)$ligne[3]
  ]);

  $compteur++;
  if ($compteur % 1000 === 0) {
    $pdo->commit();
    $pdo->beginTransaction();
    echo "$compteur tags traités...\n";
    ob_flush();
    flush();
  }
}
$pdo->commit();
fclose($fichier);
echo "✓ $compteur tags importés\n\n";
echo "=== IMPORT TERMINÉ ===";
echo "</pre>";
