<?php
set_time_limit(0);
require __DIR__ . '/../config/database.php';

$dossier = __DIR__ . '/datasetCSV/';
echo "<pre>";
echo "=== Import tags ===\n";

$fichier    = fopen($dossier . "tags.csv", "r");
fgetcsv($fichier);

$stmtUser   = $pdo->prepare("INSERT IGNORE INTO utilisateur (idUtilisateur) VALUES (:id)");
$stmtTag    = $pdo->prepare("INSERT IGNORE INTO tag (tagText) VALUES (:tagText)");
$stmtGetTag = $pdo->prepare("SELECT idTag FROM tag WHERE tagText = :tagText");
$stmtTaguer = $pdo->prepare("INSERT IGNORE INTO taguer (idUtilisateur, idFilm, idTag, dateT) VALUES (:idUtilisateur, :idFilm, :idTag, :dateT)");

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
  if ($compteur % 500 === 0) {
    echo "$compteur tags traités...\n";
  }
}
fclose($fichier);
echo "✓ $compteur tags importés\n";
echo "</pre>";
