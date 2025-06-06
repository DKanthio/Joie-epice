# 🌶️ Joie-Épice

Joie-Épice est un projet Symfony pour une boutique en ligne 🌶️.

## ✅ Guide d'installation

1. **Télécharger le fichier ZIP**
2. **Extraire le fichier**
3. **Ouvrir le dossier extrait dans l'invite de commande**

## ⚙️ Configuration de l'environnement

1. Ouvrir le fichier `.env` à la racine du projet.
2. Ajouter ou modifier la ligne suivante avec votre clé API SendGrid :

MAILER_DSN=sendgrid+api://<YOUR_SENDGRID_API_KEY>@default


> ⚠️ Ne partagez pas cette clé publiquement.

## 📦 Installation des dépendances

Exécuter la commande suivante :

composer install

## 🗄️ Configuration de la base de données

1. Créer la base de données nommée `epice` :

php bin/console doctrine:database:create

2. Lancer les migrations :

php bin/console doctrine:migrations:migrate

3. Charger les données fictives (fixtures) :

php bin/console doctrine:fixtures:load


## ▶️ Lancer le serveur Symfony

Une fois tout configuré, démarrer le serveur avec :

symfony server:start

L'application sera alors accessible via :

http://127.0.0.1:8000

---

🌶️ Merci d’avoir choisi Joie-epicee
