<?php
// PARTIE 1 : logique PHP (récupération des données)
// Ici pas de HTML, juste du PHP pur

require 'config/database.php';
require 'config/tmdb.php'; // donne accès à $tmdbApiKey

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

$stmttmdb= $pdo->prepare("SELECT tmdbid FROM fichefilm WHERE fichefilm.idFilm =:id ");
$stmttmdb->execute([':id'=> $id]);
$tmdbid= $stmttmdb->fetch();


/* Pour les infos qui manquent on les recuperent via l'api de tmdb en envotant une rerquete http comme suit : */
$url = "https://api.themoviedb.org/3/movie/" . $tmdbid['tmdbid'] . "?api_key=" . $tmdbApiKey . "&language=fr-FR";


/* $response = file_get_contents($url); on la remplace avec ce qui suit */


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CAINFO, '/opt/lampp/etc/cacert.pem');
$response = curl_exec($ch);
curl_close($ch);



/*var_dump($response);  cette commande affiche le type et et le contenu d une variable, dans le cas ou  file_get_contents($url)echoue elle contient fals 
et effectivement dans ce premier cas elle  contient false */ 
/* on a du texte brut dans response sous forme json , on cree un tableau associatif pour acceder au donnees  */

$donneesTmdb = json_decode($response, true);
 /* true pour tableau associatif, f&lse pour objet php */

 /*print_r($donneesTmdb); pour afficher ce que contient donneeestmdb*/

$overview = $donneesTmdb['overview'];
$runtime  = $donneesTmdb['runtime'];
$poster   = $donneesTmdb['poster_path'];


$url2 = "https://api.themoviedb.org/3/movie/" . $tmdbid['tmdbid'] . "/credits?api_key=" . $tmdbApiKey . "&language=fr-FR";
$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, $url2);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_CAINFO, '/opt/lampp/etc/cacert.pem');
$response2 = curl_exec($ch2);
curl_close($ch2);
$donneesTmdbReal = json_decode($response2, true);

$realisateurs = [];
foreach($donneesTmdbReal['crew'] as $membre){
    if($membre['job']=== 'Director'){
        $realisateurs[]= $membre['name'];
    }

}



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

        <a href="https://www.imdb.com/title/tt<?= $Imdb['imdbid'] ?>" target="_blank">
         Voir sur IMDb ↗
        </a>
        <!--target pout dire ou ouvrir l'onglet , _blank pour dire dans un nouvel onglet -->
        <p><?= htmlspecialchars($overview) ?></p>
        <p><?= $runtime ?> minutes</p>
        <img src="https://image.tmdb.org/t/p/w500<?= $poster ?>" alt="affiche">
        <?php foreach($realisateurs as $real) : ?>
        <span><?= htmlspecialchars($real) ?></span>
        <?php endforeach; ?>



</body>
</html>

