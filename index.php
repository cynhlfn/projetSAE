<?php
require __DIR__ . '/config/database.php';
// recuperer les genres de la base de données 
$genres = $pdo->query("SELECT * FROM genre ORDER BY nomGenre")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Wikifilm</title>
  <!-- bootstrap css -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <!-- notre css -->
  <link rel="stylesheet" href="public/css/style.css">
</head>

<!-- bootstrap scale -->
<!-- 0 → 0px
1 → 4px
2 → 8px
3 → 16px
4 → 24px
5 → 48px -->

<body>
  <!-- header pour : logo + genres -->
  <!-- border-bottom : ajouté une bordure en bas -->
  <!-- py-3  : padding vertical  -->
  <!-- py-4  : padding horizontal  -->
  <header class="border-bottom py-3 px-4">
    <!-- logo -->
    <!-- fw-bold : font-weight: bold -->
    <!-- fs-4 : font-size taille 4 -->
    <h1 class="fw-bold fs-4">
      <!-- text-danger : couleur du text rouge (danger de bootstrap) -->
      <span class="text-danger">Wiki</span>film
    </h1>
    <!-- d-flex : display: flex  -->
    <!-- flex-wrap : si trop de boutons il passe a la ligne suivante -->
    <div class="d-flex flex-wrap gap-2 mt-2">
      <!-- btn : class bootstrap pour les bouton   -->
      <!-- active : montre visualement quel bouton est actif -->
      <button class="btn btn-sm btn-dark genre-btn active" data-genre="tous">
        Tous
      </button>
      <?php foreach ($genres as $genre) : ?>
        <!--btn-outline-secondary : contour gris (pour les genres non selectionné)  -->
        <!-- genre-btn : notre propre class css -->
        <!-- data-genre : attribut html personnalié qu'on utilisera en js -->
        <button class="btn btn-sm btn-outline-secondary genre-btn" data-genre="<?= $genre['idGenre'] ?>">
          <?= htmlspecialchars($genre['nomGenre']) ?>
        </button>
      <?php endforeach; ?>
    </div>

  </header>

  <!-- recherche + tri -->
  <section id="search-sort">
    <div>
      <span>🔎</span>
      <input type="text"
        id="search-input"
        class="form-control">
    </div>
  </section>

  <!-- afichage principale de la liste des filmes -->
  <main>
    <!--cartes des filmes  -->
  </main>

  <!-- bootstrap javascript  -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- notre javascript -->
  <script src="public/js/main.js"></script>
</body>

</html>