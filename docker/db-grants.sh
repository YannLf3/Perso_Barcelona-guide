#!/bin/sh
set -e

# Utilisateur applicatif limité à SELECT, INSERT et UPDATE (ni DROP, ni FILE)
# DELETE uniquement sur BT_Message : suppression des demandes par la cliente et purge RGPD
mysql -uroot -p"$MYSQL_ROOT_PASSWORD" <<SQL
REVOKE ALL PRIVILEGES ON \`$MYSQL_DATABASE\`.* FROM '$MYSQL_USER'@'%';
GRANT SELECT, INSERT, UPDATE ON \`$MYSQL_DATABASE\`.* TO '$MYSQL_USER'@'%';
GRANT DELETE ON \`$MYSQL_DATABASE\`.\`BT_Message\` TO '$MYSQL_USER'@'%';
FLUSH PRIVILEGES;
SQL
