<?php
require __DIR__ . '/config/database.php';

$nbFilms = $pdo -> query ( "SELECT COUNT(*) FROM film" ) -> fetchColumn();
$nbUtilisateurs = $pdo -> query ( "SELECT COUNT(*) FROM utilisateur" ) -> fetchColumn();
$nbNotes = $pdo -> query ( "SELECT COUNT(*) FROM noter" ) -> fetchColumn();
$nbTags = $pdo -> query ( "SELECT COUNT(*) FROM taguer" ) -> fetchColumn();
$noteGlobale = $pdo -> query ( "SELECT ROUND(AVG(note), 2)  FROM noter" ) -> fetchColumn();


//1. FILMS LES MIEUX NOTES 

    /*  
    On va exiger 20 votes minimum pour qu'un film apparaisse dans le classement
    
    On veut les films les mieux notés
   
    On va faire une jointure entre la table film et la table annee pour récupérer l'année du film, 
   puis une jointure avec la table noter pour récupérer les notes.
   
    On va grouper par film et calculer la moyenne des notes et le nombre de votes pour chaque film.
    
    On va ensuite filtrer pour ne garder que les films ayant au moins 20 votes.
    
    On va trier par moyenne décroissante, puis par nombre de votes décroissant afin de départager les égalités.   

    On limite enfin aux 10 films les mieux notés.

    */


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

    /* 
    On va exiger 20 votes minimum pour qu'un film apparaisse dans le classement

    On veut les films les moins bien notés
   
    On va faire une jointure entre la table film et la table annee pour récupérer l'année du film, 
   puis une jointure avec la table noter pour récupérer les notes.
   
   On va grouper par film et calculer la moyenne des notes et le nombre de votes pour chaque film.
    
   On va ensuite filtrer pour ne garder que les films ayant au moins 20 votes.
    
   On va trier par moyenne croissante, puis par nombre de votes décroissant afin de départager les égalités.
   
   On limite enfin aux 10 films les moins bien notés.

   */



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

    /* 
    On va exiger 20 votes minimum pour qu'un film apparaisse dans le classement.

    On cherche les films les plus vus en assimilant les vues au nombre de notes.
   
    On va faire une jointure entre la table film et la table annee pour récupérer l'année du film, 
   puis une jointure avec la table noter pour récupérer les notes.
   
   On va grouper par film et calculer la moyenne des notes et le nombre de votes pour chaque film.
    
   On va trier par nombre de votes décroissant afin d'obtenir les films les plus vus en premier.

   On limite enfin aux 10 films les plus vus.

   
   */


$plusVus = $pdo->query("
    SELECT f.titre, 
            a.an AS annee,
            ROUND(AVG(n.note), 2)      AS moyenne,
            COUNT(n.note)              AS nb_votes
    FROM film f
    JOIN annee a ON f.idAn = a.idAn
    JOIN noter n ON f.idFilm = n.idFilm
    GROUP BY f.idFilm, f.titre, a.an
    HAVING COUNT(n.note) >= 20
    ORDER BY  nb_votes DESC
    LIMIT 10
")->fetchAll();


// 4. FILMS LES PLUS TAGUES 

    /* 
    On cherche les films les plus tagués.
   
    On va faire une jointure entre la table film et la table taguer pour récupérer les tags associés aux films.
   
   On va grouper par film afin de compter le nombre de tags associés à chaque film.
    
   On va trier par nombre de tags décroissant afin d'obtenir les films les plus tagués en premier.
   
   On limite enfin aux 10 films les plus tagués.

   */
  
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
    /* 

    On cherche les films les plus controversés à partir des films dont les avis sont les plus dispersés.
   
    On va faire une jointure entre la table film et la table noter pour récupérer les notes associées aux films.
   
   On va grouper par film afin de calculer la moyenne, l'écart-type et le nombre des notes pour chaque film.
    
   On va trier par écart-type décroissant afin d'obtenir les films les plus controversés en premier.
   
   On limite enfin aux 10 films les plus controversés.

   */

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
    /* 

    On cherche la répartition des films par genre.
   
    On va faire une jointure entre la table genre et la table film_genre pour associer les films à leurs genres.
   
   On va grouper par genre afin de compter le nombre de films pour chaque genre.
    
   On va trier par nombre de films décroissant afin d'obtenir les genres les plus représentés en premier.
   
   */

$genre = $pdo->query("
    SELECT g.nomGenre, 
            COUNT(fg.idFilm)              AS nb_films
    FROM genre g 
    JOIN film_genre fg ON g.idGenre = fg.idGenre
    GROUP BY g.idGenre, g.nomGenre
    ORDER BY  nb_films DESC
")->fetchAll();




// 7. REPARTITION PAR DECENNIE 

 /* 
    On cherche la répartition des films par décennie.
   
    On va faire une jointure entre la table annee et la table film pour associer les films à leurs années de sortie.
   
    On récupère l’année du film et on la regroupe par tranche de 10 ans.

    On calcule la décennie en utilisant FLOOR(an / 10) * 10.

   On va grouper par décennie afin de compter le nombre de films pour chaque décennie.
    
   On va trier les décennies par ordre chronologique croissant afin de voir l'évolution du nombre de films au fil du temps.

   */

$decennie = $pdo->query("
    SELECT (FLOOR(a.an / 10) * 10) AS decennie,
           COUNT(f.idFilm)         AS nb_films
    FROM film f
    JOIN annee a ON f.idAn = a.idAn
    GROUP BY decennie
    ORDER BY decennie ASC
")->fetchAll();




// 8. TAGS LES PLUS UTILISES 

/*
    On veut connaître les tags les plus utilisés.

    On fait une jointure entre la table tag et la table taguer pour récupérer 
    les associations.

    On regroupe par tag afin de compter le nombre d’utilisations de chaque tag.

    On va trier par nombre d’utilisations décroissant afin de mettre en avant 
    les tags les plus populaires.

    On limite enfin aux 5 tags les plus utilisés.
*/


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
foreach( $genre as $g){
    if($g['nb_films'] > $maxGenre){
        $maxGenre = $g['nb_films'];
    }
}

$maxDecennie = 0;
foreach( $decennie as $d){
    if($d['nb_films'] > $maxDecennie) {
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

    </style>
</head>


<body>
    <div class="app">

    <!-- Header -->
    <header class ="border-bottom py-3 mb-4"> 
      <div class="container d-flex justify-content-between align-items-center">
        <h1 class="logo">&#127916; <span>Wiki</span>Film</h1>
        <a href="index.php" class="btn btn-outline-danger">&larr; Retour aux films</a> 
      </div>
    </header>

    <div class="container py-4">
      <h1 class="h3 fw-bold mb-4">&#128202; Statistiques</h1>


      <!-- Chiffres -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md">
            <div class="card text-center h-100">
                <div class="card-body">
                <div class="h3 fw-bold text-danger mb-0"><?= number_format($nbFilms, 0, ',', ' ') ?></div>
                <div class="small text-muted text-uppercase">Films</div>
                </div>
            </div>
            </div>
            <div class="col-6 col-md">
            <div class="card text-center h-100">
                <div class="card-body">
                <div class="h3 fw-bold text-danger mb-0"><?= number_format($nbUtilisateurs, 0, ',', ' ') ?></div>
                <div class="small text-muted text-uppercase">Utilisateurs</div>
                </div>
            </div>
            </div>
            <div class="col-6 col-md">
            <div class="card text-center h-100">
                <div class="card-body">
                <div class="h3 fw-bold text-danger mb-0"><?= number_format($nbNotes, 0, ',', ' ') ?></div>
                <div class="small text-muted text-uppercase">Notes</div>
                </div>
            </div>
            </div>
            <div class="col-6 col-md">
            <div class="card text-center h-100">
                <div class="card-body">
                <div class="h3 fw-bold text-danger mb-0"><?= number_format($nbTags, 0, ',', ' ') ?></div>
                <div class="small text-muted text-uppercase">Tags poses</div>
                </div>
            </div>
            </div>
            <div class="col-12 col-md">
            <div class="card text-center h-100">
                <div class="card-body">
                <div class="h3 fw-bold text-danger mb-0">&#9733; <?= $noteGlobale ?></div>
                <div class="small text-muted text-uppercase">Note moyenne</div>
                </div>
            </div>
            </div>
        </div>

       <div class="row g-4">



    <!-- Meilleurs films -->
    <div class="col-md-6">
        <div class="card h-100">
        <div class="card-body">
            <h2 class="h6 fw-bold mb-3">&#127942; Les mieux notes <small class="text-muted fw-normal">(min. 20 votes)</small></h2>
            <ul class="list-group list-group-flush">
            <?php foreach ($mieuxNotes as $i => $f): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                <span><span class="badge <?= $i === 0 ? 'bg-warning text-dark' : 'bg-light text-dark' ?> me-2"><?= $i + 1 ?></span>
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
            <h2 class="h6 fw-bold mb-3">&#128577; Les moins bien notes <small class="text-muted fw-normal">(min. 20 votes)</small></h2>
            <ul class="list-group list-group-flush">
            <?php foreach ($moinsBienNotes as $i => $f): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                <span><span class="badge <?= $i === 0 ? 'bg-danger' : 'bg-light text-dark' ?> me-2"><?= $i + 1 ?></span>
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
            <h2 class="h6 fw-bold mb-3">&#128064; Les plus vus <small class="text-muted fw-normal">(nombre de notes)</small></h2>
            <ul class="list-group list-group-flush">
            <?php foreach ($plusVus as $i => $f): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                <span><span class="badge <?= $i === 0 ? 'bg-warning text-dark' : 'bg-light text-dark' ?> me-2"><?= $i + 1 ?></span>
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
            <h2 class="h6 fw-bold mb-3">&#9878; Les plus clivants <small class="text-muted fw-normal">(avis partages)</small></h2>
            <ul class="list-group list-group-flush">
            <?php foreach ($plusControverses as $i => $f): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                <span><span class="badge <?= $i === 0 ? 'bg-warning text-dark' : 'bg-light text-dark' ?> me-2"><?= $i + 1 ?></span>
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
            <h2 class="h6 fw-bold mb-3">&#127991; Les plus tagues</h2>
            <ul class="list-group list-group-flush">
            <?php foreach ($plusTagues as $i => $f): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                <span><span class="badge <?= $i === 0 ? 'bg-warning text-dark' : 'bg-light text-dark' ?> me-2"><?= $i + 1 ?></span>
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
            <h2 class="h6 fw-bold mb-3">&#127902; Films par genre</h2>
            <?php foreach ($genre as $g): ?>
            <div class="mb-2">
                <div class="d-flex justify-content-between small">
                <span><?= htmlspecialchars($g['nomGenre']) ?></span>
                <span class="text-muted"><?= $g['nb_films'] ?></span>
                </div>
                <div class="progress" style="height: 16px;">
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
              <h2 class="h6 fw-bold mb-3">&#128197; Films par decennie</h2>
              <?php foreach ($decennie as $d): ?>
                <div class="mb-2">
                  <div class="d-flex justify-content-between small">
                    <span><?= $d['decennie'] ?>s</span>
                    <span class="text-muted"><?= $d['nb_films'] ?></span>
                  </div>
                  <div class="progress" style="height: 16px;">
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
                <h2 class="h6 fw-bold mb-3">&#9729; Tags les plus utilises</h2>
                <div class="d-flex flex-wrap gap-2">
                <?php foreach ($topTags as $t): ?>
                    <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                    <?= htmlspecialchars($t['tagText']) ?> <b class="text-danger"><?= $t['nb_utilisations'] ?></b>
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
