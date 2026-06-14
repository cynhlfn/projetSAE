# WIKIFILM - ton wikipedia de film

## Description du projet

## Explication des choix techniques

### Base de données

Nous utilisons des données reccuille du site https://grouplens.org/datasets/movielens/ qui regroupe plusieur data set dont celle que nous utilisons dans ce projet .
notre data set est celle regrouppant les données des films de Movelens.
--> la version recommandé pour les projet fait dans le cadre educatif (ml-latest-small.zip)

- Proprieté :
  taille : 1MB
  Films : 9000
  Utilisateurs : 600

- fichiers contenus:
  - movies.csv :
    movieid (exemple : 1)
    title (exemple : Toy Story (1995))
    genre (exemple : Adventure|Animation|Children|Comedy|Fantasy)

  - tags.csv :
    userId (exemple : 2 )
    movieId (exemple : 60756)
    tag (exemple : funny) --> un tage (petit text) qu'un utilisateur vas associé a un film
    timestamp (exemple : 1445714994) <-- ici temps en seconde depuit 1/1/1970

  - ratings.csv :
    userId (exemple : 1)
    movieId (exemple : 1)
    rating (exemple : 4.0)
    timestamp (exemple : 964982703) <-- ici temps en seconde depuit 1/1/1970

  - links.csv :
    movieId (exemple : 1)
    imdbId (exemple : 0114709) <-- afin de lier vers IMDB (imdb.com/title/tt0114709)
    tmdbId (exemple : 862) <-- afin de lier vers TheMovieDB (themoviedb.org/movie/862) - grace a ces deux derniers on pourra récupérer des données supplementaire comme les affiches de films .

Nous avons par la suite reflechit a un model entité association pour notre site qui nous aiderai a correctement ranger les données fournis par la data set.

voici le model conceptuel des données de notre base de données

![MEA du projet](images/MEA.png)

- nous avons fait le choix de cree une table separer années pour eviter de stocqué une certaine années plusieur fois (si il y a 50 film sortie en 1998 on aura pas 50 fois l'années stocké mais plutot une seul fois dans la table années)

### Deploiement

- Afin de remedier au problem de base de données locale (sur phpmyadmin) et que chacun des membre du groupe doivent la cree de sont coté , ainsi que les modification qui serons que local donc pas partager, nous avons decidé de deployer la base de données sur railway (https://railway.com/) .
- nous avons deployer l'application aussi sur railway
- notre travail est en CD (continuous deployment) ce qui fait qu'a chaque push dans le main le projet ce deploie automatiquement
- on suit le deploiement sur railway
