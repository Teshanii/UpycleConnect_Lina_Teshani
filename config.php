<?php
// config.php — les clés sont dans le fichier .env (jamais commité sur GitHub)
// On les lit depuis les variables d'environnement Docker.
define('STRIPE_PUBLIC_KEY', getenv('STRIPE_PUBLIC_KEY'));
define('STRIPE_SECRET_KEY', getenv('STRIPE_SECRET_KEY'));
define('BASE_URL', getenv('BASE_URL') ?: 'http://localhost');
