# api-recette
API de recettes de cuisine avec CRUD sur Symfony 5

## Prérequis:

PHP 7.2.5 ou supérieur et ces extensions PHP ;
Composer, qui est utilisé pour installer les packages PHP.

## Les étapes à suivre :


### 1 Composer :

```bash
composer install

```

### 2 Configuration de base de données :

Les paramètres de la connexion à la base de donne sont stockées dans la variable DATABASE_URL qui existe dans la fichier .env.
    Exemple:

```bash

    DATABASE_URL='mysql://db_user:db_password@127.0.0.1:3306/db_name'
    db_user: root
    db_password: root
    db_name: nom de votre base par exemple 'recettes_cuisine'
```

### 3 Commandes à lancer : 

```bash
  
php bin/console doctrine:database:create

php bin/console doctrine:migrations:migrate

php bin/console server:run 
```

### 4 Pour réaliser les test nos allons utiliser Postman https://www.postman.com/

## POST — Create
Votre demande POST devrait ressembler à ceci:

http://127.0.0.1:8000/recettes/add

### Les champs obligatoires sont titre et liste_ingredients.

Exemple des données à saisir en JSON :

```json
{
    "titre": "Tarte aux fraises",
    "sous_titre": "Tarte",
    "liste_ingredients": "fraises sucre farine beurre"
}
```

## GET — Read
Votre demande GET de toutes les recettes devrait ressembler à ceci:

http://127.0.0.1:8000/recettes/

Votre demande GET d'une recette devrait ressembler à ceci:

http://127.0.0.1:8000/recettes/1


## PUT — Update
Votre demande PUT devrait ressembler à ceci:

http://127.0.0.1:8000/recettes/update/1

Exemple des données à saisir en JSON :

```json
{
    "titre": "Tarte aux fraises",
    "sous_titre": "Tarte",
    "liste_ingredients": "fraises sucre farine beurre lait"
}
```

## DELETE — Delete
Votre demande DELETE devrait ressembler à ceci:

http://127.0.0.1:8000/recettes/delete/1
