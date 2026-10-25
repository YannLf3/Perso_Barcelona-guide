#!/bin/sh
set -e

# Dossiers d'écriture de l'application
mkdir -p /var/www/html/storage/logs /var/www/html/storage/ratelimit /var/www/html/public/assets/uploads
chmod -R 777 /var/www/html/storage /var/www/html/public/assets/uploads

# Attente de la base puis création du compte administrateur depuis le .env
until php /var/www/html/bin/create-admin.php; do
    echo "En attente de la base de données..."
    sleep 3
done

exec "$@"
