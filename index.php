<?php
require __DIR__ . '/config/init.php';
require __DIR__ . '/config/database.php';
require __DIR__ . '/config/tmdb.php';

// pour la pagination
// regle de calcul : offset = (page - 1) * limit
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Récupération des genres
$genres = $pdo->query("SELECT * FROM genre ORDER BY nomGenre")->fetchAll(PDO::FETCH_ASSOC);
// recuperer le genres 
$selectedGenre = $_GET['genre'] ?? null;

$search = trim($_GET['search'] ?? '');

$sort = $_GET['sort'] ?? 'title-asc';

$where = "
    f.idAn = a.idAn
    AND f.idFilm = fi.idFilm
    AND f.idFilm = fg.idFilm
    AND fg.idGenre = g.idGenre
";

$params = [];

if ($selectedGenre) {
  $where .= " AND g.nomGenre = :genre";
  $params[':genre'] = $selectedGenre;
}

if (!empty($search)) {
  $where .= " AND f.titre LIKE :search";
  $params[':search'] = "%$search%";
}

$queryGenre = $selectedGenre ? '&genre=' . urlencode($selectedGenre) : '';

$orderBy = "f.titre ASC";

switch ($sort) {
  case 'note-desc':
    $orderBy = "note_moyenne DESC, f.titre ASC";
    break;
  case 'year-asc':
    $orderBy = "a.an ASC, f.titre ASC";
    break;
  case 'year-desc':
    $orderBy = "a.an DESC, f.titre ASC";
    break;
  case 'title-asc':
    $orderBy = "f.titre ASC";
    break;
}

$sql = "
SELECT
    f.idFilm,
    f.titre,
    a.an AS annee,
    fi.tmdbid,
    GROUP_CONCAT(DISTINCT g.nomGenre ORDER BY g.nomGenre SEPARATOR ',') AS genres,
    (
        SELECT ROUND(AVG(note), 1)
        FROM noter
        WHERE idFilm = f.idFilm
    ) AS note_moyenne
FROM film f, annee a, fichefilm fi, film_genre fg, genre g
WHERE $where
GROUP BY f.idFilm, f.titre, a.an, fi.tmdbid
ORDER BY $orderBy
LIMIT :limit OFFSET :offset
";
// Récupération des films
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
  $stmt->bindValue($key, $value);
}

$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$countSql = "
SELECT COUNT(DISTINCT f.idFilm)
FROM film f
JOIN fichefilm fi ON f.idFilm = fi.idFilm
JOIN film_genre fg ON f.idFilm = fg.idFilm
JOIN genre g ON fg.idGenre = g.idGenre
WHERE 1=1
";

if ($selectedGenre) {
  $countSql .= " AND g.nomGenre = :genre";
}

if (!empty($search)) {
  $countSql .= " AND f.titre LIKE :search";
}

$countStmt = $pdo->prepare($countSql);

if ($selectedGenre) {
  $countStmt->bindValue(':genre', $selectedGenre);
}

if (!empty($search)) {
  $countStmt->bindValue(':search', "%$search%");
}

$countStmt->execute();
$total = $countStmt->fetchColumn();

$pages = ceil($total / $limit);
$films = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Fonction pour récupérer l'affiche
function getPosters(array $films): array
{
  $posters   = [];
  $handles   = [];
  $multiCurl = curl_multi_init();

  foreach ($films as $film) {
    $tmdbid = $film['tmdbid'];
    if (empty($tmdbid)) {
      $posters[$film['idFilm']] = null;
      continue;
    }

    $ch = curl_init("https://api.themoviedb.org/3/movie/{$tmdbid}?language=fr-FR");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      "Authorization: Bearer " . TMDB_READ_TOKEN,
      "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_multi_add_handle($multiCurl, $ch);
    $handles[$film['idFilm']] = $ch;
  }

  // Lance tous les appels EN MÊME TEMPS
  do {
    curl_multi_exec($multiCurl, $running);
    curl_multi_select($multiCurl);
  } while ($running > 0);

  // Récupère les résultats
  foreach ($handles as $idFilm => $ch) {
    $response = curl_multi_getcontent($ch);
    if ($response) {
      $data = json_decode($response, true);
      $posters[$idFilm] = !empty($data['poster_path'])
        ? "https://image.tmdb.org/t/p/w342" . $data['poster_path']
        : null;
    } else {
      $posters[$idFilm] = null;
    }
    curl_multi_remove_handle($multiCurl, $ch);
    curl_close($ch);
  }

  curl_multi_close($multiCurl);
  return $posters;
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Wikifilm</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100..700;1,100..700&family=Rubik:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="public/css/style.css">
  </style>
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

        <a href="stats.php" class="btn btn-stats">
          <i class="bi bi-bar-chart-line"></i>
          Statistiques
        </a>

      </div>
    </header>
    <!-- filtres -->
    <div class="filters-section py-4">
      <div class="container">
        <form method="GET">

          <div class="row g-3 align-items-center">
            <!-- Recherche -->
            <div class="col-md-5">
              <div class="search-wrapper">
                <input
                  type="text"
                  name="search"
                  class="form-control search-input"
                  placeholder="Rechercher un film..."
                  value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-search">
                  <i class="bi bi-search"></i>
                </button>
              </div>
            </div>

            <!-- Tri -->
            <div class="col-md-4">
              <select name="sort" id="sort-select" class="form-select custom-select">
                <option value="note-desc">Note décroissante</option>
                <option value="year-desc">Année récente</option>
                <option value="year-asc">Année ancienne</option>
                <option value="title-asc">Titre A-Z</option>
              </select>
            </div>
          </div>

        </form>

        <!-- Boutons Genres -->
        <div class="mt-3" id="genres-container">
          <a href="?" class="btn btn-dark me-2 mb-2">Tous</a>
          <?php foreach ($genres as $g): ?>
            <?php $isActive = ($selectedGenre == $g['nomGenre']) ?>
            <a href="?genre=<?= urlencode($g['nomGenre']) ?>"
              class="btn <?= $isActive ? 'btn-dark' : 'btn-outline-secondary' ?> me-2 mb-2">
              <?= htmlspecialchars($g['nomGenre']) ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <!-- GRILLE -->
    <div class="container py-4">
      <div class="row g-4" id="list-view">
        <?php
        $posters = getPosters($films); // ← UN seul appel pour tous les films 
        ?>
        <?php foreach ($films as $film):
          $genresList = $film['genres'] ? explode(',', $film['genres']) : [];
          $poster = $posters[$film['idFilm']] ?? null;
        ?>
          <div class="col-12 col-md-6 col-lg-4"
            data-title="<?= htmlspecialchars(strtolower($film['titre'])) ?>"
            data-genres="<?= htmlspecialchars(strtolower($film['genres'] ?? '')) ?>"
            data-year="<?= $film['annee'] ?>"
            data-note="<?= $film['note_moyenne'] ?? 0 ?>">
            <div class="film-card h-100" onclick="window.location.href='film.php?id=<?= $film['idFilm'] ?>'">
              <div class="film-poster">
                <img
                  src="<?= $poster ? htmlspecialchars($poster) : 'https://via.placeholder.com/300x420/1a2634/ffffff?text=' . urlencode($film['titre']) ?>"
                  alt="<?= htmlspecialchars($film['titre']) ?>"
                  class="poster-img">
              </div>
              <div class="film-info">
                <div class="film-title"><?= htmlspecialchars($film['titre']) ?></div>
                <div class="film-meta small">
                  <?= htmlspecialchars($genresList[0] ?? '') ?>
                  ★ <?= $film['note_moyenne'] ?: '–' ?>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>
  <nav class="mt-4">
    <ul class="pagination justify-content-center">

      <!-- PREV -->
      <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
        <a class="page-link" href="?page=<?= max(1, $page - 1) ?><?= $queryGenre ?>&search=<?= urlencode($search) ?>&sort=<?= urlencode($sort) ?>">Prev</a>
      </li>

      <!-- PAGES -->
      <?php
      $start = max(1, $page - 2);
      $end = min($pages, $page + 2);
      ?>
      <?php for ($i = $start; $i <= $end; $i++): ?>
        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
          <a class="page-link" href="?page=<?= $i ?><?= $queryGenre ?>&search=<?= urlencode($search) ?>&sort=<?= urlencode($sort) ?>">
            <?= $i ?>
          </a>
        </li>
      <?php endfor; ?>

      <!-- NEXT -->
      <li class="page-item <?= ($page >= $pages) ? 'disabled' : '' ?>">
        <a class="page-link" href="?page=<?= min($pages, $page + 1) ?><?= $queryGenre ?>&search=<?= urlencode($search) ?>&sort=<?= urlencode($sort) ?>">Next</a>
      </li>

    </ul>
  </nav>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="public/js/main.js"></script>
</body>

</html>