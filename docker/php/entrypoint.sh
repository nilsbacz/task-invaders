#!/bin/sh
set -e

if [ ! -f /var/www/html/composer.json ]; then
  echo "composer.json not found, creating Symfony 8.0 skeleton..."
  tmp_dir="/tmp/symfony"
  rm -rf "$tmp_dir"
  composer create-project symfony/skeleton:"8.0.*" "$tmp_dir" --no-interaction --no-install
  rsync -a --ignore-existing "$tmp_dir"/ /var/www/html/
  rm -rf "$tmp_dir"
fi

if [ ! -d /var/www/html/vendor ]; then
  echo "Installing composer dependencies..."
  composer install --no-interaction
fi

mkdir -p /var/www/html/var
if [ "$(id -u)" = "0" ]; then
  chown -R www-data:www-data /var/www/html/var
fi

exec "$@"
