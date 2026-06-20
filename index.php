<?php
require __DIR__ . '/config/database.php';
require __DIR__ . '/config/tmdb.php';

// Récupération des genres
$genres = $pdo->query("SELECT * FROM genre ORDER BY nomGenre")->fetchAll(PDO::FETCH_ASSOC);

// Récupération des films
$stmt = $pdo->query("
SELECT
    f.idFilm,
    f.titre,
    a.an                                        AS annee,
    fi.tmdbid,
    GROUP_CONCAT(DISTINCT g.nomGenre
                 ORDER BY g.nomGenre
                 SEPARATOR ',')                 AS genres,
    (
        SELECT ROUND(AVG(note), 1)
        FROM noter
        WHERE idFilm = f.idFilm
    )                                           AS note_moyenne
FROM
    film f,
    annee a,
    fichefilm fi,
    film_genre fg,
    genre g
WHERE
    f.idAn       = a.idAn
    AND f.idFilm = fi.idFilm
    AND f.idFilm = fg.idFilm
    AND fg.idGenre = g.idGenre
GROUP BY
    f.idFilm, f.titre, a.an, fi.tmdbid
ORDER BY
    note_moyenne DESC, f.titre ASC
LIMIT 30
");
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
  <style>
    body {
      background: #f5f5f0;
    }

    .app {
      max-width: 1200px;
      margin: 0 auto;
      background: white;
      min-height: 100vh;
    }

    .logo {
      font-size: 2.2rem;
      font-weight: bold;
    }

    .logo span {
      color: #E24B4A;
    }

    .film-card {
      border: 1px solid #ddd;
      border-radius: 12px;
      overflow: hidden;
      cursor: pointer;
      transition: 0.2s;
    }

    .film-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .film-poster {
      height: 280px;
      background-size: cover;
      background-position: center;
      background-color: #1a2634;
    }

    .film-info {
      padding: 12px;
    }

    .film-title {
      font-weight: 600;
      font-size: 1.05rem;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
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
        <div class="row g-3 align-items-center">
          <!-- Recherche -->
          <div class="col-md-5">
            <input type="text" id="search-input" class="form-control" placeholder="🔍 Rechercher un film...">
          </div>

          <!-- Tri -->
          <div class="col-md-4">
            <select id="sort-select" class="form-select">
              <option value="note-desc">Note décroissante</option>
              <option value="year-desc">Année récente</option>
              <option value="year-asc">Année ancienne</option>
              <option value="title-asc">Titre A-Z</option>
            </select>
          </div>
        </div>

        <!-- Boutons Genres -->
        <div class="mt-3" id="genres-container">
          <button class="genre-btn btn btn-dark active me-2 mb-2" data-genre="all">Tous</button>
          <?php foreach ($genres as $g): ?>
            <button class="genre-btn btn btn-outline-secondary me-2 mb-2"
              data-genre="<?= htmlspecialchars($g['nomGenre']) ?>">
              <?= htmlspecialchars($g['nomGenre']) ?>
            </button>
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="public/js/main.js"></script>
</body>

</html>