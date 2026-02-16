# task-invaders

## Docker Symfony 8.0 Setup

### Stack
- PHP 8.4 (FPM)
- Nginx
- MySQL 8.4
- MailHog (dev only)

### Quick start (dev)
```bash
docker compose up -d --build
```

Services and ports:
- App: http://localhost:8080
- MailHog UI: http://localhost:8025
- MySQL: localhost:3306 (user `app`, pass `app`, db `app`)

### Production stack
```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build
```

### How the skeleton is created
On the first PHP container start, the entrypoint checks for `composer.json`. If missing, it runs:
- `composer create-project symfony/skeleton:"8.0.*" /tmp/symfony --no-install`
- Syncs the skeleton into the project root
- `composer install`

This keeps the repo clean while still generating a full Symfony 8.0 app locally.

### Notes
- The project root is mounted into the PHP and Nginx containers.
- `vendor/`, `var/`, local env files, and other build artifacts are ignored in `.gitignore`.
- Update DB creds in `docker-compose.yml`/`docker-compose.prod.yml` as needed.
- The PHP container runs as your host UID/GID (defaults to 1000/1000). Override with `UID`/`GID` env vars if needed.

### Xdebug (PhpStorm)
Xdebug is enabled in the dev PHP image and connects to your host on port 9003.

PhpStorm setup:
- Settings > PHP > Debug: listen on port 9003
- Settings > PHP > Servers: add `localhost:8080`
- set path mapping `/var/www/html` -> project root
- set idekey: PHPSTORM
- Click "Start Listening for PHP Debug Connections"

If you need to disable Xdebug temporarily:
```bash
XDEBUG_MODE=off docker compose up -d --build
```

### Common commands
```bash
# Symfony console
docker compose exec php php bin/console

# Composer
docker compose exec php composer

# Rebuild containers
docker compose up -d --build
```

## 🧠 Development notes
- ** phpstan **
  ```bash
  vendor/bin/phpstan analyse
  ```

- **Code Fixer**
  ```bash
  vendor/bin/phpcbf
  ```
