Ce projet et un projet scolaire réaliser avec le framework symfony, il s'agit d'une application web fictive pour un traiteur permettant la visualisation et la commande de différents menus.
Cette application permet également aux différents type d'utilisateur d'accéder à un esapce personnel qui propose différentes types de fonctionnalités selon le rôle de l'utilisateur.
Les rôle employé et administrateur permettent de gérer les commandes clients et les autres différentes fonctionnalités du site web.

# Prérequis:

Git(évidemment si on utilise git clone pour le déploiment)

PHP 8.3 NTS avec les extensions suivantes activé dans le php.ini: curl, fileinfo, gd, mbstring, mysqli, openssl, pdo_mysql et php_mongodb.dll(la version que j'ai utiliser est trouvable ici https://pecl.php.net/package/mongodb/1.21.5/windows).

Composer

Mysql Server 8.0(vous devez créer votre base de données pour pouvoir l'ajouter dans votre .env ensuite)

MongoDB Server 8.2(vous devez créer votre base de données pour pouvoir l'ajouter dans votre .env ensuite)

Un IDE

Pour la gestion des mail vous pouvez utiliser des outil comme mailtrap, mailpit ou autres

# Configuration du déploiement local:

Une fois tout les prérequis installer il faut récuperer le lien de ce repository et exécuter la commande git clone "le lien du repository".

Suite à ça vous devez renommer le .env.example à la racine du dossier en .env et l'adapter avec vos propre informations(Bases de données MySql et MongoDB, SMTP(mailtrap ou mailpit etc)) et veiller à ne pas modifier votre APP_ENV=dev.

Une fois le repository correctement cloner sur votre système et le .env configurer il faut exécuter les commande composer install et php bin/console importmap:install.

Une fois cela fait créer votre base de donnée Mysql et importer l'export fournie dans le dossier "Données d'intégrations pour les bases de données", si vous voulez une base de données vierge exécuter la commande php bin/console doctrine:migrations:migrate cela créera les table du projet dans la base de données MySql que vous avez créer.

Et pour la base de données MongoDB créer votre base et votre collection puis importer l'export fournie dans le dossier "Données d'intégrations pour les bases de données", sinon laisser votre collection vide des données seront ajouter à chaque nouvelle commande que vous créerez via le site(déployer localement) uniquement.

Important: ne déployer pas le site en local tant que vous n'avez pas créer vos bases de données et importer les export fournie(ou effectuer les migrations )et que vous ne les avez pas correctement indiquer dans le .env
# Déploiement local avec PHP:

Utiliser le serveur interne de php pour déployer le site en local avec la commande php -S localhost:8000 -t public.
