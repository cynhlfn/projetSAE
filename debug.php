<?php
require __DIR__ . '/config/init.php';
require __DIR__ . '/config/tmdb.php';

$url = "https://api.themoviedb.org/3/movie/862?language=fr-FR";
$ch  = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "Authorization: Bearer " . TMDB_READ_TOKEN,
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$erreur   = curl_error($ch);
curl_close($ch);

echo "Code HTTP : $code <br>";
echo "Erreur : $erreur <br>";
echo "Réponse : " . substr($response, 0, 200);
