<?php
// PARTIE 1 : logique PHP (récupération des données)
// Ici pas de HTML, juste du PHP pur

require __DIR__ . '/config/init.php';
require __DIR__ . '/config/database.php';
require __DIR__ . '/config/tmdb.php';

$id = $_GET['id'] ?? null;
// le ?? null = si 'id' n'existe pas dans l'URL, on met null
if ($id === null) {
  die("Aucun film sélectionné.");
}

// Requête SQL pour récupérer les infos du film
// Requête SQL pour récupérer les genres du film

$stmtTitre = $pdo->prepare("SELECT titre FROM film WHERE film.idFilm = :id ");
$stmtTitre->execute([':id' => $id]);
$titre = $stmtTitre->fetch();

$stmtAnnee = $pdo->prepare("SELECT annee.an FROM film, annee WHERE film.idAn = annee.idAn AND film.idFilm =:id ");
$stmtAnnee->execute([':id' => $id]);
$annee = $stmtAnnee->fetch();

$stmtGenre = $pdo->prepare("SELECT genre.nomGenre FROM genre, film_genre WHERE film_genre.idGenre = genre.idGenre AND film_genre.idFilm =:id ");
$stmtGenre->execute([':id' => $id]);
$genres = $stmtGenre->fetchAll();

$stmtNotes = $pdo->prepare("SELECT AVG(note) AS note_moyenne FROM noter WHERE noter.idFilm =:id ");
$stmtNotes->execute([':id' => $id]);
$note = $stmtNotes->fetch();

$stmtTags = $pdo->prepare("SELECT DISTINCT tag.tagText FROM tag, taguer WHERE taguer.idTag = tag.idTag AND taguer.idFilm =:id ");
$stmtTags->execute([':id' => $id]);
$tags = $stmtTags->fetchAll();

$stmtImdb = $pdo->prepare("SELECT imdbid FROM fichefilm WHERE fichefilm.idFilm =:id ");
$stmtImdb->execute([':id' => $id]);
$Imdb = $stmtImdb->fetch();

$stmttmdb = $pdo->prepare("SELECT tmdbid FROM fichefilm WHERE fichefilm.idFilm =:id ");
$stmttmdb->execute([':id' => $id]);
$tmdbid = $stmttmdb->fetch();

/* Pour les infos qui manquent on les recuperent via l'api de tmdb en envotant une rerquete http comme suit : */
$url = "https://api.themoviedb.org/3/movie/" . $tmdbid['tmdbid'] . "?language=fr-FR";

/* $response = file_get_contents($url); on la remplace avec ce qui suit */

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "Authorization: Bearer " . TMDB_READ_TOKEN,
  "Content-Type: application/json"
]);
$response = curl_exec($ch);
curl_close($ch);

/*var_dump($response);  cette commande affiche le type et et le contenu d une variable, dans le cas ou  file_get_contents($url)echoue elle contient fals 
et effectivement dans ce premier cas elle  contient false */
/* on a du texte brut dans response sous forme json , on cree un tableau associatif pour acceder au donnees  */

$donneesTmdb = json_decode($response, true);
/* true pour tableau associatif, f&lse pour objet php */

/*print_r($donneesTmdb); pour afficher ce que contient donneeestmdb*/

$overview = $donneesTmdb['overview']    ?? '';
$runtime  = $donneesTmdb['runtime']     ?? '';
$poster   = $donneesTmdb['poster_path'] ?? '';
$url2 = "https://api.themoviedb.org/3/movie/" . $tmdbid['tmdbid'] . "/credits?language=fr-FR";
$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, $url2);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_HTTPHEADER, [
  "Authorization: Bearer " . TMDB_READ_TOKEN,
  "Content-Type: application/json"
]);
$response2 = curl_exec($ch2);
curl_close($ch2);
$donneesTmdbReal = json_decode($response2, true);

$realisateurs = [];
if (!empty($donneesTmdbReal['crew'])) {
  foreach ($donneesTmdbReal['crew'] as $membre) {
    if ($membre['job'] === 'Director') {
      $realisateurs[] = $membre['name'];
    }
  }
}


/***************** partie code claude  */
$noteArrondie   = round($note['note_moyenne'], 1); // 3.9
$etoilespleines = floor($noteArrondie);            // 3 → partie entière
$demiEtoile     = ($noteArrondie - $etoilespleines) >= 0.5 ? 1 : 0; // 1 si .5 ou plus
$etoilesVides   = 5 - $etoilespleines - $demiEtoile; // le reste
/**************** fin de la partie claude  */
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100..700;1,100..700&family=Rubik:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
  <link href="public/css/style.css" rel="stylesheet"> <!-- après Bootstrap -->
</head>

<body>
  <div class="app">

    <!-- HEADER -->
    <header class="main-header py-3">
      <div class="container d-flex justify-content-between align-items-center">

        <h1 class="logo">
          <i class="bi bi-film"></i>
          <span>Wiki</span>film
        </h1>

        <a href="index.php" class="btn btn-stats">
          <i class="bi bi-arrow-left"></i>
          Retour
        </a>

      </div>
    </header>

    <!-- HERO FILM -->
    <section class="film-hero"
      style="background-image: url('https://image.tmdb.org/t/p/original<?= $poster ?>');">

      <div class="film-hero-overlay"></div>

      <div class="container film-hero-content">
        <div class="row align-items-center">

          <!-- POSTER -->
          <div class="col-12 col-md-4 text-center mb-4 mb-md-0">
            <img class="film-hero-poster"
              src="https://image.tmdb.org/t/p/w500<?= $poster ?>"
              alt="affiche">
          </div>

          <!-- INFOS -->
          <div class="col-12 col-md-8">

            <h1 class="film-hero-title">
              <?= htmlspecialchars($titre['titre']) ?>
            </h1>

            <div class="film-meta mb-3">
              <span><?= $annee['an'] ?></span>
              <span>• <?= $runtime ?> min</span>
            </div>
            <div class="film-meta mb-3">
              <?php if (!empty($realisateurs)): ?>
                <span>
                  <i class="bi bi-person-video3"></i>
                  <?= implode(', ', array_map('htmlspecialchars', $realisateurs)) ?>
                </span>
              <?php endif; ?>
            </div>
            <!-- GENRES -->
            <div class="mb-3">
              <?php foreach ($genres as $genre): ?>
                <span class="badge genre-badge me-1">
                  <?= htmlspecialchars($genre['nomGenre']) ?>
                </span>
              <?php endforeach; ?>
            </div>
            <!-- tages -->
            <?php if (!empty($tags)): ?>
              <div class="mb-3">
                <?php foreach ($tags as $tag): ?>
                  <span class="badge genre-badge me-1">
                    #<?= htmlspecialchars($tag['tagText']) ?>
                  </span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
            <!-- NOTE -->
            <div class="mb-3">
              <?php for ($i = 0; $i < $etoilespleines; $i++): ?>
                <i class="bi bi-star-fill text-warning"></i>
              <?php endfor; ?>

              <?php if ($demiEtoile): ?>
                <i class="bi bi-star-half text-warning"></i>
              <?php endif; ?>

              <?php for ($i = 0; $i < $etoilesVides; $i++): ?>
                <i class="bi bi-star text-warning"></i>
              <?php endfor; ?>
            </div>

            <!-- DESCRIPTION -->
            <p class="film-overview">
              <?= htmlspecialchars($overview) ?>
            </p>

            <!-- ACTIONS -->
            <div class="mt-4 d-flex gap-3 flex-wrap">

              <a class="btn btn-search"
                href="https://www.imdb.com/title/tt<?= $Imdb['imdbid'] ?>"
                target="_blank">
                <i class="bi bi-box-arrow-up-right"></i>
                IMDb
              </a>



            </div>

          </div>
        </div>
      </div>

    </section>

  </div>
</body>

</html>