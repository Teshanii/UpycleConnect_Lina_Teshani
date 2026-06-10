<?php
// MODÈLE de config — copie ce fichier en config.php et remplis les vraies clés
// (ou mieux : utilise le fichier .env comme expliqué dans le README)
define('STRIPE_PUBLIC_KEY', getenv('STRIPE_PUBLIC_KEY'));
define('STRIPE_SECRET_KEY', getenv('STRIPE_SECRET_KEY'));