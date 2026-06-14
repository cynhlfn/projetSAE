(ce read me concerne les memebre du groupe uniquement)

# petit read me pour nous aider a faire les chose proprement

# auteur :

HALFOUN Cyndia

## Quelques exemples de syntaxt php :

### Syntaxt de base

- exemple sympa généré par Claude :

```bash
<?php
// Variables
$nom = "Alice";
$age = 21;
$prix = 9.99;
$estActif = true;

// Affichage
echo "Bonjour " . $nom;        // concaténation avec .
echo "Bonjour $nom";           // interpolation directe dans ""

// Conditions
if ($age >= 18) {
    echo "Majeur";
} elseif ($age >= 16) {
    echo "Presque";
} else {
    echo "Mineur";
}

// Boucle foreach (la plus utilisée avec les BDD)
$films = ["Inception", "Matrix", "Interstellar"];
foreach ($films as $film) {
    echo $film . "<br>";
}

// Boucle for classique
for ($i = 0; $i < 10; $i++) {
    echo $i;
}

// Tableaux associatifs (clé => valeur)
$film = [
    "titre"  => "Inception",
    "annee"  => 2010,
    "note"   => 8.8
];
echo $film["titre"]; // "Inception"

// Fonctions
function saluer($prenom) {
    return "Bonjour " . $prenom;
}
echo saluer("Alice");
?>
```

### Exemple html-php

- exemple sympa généré par Claude :

```bash
<!-- fichier films.php -->
<?php $titre = "Ma liste de films"; ?>

<!DOCTYPE html>
<html>
<head>
    <title><?= $titre ?></title>  <!-- <?= ... ?> = raccourci de echo -->
</head>
<body>
    <h1><?= $titre ?></h1>

    <?php
    $films = ["Inception", "Matrix"];
    foreach ($films as $film) : // syntaxe alternative plus lisible dans le HTML
    ?>
        <p><?= $film ?></p>
    <?php endforeach; ?>
</body>
</html>
```

### Exemple de lecture de donnée

- exemple sympa généré par Claude :

```bash
<?php
require 'config/database.php'; // importe $pdo

// --- Récupérer TOUS les films ---
$stmt = $pdo->query("SELECT * FROM films");
$films = $stmt->fetchAll(); // tableau de tous les résultats

foreach ($films as $film) {
    echo $film["titre"] . " - " . $film["annee"] . "<br>";
}

// --- Récupérer UN film par son id ---
//   TOUJOURS utiliser les requêtes préparées quand il y a des variables
//    pour éviter les injections SQL

$id = 5; // vient par exemple de l'URL : $_GET["id"]

$stmt = $pdo->prepare("SELECT * FROM films WHERE id = :id");
$stmt->execute([":id" => $id]);
$film = $stmt->fetch(); // un seul résultat

echo $film["titre"];

// --- Recherche avec LIKE ---
$recherche = "%" . $_GET["q"] . "%";
$stmt = $pdo->prepare("SELECT * FROM films WHERE titre LIKE :recherche");
$stmt->execute([":recherche" => $recherche]);
$films = $stmt->fetchAll();
?>
```

### Exemple d'insertion de donnée

- exemple sympa généré par Claude :

```bash
<?php
// Données qui viennent d'un formulaire HTML (méthode POST)
$titre    = $_POST["titre"];
$annee    = $_POST["annee"];
$synopsis = $_POST["synopsis"];

$stmt = $pdo->prepare(
    "INSERT INTO films (titre, annee, synopsis) VALUES (:titre, :annee, :synopsis)"
);
$stmt->execute([
    ":titre"    => $titre,
    ":annee"    => $annee,
    ":synopsis" => $synopsis
]);

// Récupérer l'id de la ligne qu'on vient d'insérer
$newId = $pdo->lastInsertId();

// Rediriger après insertion (bonne pratique)
header("Location: films.php?success=1");
exit;
?>
```

### Exemple de modification

- exemple sympa généré par Claude :

```bash
<?php
$id       = $_POST["id"];
$titre    = $_POST["titre"];
$annee    = $_POST["annee"];

$stmt = $pdo->prepare(
    "UPDATE films SET titre = :titre, annee = :annee WHERE id = :id"
);
$stmt->execute([
    ":titre" => $titre,
    ":annee" => $annee,
    ":id"    => $id
]);

header("Location: films.php");
exit;
?>
```

### Exemple de supression

- exemple sympa généré par Claude :

```bash
<?php
$id = $_GET["id"]; // vient de l'URL : supprimer.php?id=5

$stmt = $pdo->prepare("DELETE FROM films WHERE id = :id");
$stmt->execute([":id" => $id]);

header("Location: films.php");
exit;
?>
```

### Idee initiale de structure de projet

on prend en exemple la base de données de filmes

- exemple sympa généré par Claude :

```
Projet/
├── config/
│   └── database.php        ← connexion PDO
├── public/
│   ├── index.php           ← page d'accueil
│   ├── films.php           ← liste des films
│   ├── film_detail.php     ← détail d'un film
│   ├── film_ajouter.php    ← formulaire ajout
│   ├── film_modifier.php   ← formulaire modif
│   └── film_supprimer.php  ← suppression
├── sql/
│   └── schema.sql          ← script de création de la BDD
├── assets/
│   ├── css/
│   └── js/
└── README.md
```

## Git et Github

- pour l'explication generale voyez ça comme ça : git manage le repo en local -- github stock le repo sur internet
- j'expliquerai ici la suite des commande utilie des que j'en trouve l'utilité (n'hesite pas a me n'importe n'importe quel question sur ça 😉)
- explication de ce que j'ai fait pour initialiser (cree) le repo du projet
  **NE REPRODUISEZ PAS CETTE PARTIE ELLE EST FAITE UNE SEUL FOIS PAS LE CREATEUR DU DEPO GIT**

```bash
# 1. j'ai d'abord cree une repo sur github

# 2. ensuite j'ai cree un repo local dans htdocs

# 3. Initialise Git dans ce dossier
git init

# 4. j'ai cree un read me


# 5. avec cette commande je prepare tout les fichier du repo locale (.) (pour l'instant que le readme.md) pour etre enregistré
git add .

# 6. je cree le premier commit (sauvgarder la version des fichier choisis)
git commit -m "docs: initialisation du projet"

# 7. Renomme la branche principale en "main"
git branch -M main
# dans le projet locale je renome la branche principle pour qu'elle corresponde a github (important)
# git branch: commande pour géré les branches
# -M : pour renomé la branche locale
# main : nouveau nom de la branche

# 8. Connection du depo local au depo gitHub
#    (l'URL vient de GitHub après avoir créé le dépôt sur le site)
git remote add origin https://github.com/cynhlfn/projetSAE.git
# pour connecté ton projet locale au repo github
# git remote add : on demande a git d'ajouté un lien vers un repo distant
# origin : nom du projet distant


# 9. Envoie le code sur GitHub
git push -u origin main
#git push : envoie les fichier commité de mon repo locale vers github
# -u : on dit a git : souvient toi de ce lien entre la branche main locale et la branche main distante
# origin : nom du repo distant
# main : la branche locale enoyé sur github

```

```
GitHub → ton dépôt → Settings → Branches → Add rule
→ Branch name pattern : main
→ Cocher : "Require a pull request before merging"
→ Save changes
```

- Ainsi personne (pas même moi) ne peut pusher directement sur main. Tout passe par une Pull Request.

### Gestion d'une pull request (moi-cyndia)

```
1. aller sur GitHub → onglet "Pull Requests"
2. Cliquer sur la PR en question
3. Lire les fichiers modifiés (onglet "Files changed")
4. Si tout va bien → bouton vert "Merge pull request" → "Confirm merge"
5. Supprimer la branche mergée (bouton "Delete branch" qui apparaît)
```

### apres chaque merge (mettre le repo local a jour)

```bash
git checkout main
git pull origin main
# → Récupère sur ton ordi le main qui vient d'être mis à jour sur GitHub
```

### Résoudre un conflit (si GitHub signale un conflit)

(Un conflit arrive quand deux personnes ont modifié la même ligne du même fichier.)

```bash
# Tu es sur main, tu veux merger la branche de ton collègue
git checkout main
git pull origin main

# Tu récupères sa branche
git checkout feature/liste-films
git pull origin feature/liste-films

# Tu tentes le merge
git merge main
# → Git affiche : CONFLICT in public/films.php

# Tu ouvres le fichier concerné, tu verras ça :
# <<<<<<< HEAD
# ton code
# =======
# son code
# >>>>>>> main

# Tu choisis quoi garder (ou tu combines les deux)
# Tu supprimes les balises <<<, ===, >>>

# Tu valides la résolution
git add public/films.php
git commit -m "fix: résolution conflit sur films.php"
git push origin feature/liste-films
# → La PR est maintenant mergeable sur GitHub
```

### Pour les autre memebre du groupe

- chacun d'entre vous me donne sont compte github -- je vous ajoute comme contributeur au projet
- vous allez ensuite cree un repo sur vos ordinateur (dans htdocs)
- ensuite executer la commande suivante

- good to do :

```bash
# Dire à Git qui tu es (apparaît dans les commits)
git config --global user.name "Prénom Nom"
git config --global user.email "ton.email@example.com" #celui de github c'est important

# Vérifier que c'est bien enregistré
git config --list
```

- cloner le depos git :(dans votre dossier que vous avez cree dans htdocs)

```bash
# Télécharge une copie complète du dépôt sur ton ordi
git clone https://github.com/cynhlfn/projetSAE.git
```

#### Avant de commencer à coder (à faire à chaque nouvelle tâche)

```bash
# 1. Revenir sur main
git checkout main

# 2. Récupérer les dernières modifications de tes coéquipiers
git pull origin main
# → Met ton main local à jour avec ce qui est sur GitHub
#   TOUJOURS faire ça avant de créer une branche, sinon tu pars d'un main périmé

# 3. Créer ta branche de fonctionnalité
git checkout -b feature/ajout-film
# -b = "create branch" : crée la branche ET bascule dessus en une commande (changer le nom de la branche selon ce que vous faite)

# Vérifier sur quelle branche on est
git branch
# → * feature/ajout-film
#     main
```

#### Pendant que tu codes

```bash
# Voir l'état de tes fichiers à tout moment
git status
# → Affiche :
#   - fichiers modifiés (en rouge = pas encore stagés)
#   - fichiers stagés (en vert = prêts pour le commit)
#   - fichiers non trackés (nouveaux fichiers)

# Voir exactement ce qui a changé ligne par ligne
git diff
# → Affiche les lignes ajoutées (+) et supprimées (-) depuis le dernier commit

# Ajouter UN fichier spécifique au staging
git add public/film_ajouter.php

# Ajouter TOUS les fichiers modifiés d'un coup
git add .
# Le point = "tout ce dossier et sous-dossiers"
# ⚠️ Vérifier avec git status avant de faire git add . pour ne pas ajouter des fichiers indésirables

# Créer un commit avec les fichiers stagés
git commit -m "feat: création du formulaire d'ajout de film"

# Voir l'historique des commits
git log --oneline
# → Affiche quelque chose comme :
#   a1b2c3d feat: création du formulaire d'ajout de film
#   e4f5g6h db: schéma initial de la base de données
```

- Rythme recommandé pour les commits :

```
✅ Un commit = une chose logique accomplie
   "feat: formulaire HTML de la page ajout"
   "feat: traitement PHP du formulaire ajout"
   "fix: correction validation champ année"

❌ Pas de commit fourre-tout
   "modifications diverses"
   "travail du soir"
```

#### Envoyer son travail sur GitHub

```bash
# Premier push d'une nouvelle branche
git push origin feature/ajout-film
# "origin" = le dépôt GitHub
# "feature/ajout-film" = la branche à envoyer

# Les push suivants sur la même branche (plus court)
git push
# Fonctionne si tu as déjà pushé cette branche une fois
```

#### Ouvrir une Pull Request

```
1. Va sur GitHub → tu verras une bannière jaune
   "feature/ajout-film had recent pushes" → bouton "Compare & pull request"
2. Clique dessus
3. Titre : "feat: ajout du formulaire d'ajout de film"
4. Description : explique ce que tu as fait, ce que le relecteur doit tester
5. "Create pull request"
6. Préviens le responsable (toi) que la PR est prête
```

#### Rester à jour pendant que tu codes (le rebase)

Si pendant que tu travailles sur ta branche, tes coéquipiers ont mergé du code dans main, tu dois intégrer leurs modifications dans ta branche. Il y a deux façons : merge ou rebase.

Le rebase est plus propre car il réécrit l'historique comme si tu avais commencé ta branche depuis le main le plus récent :

```bash
# 1. Sauvegarder d'abord ton travail en cours
git add .
git commit -m "feat: travail en cours sur le formulaire"

# 2. Récupérer le main à jour
git fetch origin
# → "fetch" télécharge les infos de GitHub SANS modifier tes fichiers locaux
#   (contrairement à pull qui télécharge ET fusionne)

# 3. Rebaser ta branche sur le main à jour
git rebase origin/main
# → Rejoue tes commits AU-DESSUS du main mis à jour
# → Si aucun conflit : terminé, ta branche est à jour
# → Si conflit : Git s'arrête et te demande de le résoudre

# En cas de conflit pendant le rebase :
# 1. Ouvre le fichier en conflit, résous-le
git add fichier-en-conflit.php
git rebase --continue
# → Continue le rebase au commit suivant

# Si tu veux tout annuler et revenir avant le rebase :
git rebase --abort

# 4. Push après rebase (il faut forcer car l'historique a changé)
git push --force-with-lease origin feature/ajout-film
# --force-with-lease = force mais annule si quelqu'un d'autre a pushé entre temps (plus sûr que --force)
```

- fetch vs pull :
  git fetch → télécharge les infos de GitHub, ne touche pas à tes fichiers
  git pull → fetch + merge automatique (= git fetch puis git merge)

#### Récapitulatif — Le workflow complet d'une fonctionnalité

```bash
# ── DÉBUT DE TÂCHE ──────────────────────────────────
git checkout main
git pull origin main
git checkout -b feature/ma-fonctionnalite

# ── PENDANT LE DÉVELOPPEMENT (répéter) ──────────────
# ... tu codes ...
git add .
git status          # vérifier ce qu'on va commiter
git commit -m "feat: description claire de ce qui est fait"

# Si le main a avancé entre temps :
git fetch origin
git rebase origin/main

# ── FIN DE TÂCHE ────────────────────────────────────
git push origin feature/ma-fonctionnalite
# → Ouvrir la Pull Request sur GitHub
# → Prévenir le responsable
```

**Ne jamais push ses modification dans la branche main**

**faire des commite propre et claire**

- Convention des nommage de branche

```
type/description-courte-en-kebab-case
```

- Les types courants :

```
feature/ → nouvelle fonctionnalité
docs/ → documentation
style/ → CSS, mise en forme
db/ → base de données, SQL
```

- exemple sympa généré par Claude

```
feature/liste-films
feature/ajout-film
feature/modification-film
feature/suppression-film
feature/detail-film
feature/recherche-films
fix/requete-jointure-genres
db/schema-initial
db/import-donnees
docs/readme
style/page-accueil
```

- pour notre projet on aura a peut prés **2 a 3** branche **NE LES NOMEE PAS PAR VOTRE NOM**

- voici des exemples de ce qu'il faut faire et ce qu'il ne faut pas faire

```bash
# ✅ Bons commits
git commit -m "feat: ajout du formulaire d'ajout de film"
git commit -m "fix: correction de la requête SQL sur les genres"
git commit -m "style: amélioration du design de la page d'accueil"
git commit -m "docs: mise à jour du README avec instructions d'install"
git commit -m "db: ajout de la table affiches dans schema.sql"

# ❌ Mauvais commits
git commit -m "modif"
git commit -m "ça marche"
git commit -m "update"
```

- toujour recupere les dernier changement sur ton repo locale avant de commencer : (a chaque fois que des modification on ete faite par les autes dans le main du depo git)

```bash
git checkout main
git pull origin main              # récupère les dernières modifs
git checkout -b feature/ma-tache # crée ta branche depuis le main à jour
```

#### Structure a retenir

- dans votre branche personnel  
  -faire des commite regulier
- cree un full resquest quand une version est prete

### Exemple sympa CRUD pour les films generer par Claude

```bash
<?php
// public/films.php
require '../config/database.php';

// Recherche optionnelle
$recherche = $_GET["q"] ?? ""; // ?? = valeur par défaut si non défini

if ($recherche) {
    $stmt = $pdo->prepare("SELECT * FROM films WHERE titre LIKE :q ORDER BY titre");
    $stmt->execute([":q" => "%" . $recherche . "%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM films ORDER BY titre");
}
$films = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Films</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h1>Liste des films</h1>

    <!-- Formulaire de recherche -->
    <form method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="q" class="form-control"
                   placeholder="Rechercher..." value="<?= htmlspecialchars($recherche) ?>">
            <button class="btn btn-primary">Rechercher</button>
        </div>
    </form>

    <a href="film_ajouter.php" class="btn btn-success mb-3">+ Ajouter un film</a>

    <table class="table table-striped">
        <thead>
            <tr><th>Titre</th><th>Année</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($films as $film) : ?>
            <tr>
                <td><?= htmlspecialchars($film["titre"]) ?></td>
                <td><?= $film["annee"] ?></td>
                <td>
                    <a href="film_detail.php?id=<?= $film["id"] ?>"
                       class="btn btn-sm btn-info">Voir</a>
                    <a href="film_modifier.php?id=<?= $film["id"] ?>"
                       class="btn btn-sm btn-warning">Modifier</a>
                    <a href="film_supprimer.php?id=<?= $film["id"] ?>"
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('Supprimer ce film ?')">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>

```
