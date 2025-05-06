#!/bin/sh
VSIGN_TARGET_PATH="/var/www/shared/vsign/public"
VSIGN_CLIENT_TARGET_PATH="/var/www/shared/vsign-client/dist"

mkdir -p $VSIGN_TARGET_PATH
mkdir -p $VSIGN_CLIENT_TARGET_PATH

cp -r /var/www/vsign/public/* $VSIGN_TARGET_PATH/
cp -r /var/www/vsign-client/dist/* $VSIGN_CLIENT_TARGET_PATH/

php artisan migrate && php artisan config:cache && php artisan route:cache
exec "$@"
