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
function getPoster($tmdbid)
{
  if (empty($tmdbid)) return null;

  $url = "https://api.themoviedb.org/3/movie/{$tmdbid}?language=fr-FR";

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . TMDB_READ_TOKEN,
    "Content-Type: application/json"
  ]);
  curl_setopt($ch, CURLOPT_TIMEOUT, 10);
  $response = curl_exec($ch);
  curl_close($ch);

  if ($response) {
    $data = json_decode($response, true);
    if (!empty($data['poster_path'])) {
      return "https://image.tmdb.org/t/p/w342" . $data['poster_path'];
    }
  }
  return null;
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Wikifilm</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="/Projet_SAE/public/css/style.css">
  </style>
</head>

<body>
  <div class="app">

    <!-- HEADER -->
    <header class="border-bottom py-3">
      <div class="container">
        <h1 class="logo">🎬 <span>Wiki</span>film</h1>
      </div>
    </header>

    <!-- SEARCH -->
    <!-- FILTRES -->
    <div class="bg-light py-3 border-bottom">
      <div class="container">
        <form method="GET">

          <div class="row g-3 align-items-center">
            <!-- Recherche -->
            <div class="col-md-5">

              <input type="text" name="search" class="form-control" placeholder="🔍 Rechercher un film..." value="<?= htmlspecialchars($search) ?>">
              <button type="submit" class="btn btn-primary">Rechercher</button>

            </div>

            <!-- Tri -->
            <div class="col-md-4">
              <select name="sort" id="sort-select" class="form-select">
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
      <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-4" id="list-view">
        <?php foreach ($films as $film):
          $genresList = $film['genres'] ? explode(',', $film['genres']) : [];
          $poster = getPoster($film['tmdbid']);
        ?>
          <div class="col film-item"
            data-title="<?= htmlspecialchars(strtolower($film['titre'])) ?>"
            data-genres="<?= htmlspecialchars(strtolower($film['genres'] ?? '')) ?>"
            data-year="<?= $film['annee'] ?>"
            data-note="<?= $film['note_moyenne'] ?? 0 ?>">
            <div class="film-card h-100" onclick="showDetail(<?= $film['idFilm'] ?>)">
              <div class="film-poster"
                style="background-image: url('<?= $poster ? htmlspecialchars($poster) : 'https://via.placeholder.com/300x420/1a2634/ffffff?text=' . urlencode($film['titre']) ?>')">
              </div>
              <div class="film-info">
                <div class="film-title"><?= htmlspecialchars($film['titre']) ?></div>
                <div class="text-muted small">
                  <?= htmlspecialchars($genresList[0] ?? '') ?>
                  ★ <?= $film['note_moyenne'] ?: '–' ?>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- VUE DETAIL -->
    <div id="detail-view" class="container mt-4" style="display: none;"></div>

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
  <script src="/Projet_SAE/public/js/main.js"></script>
</body>

</html>