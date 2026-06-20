<?php
// PARTIE 1 : logique PHP (récupération des données)
// Ici pas de HTML, juste du PHP pur

require 'config/database.php';

$id =1;/* $_GET['id'] ?? null;*/
// le ?? null = si 'id' n'existe pas dans l'URL, on met null

// Requête SQL pour récupérer les infos du film
// Requête SQL pour récupérer les genres du film

$stmtTitre= $pdo->prepare("SELECT titre FROM film WHERE film.idFilm = :id ");
$stmtTitre->execute([':id'=> $id]);
$titre= $stmtTitre->fetch();

$stmtAnnee= $pdo->prepare("SELECT annee.an FROM film, annee WHERE film.idAn = annee.idAn AND film.idFilm =:id ");
$stmtAnnee->execute([':id'=> $id]);
$annee= $stmtAnnee->fetch();


$stmtGenre= $pdo->prepare("SELECT genre.nomGenre FROM genre, film_genre WHERE film_genre.idGenre = genre.idGenre AND film_genre.idFilm =:id ");
$stmtGenre->execute([':id'=> $id]);
$genres= $stmtGenre->fetchAll();


$stmtNotes= $pdo->prepare("SELECT AVG(note) AS note_moyenne FROM noter WHERE noter.idFilm =:id ");
$stmtNotes->execute([':id'=> $id]);
$note= $stmtNotes->fetch();


$stmtTags= $pdo->prepare("SELECT DISTINCT  tag.tagText FROM tag, taguer WHERE taguer.idTag = tag.idTag AND taguer.idFilm =:id ");
$stmtTags->execute([':id'=> $id]);
$tags= $stmtTags->fetchAll();


$stmtImdb= $pdo->prepare("SELECT imdbid FROM fichefilm WHERE fichefilm.idFilm =:id ");
$stmtImdb->execute([':id'=> $id]);
$Imdb= $stmtImdb->fetch();


?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- PARTIE 2 : le HTML -->
    <!-- Ici on affiche les données récupérées au-dessus -->

</head> 
<body>

    <h1><?= htmlspecialchars($titre['titre'])  ?></h1>
    <p> <?= $annee['an'] ?> </p>
    <?php foreach($genres as $genre) : ?>
        <span> <?= $genre['nomGenre'] ?></span>   
        <?php endforeach; ?>
        <p> <?= round($note['note_moyenne'],1) ?> </p>

        <?php foreach($tags as  $tag) : ?>
        <span> <?= $tag['tagText'] ?></span>   
        <?php endforeach; ?>

        <p> <?= $Imdb['imdbid'] ?> </p>



</body>
</html>

