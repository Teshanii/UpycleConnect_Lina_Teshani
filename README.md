# UpcycleConnect

Plateforme web d'upcycling mettant en relation particuliers, artisans et salariés
(dépôt d'annonces, dépôt d'objets en box, ateliers, forum, projets, paiements...).
Projet réalisé dans le cadre du Projet Annuel 2 ESGI.

- **Front** : PHP 8.2 + Apache, JavaScript, Bootstrap 5
- **API** : Go (net/http)
- **Base de données** : MySQL 8
- **Paiements** : Stripe (mode test)
- **Emails** : PHPMailer (SMTP Gmail)
- **PDF** : FPDF (factures, attestations)
- **Déploiement** : Docker Compose (3 conteneurs) + tunnel Cloudflare pour l'accès externe

## Prérequis

- Docker et Docker Compose installés
- Un fichier `.env` à la racine du projet

## Structure du projet (résumé)

- `api_backend/` : API Go + schéma de la base (`database.sql`)
- `particulier/`, `artisan/`, `salarie/`, `admin_backoffice/` : les 4 espaces du site
- `includes/` : connexion BDD (`db.php`), mail, traductions
- `docker-compose.yml`, `dockerfile.php` : configuration Docker

## 1. Installation

Cloner le dépôt :

```bash
git clone https://github.com/Teshanii/UpycleConnect_Lina_Teshani.git
cd UpycleConnect_Lina_Teshani
```

Créer le fichier `.env` à la racine avec vos clés :

```env
STRIPE_PUBLIC_KEY=pk_test_xxxxx
STRIPE_SECRET_KEY=sk_test_xxxxx
GMAIL_USER=votre.adresse@gmail.com
GMAIL_PASSWORD=mot_de_passe_application
BASE_URL=http://localhost
```

Lancer les conteneurs :

```bash
docker compose up -d --build
```

Au premier démarrage, MySQL crée automatiquement la base à partir de
`api_backend/database.sql` (structure + référentiels + comptes de base).

## 2. Charger les données

Deux bases sont fournies :

- `base_vide.sql` : la structure seule (+ référentiels et comptes de base)
- `base_remplie.sql` : la structure + un grand nombre de données de démonstration

Pour charger la base remplie dans le conteneur qui tourne :

```bash
docker exec -i upcycle_db mysql -u root -proot upcycle_connect < base_remplie.sql
```

Astuce : le script `install.sh` fait tout automatiquement (démarrage + chargement de la base remplie).

## 3. Accès au site

- En local : http://localhost
- Accès externe (démonstration) : https://upcycle-connect.store (via le tunnel Cloudflare)

## Comptes de démonstration (base remplie)

Mot de passe pour tous les comptes ajoutés : **Demo_2026**

| Rôle        | Email                       |
| ----------- | --------------------------- |
| Particulier | marie.dubois@gmail.com      |
| Artisan     | thomas.leroy@boiscreatif.fr |
| Salarié     | paul.mercier@upcycle.fr     |

Le compte administrateur est celui défini dans `api_backend/database.sql`.

## Commandes utiles

- Voir les logs : `docker compose logs -f`
- Reconstruire l'API après une modification du code Go : `docker compose up -d --build api`
- Tout arrêter : `docker compose down`
- Tout arrêter ET effacer la base (repart de zéro) : `docker compose down -v`
