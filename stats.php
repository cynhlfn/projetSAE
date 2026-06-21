<?php
require __DIR__ . '/config/database.php';

$nbFilms = $pdo->query("SELECT COUNT(*) FROM film")->fetchColumn();
$nbUtilisateurs = $pdo->query("SELECT COUNT(*) FROM utilisateur")->fetchColumn();
$nbNotes = $pdo->query("SELECT COUNT(*) FROM noter")->fetchColumn();
$nbTags = $pdo->query("SELECT COUNT(*) FROM taguer")->fetchColumn();
$noteGlobale = $pdo->query("SELECT ROUND(AVG(note), 2)  FROM noter")->fetchColumn();


//1. FILMS LES MIEUX NOTES 
// On va exiger 20 votes minimum pour qu'un film apparaisse dans le classement

$mieuxNotes = $pdo->query("
    SELECT f.titre, 
            a.an AS annee,
            ROUND(AVG(n.note), 2)      AS moyenne,
            COUNT(n.note)              AS nb_votes
    FROM film f
    JOIN annee a ON f.idAn = a.idAn
    JOIN noter n ON f.idFilm = n.idFilm
    GROUP BY f.idFilm, f.titre, a.an
    HAVING COUNT(n.note) >= 20
    ORDER BY moyenne DESC, nb_votes DESC
    LIMIT 10
")->fetchAll();


// 2. FILMS LES MOINS BIEN NOTES
// On va exiger 20 votes minimum pour qu'un film apparaisse dans le classement

$moinsBienNotes = $pdo->query("
    SELECT f.titre, 
            a.an AS annee,
            ROUND(AVG(n.note), 2)      AS moyenne,
            COUNT(n.note)              AS nb_votes
    FROM film f
    JOIN annee a ON f.idAn = a.idAn
    JOIN noter n ON f.idFilm = n.idFilm
    GROUP BY f.idFilm, f.titre, a.an
    HAVING COUNT(n.note) >= 20
    ORDER BY moyenne ASC, nb_votes DESC
    LIMIT 10
")->fetchAll();


// 3. FILMS LES PLUS VUS ( on va considérer que ce sont les plus notés) 
$plusVus = $pdo->query("
    SELECT f.titre, 
            a.an AS annee,
            ROUND(AVG(n.note), 2)      AS moyenne,
            COUNT(n.note)              AS nb_votes
    FROM film f
    JOIN annee a ON f.idAn = a.idAn
    JOIN noter n ON f.idFilm = n.idFilm
    GROUP BY f.idFilm, f.titre, a.an
    ORDER BY  nb_votes DESC
    LIMIT 10
")->fetchAll();


// 4. FILMS LES PLUS TAGUES 
$plusTagues = $pdo->query("
    SELECT f.titre, 
            COUNT(t.idTag)              AS nb_tags
    FROM film f
    JOIN annee a ON f.idAn = a.idAn
    JOIN taguer t ON f.idFilm = t.idFilm
    GROUP BY f.idFilm, f.titre 
    ORDER BY  nb_tags DESC
    LIMIT 10
")->fetchAll();


// 5. FILMS LES PLUS CONTROVERSES
$plusControverses = $pdo->query("
     SELECT f.titre, 
            ROUND(AVG(n.note), 2)      AS moyenne,
            ROUND(STDDEV(n.note), 3)      AS ecart_type,

            COUNT(n.note)              AS nb_votes
    FROM film f  
    JOIN noter n ON f.idFilm = n.idFilm
    GROUP BY f.idFilm, f.titre 
    HAVING COUNT(n.note) >= 20
    ORDER BY  ecart_type DESC
    LIMIT 10
")->fetchAll();






//6. REPARTITION PAR GENRE 
$genre = $pdo->query("
    SELECT g.nomGenre, 
            COUNT(fg.idFilm)              AS nb_films
    FROM genre g 
    JOIN film_genre fg ON g.idGenre = fg.idGenre
    GROUP BY g.idGenre, g.nomGenre
    ORDER BY  nb_films DESC
")->fetchAll();




// 7. REPARTITION PAR DECENNIE 
$decennie = $pdo->query("
    SELECT (FLOOR(a.an / 10) * 10) AS decennie,
           COUNT(f.idFilm)         AS nb_films
    FROM film f
    JOIN annee a ON f.idAn = a.idAn
    GROUP BY decennie
    ORDER BY decennie ASC
")->fetchAll();




// 8. TAGS LES PLUS UTILISES 
$topTags = $pdo->query("
    SELECT t.tagText,
           COUNT(tg.idTag) AS nb_utilisations
    FROM tag t
    JOIN taguer tg ON t.idTag = tg.idTag
    GROUP BY t.idTag, t.tagText
    ORDER BY nb_utilisations DESC
    LIMIT 5
")->fetchAll();


$maxGenre = 0;
foreach ($genre as $g) {
  if ($g['nb_films'] > $maxGenre) {
    $maxGenre = $g['nb_films'];
  }
}

$maxDecennie = 0;
foreach ($decennie as $d) {
  if ($d['nb_films'] > $maxDecennie) {
    $maxDecennie = $d['nb_films'];
  }
}

?>


<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title> Statistiques </title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100..700;1,100..700&family=Rubik:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
  <link href="public/css/style.css" rel="stylesheet">
</head>


<body class="stats-page">
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

    <div class="container py-4">
      <h1 class="logo logo-stats mb-4">
        <i class="bi bi-bar-chart-line"></i>
        Stats film
      </h1>

      <!-- Chiffres -->
      <div class="row g-3 mb-4">

        <div class="col-6 col-md">
          <div class="stat-card p-3 text-center">
            <div class="stat-value"><?= number_format($nbFilms) ?></div>
            <div class="stat-label">Films</div>
          </div>
        </div>

        <div class="col-6 col-md">
          <div class="stat-card p-3 text-center">
            <div class="stat-value"><?= number_format($nbUtilisateurs) ?></div>
            <div class="stat-label">Utilisateurs</div>
          </div>
        </div>

        <div class="col-6 col-md">
          <div class="stat-card p-3 text-center">
            <div class="stat-value"><?= number_format($nbNotes) ?></div>
            <div class="stat-label">Notes</div>
          </div>
        </div>

        <div class="col-6 col-md">
          <div class="stat-card p-3 text-center">
            <div class="stat-value"><?= number_format($nbTags) ?></div>
            <div class="stat-label">Tags</div>
          </div>
        </div>

        <div class="col-12 col-md">
          <div class="stat-card p-3 text-center">
            <div class="stat-value">★ <?= $noteGlobale ?></div>
            <div class="stat-label">Note moyenne</div>
          </div>
        </div>

      </div>

      <div class="row g-4">



        <!-- Meilleurs films -->
        <div class="col-md-6">
          <div class="card h-100">
            <div class="card-body">
              <h2 class="h6 fw-bold mb-3"><i class="bi bi-trophy"></i>Les mieux notes <small class="text-muted fw-normal">(min. 20 votes)</small></h2>
              <ul class="list-unstyled">
                <?php foreach ($mieuxNotes as $i => $f): ?>
                  <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span><span class="me-2 opacity-75"><?= $i + 1 ?></span>
                      <?= htmlspecialchars($f['titre']) ?> <small class="text-muted">(<?= $f['annee'] ?>)</small></span>
                    <span class="fw-bold text-warning-emphasis">&#9733; <?= $f['moyenne'] ?></span>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        </div>


        <!-- Pires films -->
        <div class="col-md-6">
          <div class="card h-100">
            <div class="card-body">
              <h2 class="h6 fw-bold mb-3"><i class="bi bi-emoji-frown"></i> Les moins bien notes <small class="text-muted fw-normal">(min. 20 votes)</small></h2>
              <ul class="list-unstyled">
                <?php foreach ($moinsBienNotes as $i => $f): ?>
                  <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span><span class="me-2 opacity-75"><?= $i + 1 ?></span>
                      <?= htmlspecialchars($f['titre']) ?> <small class="text-muted">(<?= $f['annee'] ?>)</small></span>
                    <span class="fw-bold text-warning-emphasis">&#9733; <?= $f['moyenne'] ?></span>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        </div>



        <!-- Plus vus -->
        <div class="col-md-6">
          <div class="card h-100">
            <div class="card-body">
              <h2 class="h6 fw-bold mb-3"><i class="bi bi-eye"></i> Les plus vus <small class="text-muted fw-normal">(nombre de notes)</small></h2>
              <ul class="list-unstyled">
                <?php foreach ($plusVus as $i => $f): ?>
                  <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span><span class="me-2 opacity-75"><?= $i + 1 ?></span>
                      <?= htmlspecialchars($f['titre']) ?> <small class="text-muted">(<?= $f['annee'] ?>)</small></span>
                    <span class="fw-bold text-warning-emphasis"><?= number_format($f['nb_votes'], 0, ',', ' ') ?> votes</span>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        </div>




        <!-- Plus controversés -->
        <div class="col-md-6">
          <div class="card h-100">
            <div class="card-body">
              <h2 class="h6 fw-bold mb-3"><i class="bi bi-lightning"></i> Les plus clivants <small class="text-muted fw-normal">(avis partages)</small></h2>
              <ul class="list-unstyled">
                <?php foreach ($plusControverses as $i => $f): ?>
                  <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span><span class="me-2 opacity-75"><?= $i + 1 ?></span>
                      <?= htmlspecialchars($f['titre']) ?> <small class="text-muted">(moy. <?= $f['moyenne'] ?>)</small></span>
                    <span class="fw-bold text-warning-emphasis">&sigma; <?= $f['ecart_type'] ?></span>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        </div>


        <!-- Les plus tagués -->
        <div class="col-md-6">
          <div class="card h-100">
            <div class="card-body">
              <h2 class="h6 fw-bold mb-3"><i class="bi bi-tags"></i> Les plus tagues</h2>
              <ul class="list-unstyled">
                <?php foreach ($plusTagues as $i => $f): ?>
                  <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span><span class="me-2 opacity-75"><?= $i + 1 ?></span>
                      <?= htmlspecialchars($f['titre']) ?></span>
                    <span class="fw-bold text-warning-emphasis"><?= $f['nb_tags'] ?> tags</span>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        </div>


        <!-- Répartition par genre -->
        <div class="col-md-6">
          <div class="card h-100">
            <div class="card-body">
              <h2 class="h6 fw-bold mb-3"><i class="bi bi-collection-play"></i> Films par genre</h2>
              <?php foreach ($genre as $g): ?>
                <div class="mb-2">
                  <div class="d-flex justify-content-between small">
                    <span><?= htmlspecialchars($g['nomGenre']) ?></span>
                    <span class="text-muted"><?= $g['nb_films'] ?></span>
                  </div>
                  <div class="progress custom-progress">
                    <div class="progress-bar bg-danger" style="width: <?= $maxGenre ? round($g['nb_films'] / $maxGenre * 100) : 0 ?>%"></div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>



        <!-- Répartition par décennie -->
        <div class="col-md-6">
          <div class="card h-100">
            <div class="card-body">
              <h2 class="h6 fw-bold mb-3"><i class="bi bi-calendar3"></i> Films par decennie</h2>
              <?php foreach ($decennie as $d): ?>
                <div class="mb-2">
                  <div class="d-flex justify-content-between small">
                    <span><?= $d['decennie'] ?>s</span>
                    <span class="text-muted"><?= $d['nb_films'] ?></span>
                  </div>
                  <div class="progress custom-progress">
                    <div class="progress-bar bg-danger" style="width: <?= $maxDecennie ? round($d['nb_films'] / $maxDecennie * 100) : 0 ?>%"></div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>


        <!-- Tags les plus utilisés -->
        <div class="col-12">
          <div class="card">
            <div class="card-body">
              <h2 class="h6 fw-bold mb-3"><i class="bi bi-cloud"></i> Tags les plus utilises</h2>
              <div class="d-flex flex-wrap gap-2">
                <?php foreach ($topTags as $t): ?>
                  <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                    <?= htmlspecialchars($t['tagText']) ?> <b class="text-warning-emphasis"><?= $t['nb_utilisations'] ?></b>
                  </span>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>

      </div>

    </div>

  </div>
</body>


</html>