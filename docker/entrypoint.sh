#!/bin/sh
set -e

# Dossiers d'écriture de l'application
mkdir -p /var/www/html/storage/logs /var/www/html/storage/ratelimit /var/www/html/public/assets/uploads
chmod -R 777 /var/www/html/storage /var/www/html/public/assets/uploads

# Attente de la base puis création des comptes depuis le .env :
# ADMIN_USER = accès complet, EDITOR_USER (facultatif) = contenu uniquement
until ADMIN_PASSWORD="$ADMIN_PASSWORD" php /var/www/html/bin/create-admin.php "${ADMIN_USER:-admin}" admin; do
    echo "En attente de la base de données..."
    sleep 3
done

if [ -n "$EDITOR_USER" ] && [ -n "$EDITOR_PASSWORD" ]; then
    ADMIN_PASSWORD="$EDITOR_PASSWORD" php /var/www/html/bin/create-admin.php "$EDITOR_USER" editor
fi

exec "$@"
