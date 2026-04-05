# Installation guide

## System requirements

- [`PHP`](https://www.php.net/downloads) 8.2 or higher.
- [`Composer`](https://getcomposer.org/download/) for dependency management.
- [`Node.js`](https://nodejs.org/) 18 or higher with npm.
- [`Yii2`](https://github.com/yiisoft/yii2) 2.0.54+ or 22.x.

## Installation

### Clone the repository

```bash
git clone https://github.com/yii2-framework/app-inertia-vue.git
cd app-inertia-vue
```

### Install dependencies

```bash
composer install
```

> **Note:** `npm install` runs automatically via [php-forge/foxy](https://github.com/php-forge/foxy) during `composer install`.

### Run database migrations

```bash
php yii migrate
```

This creates the `user` table and seeds a default admin user (`admin` / `admin`) for initial local setup.

**Important:** Change these credentials immediately and do not use them in shared or production environments.

### Start the development servers

In one terminal, start the Vite development server (port 5173):

```bash
npm run dev
```

In another terminal, start the Yii2 PHP development server:

```bash
php yii serve
```

Open your browser at `http://localhost:8080`. Vite handles hot module replacement automatically.

### Build for production

```bash
npm run build
```

## Docker setup

As an alternative to local development, use the included `docker-compose.yml`:

```bash
docker compose up -d
```

This starts a PHP 8.5 Apache container that:

- Maps the project to `/app` inside the container.
- Symlinks `public/` to `/app/web` for Apache's DocumentRoot.
- Exposes the application on `http://localhost:8000`.

### Running in Docker

```bash
# Install dependencies (foxy installs npm packages automatically)
docker compose exec php composer install

# Build frontend assets
docker compose exec php npm run build

# Run database migrations
docker compose exec php php yii migrate

# Run tests
docker compose exec php vendor/bin/codecept run
```

The `public/.htaccess` file handles URL rewriting for pretty URLs under Apache.

## Next steps

- ⚙️ [Configuration Reference](configuration.md)
- 💡 [Usage Examples](examples.md)
- 🧪 [Testing Guide](testing.md)
