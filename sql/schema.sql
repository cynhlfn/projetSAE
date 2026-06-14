CREATE DATABASE IF NOT EXISTS movielens CHARACTER SET utf8mb4;
USE movielens;

CREATE TABLE annee (
    idAn INT          AUTO_INCREMENT,
    an   INT          NOT NULL,
    CONSTRAINT pk_annee        PRIMARY KEY (idAn),
    CONSTRAINT uq_annee_an     UNIQUE      (an)
);

CREATE TABLE film (
    idFilm INT          NOT NULL,
    titre  VARCHAR(255) NOT NULL,
    idAn   INT,
    CONSTRAINT pk_film         PRIMARY KEY (idFilm),
    CONSTRAINT fk_film_annee   FOREIGN KEY (idAn)
        REFERENCES annee(idAn) ON DELETE SET NULL
);

CREATE TABLE genre (
    idGenre  INT         AUTO_INCREMENT,
    nomGenre VARCHAR(50) NOT NULL,
    CONSTRAINT pk_genre        PRIMARY KEY (idGenre),
    CONSTRAINT uq_genre_nom    UNIQUE      (nomGenre)
);

CREATE TABLE film_genre (
    idFilm  INT NOT NULL,
    idGenre INT NOT NULL,
    CONSTRAINT pk_film_genre         PRIMARY KEY (idFilm, idGenre),
    CONSTRAINT fk_film_genre_film    FOREIGN KEY (idFilm)
        REFERENCES film(idFilm)  ON DELETE CASCADE,
    CONSTRAINT fk_film_genre_genre   FOREIGN KEY (idGenre)
        REFERENCES genre(idGenre) ON DELETE CASCADE
);

CREATE TABLE fichefilm (
    idFiche INT         AUTO_INCREMENT,
    idFilm  INT         NOT NULL,
    imdbid  VARCHAR(20),
    tmdbid  INT,
    CONSTRAINT pk_fichefilm          PRIMARY KEY (idFiche),
    CONSTRAINT uq_fichefilm_film     UNIQUE      (idFilm),
    CONSTRAINT fk_fichefilm_film     FOREIGN KEY (idFilm)
        REFERENCES film(idFilm) ON DELETE CASCADE
);

CREATE TABLE utilisateur (
    idUtilisateur INT NOT NULL,
    CONSTRAINT pk_utilisateur  PRIMARY KEY (idUtilisateur)
);

CREATE TABLE noter (
    idUtilisateur INT          NOT NULL,
    idFilm        INT          NOT NULL,
    note          DECIMAL(2,1) NOT NULL,
    dateRating    INT,
    CONSTRAINT pk_noter              PRIMARY KEY (idUtilisateur, idFilm),
    CONSTRAINT fk_noter_utilisateur  FOREIGN KEY (idUtilisateur)
        REFERENCES utilisateur(idUtilisateur) ON DELETE CASCADE,
    CONSTRAINT fk_noter_film         FOREIGN KEY (idFilm)
        REFERENCES film(idFilm) ON DELETE CASCADE
);

CREATE TABLE tag (
    idTag   INT          AUTO_INCREMENT,
    tagText VARCHAR(255) NOT NULL,
    CONSTRAINT pk_tag              PRIMARY KEY (idTag),
    CONSTRAINT uq_tag_text         UNIQUE      (tagText)
);

CREATE TABLE taguer (
    idUtilisateur INT NOT NULL,
    idFilm        INT NOT NULL,
    idTag         INT NOT NULL,
    dateT         INT,
    CONSTRAINT pk_taguer              PRIMARY KEY (idUtilisateur, idFilm, idTag),
    CONSTRAINT fk_taguer_utilisateur  FOREIGN KEY (idUtilisateur)
        REFERENCES utilisateur(idUtilisateur) ON DELETE CASCADE,
    CONSTRAINT fk_taguer_film         FOREIGN KEY (idFilm)
        REFERENCES film(idFilm)        ON DELETE CASCADE,
    CONSTRAINT fk_taguer_tag          FOREIGN KEY (idTag)
        REFERENCES tag(idTag)          ON DELETE CASCADE
);