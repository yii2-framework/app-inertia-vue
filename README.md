<!-- markdownlint-disable MD041 -->
<p align="center">
    <picture>
        <source media="(prefers-color-scheme: dark)" srcset="https://www.yiiframework.com/image/design/logo/yii3_full_for_dark.svg">
        <source media="(prefers-color-scheme: light)" srcset="https://www.yiiframework.com/image/design/logo/yii3_full_for_light.svg">
        <img src="https://www.yiiframework.com/image/design/logo/yii3_full_for_dark.svg" alt="Yii Framework" width="80%">
    </picture>
    <h1 align="center">Yii2 Inertia.js + Vue 3 Application</h1>
    <br>
</p>
<!-- markdownlint-enable MD041 -->

<p align="center">
    <a href="https://github.com/yii2-framework/app-inertia-vue/actions/workflows/build.yml" target="_blank">
        <img src="https://img.shields.io/github/actions/workflow/status/yii2-framework/app-inertia-vue/build.yml?style=for-the-badge&label=build&logo=github" alt="Build">
    </a>
    <a href="https://github.com/yii2-framework/app-inertia-vue/actions/workflows/static.yml" target="_blank">
        <img src="https://img.shields.io/github/actions/workflow/status/yii2-framework/app-inertia-vue/static.yml?style=for-the-badge&label=PHPStan&logo=github" alt="PHPStan">
    </a>
    <a href="https://github.com/yii2-framework/app-inertia-vue/actions/workflows/ecs.yml" target="_blank">
        <img src="https://img.shields.io/github/actions/workflow/status/yii2-framework/app-inertia-vue/ecs.yml?style=for-the-badge&label=ECS&logo=github" alt="ECS">
    </a>
</p>

<p align="center">
    <strong>A modern Yii2 application template with Inertia.js, Vue 3, Tailwind CSS, and Flowbite</strong><br>
    <em>Server-driven SPA with authentication, dark mode, Codeception tests, and PHPStan</em>
</p>

## Screenshots

| Light                                           | Dark                                          |
| ----------------------------------------------- | --------------------------------------------- |
| ![Home Light](docs/images/home-light.png)       | ![Home Dark](docs/images/home-dark.png)       |
| ![Login Light](docs/images/login-light.png)     | ![Login Dark](docs/images/login-dark.png)     |
| ![Users Light](docs/images/users-light.png)     | ![Users Dark](docs/images/users-dark.png)     |
| ![Contact Light](docs/images/contact-light.png) | ![Contact Dark](docs/images/contact-dark.png) |

## Stack

| Layer            | Technology                                 |
| ---------------- | ------------------------------------------ |
| Backend          | PHP 8.2+, Yii2, Inertia.js server adapter  |
| Frontend         | Vue 3, Inertia.js client, Vite             |
| CSS              | Tailwind CSS v4, Flowbite, Flowbite Vue    |
| CAPTCHA          | Cloudflare Turnstile                       |
| Testing          | Codeception (unit, functional, acceptance) |
| Static Analysis  | PHPStan (max level)                        |
| Asset Management | php-forge/foxy (npm via Composer)          |

## Features

- Inertia.js SPA navigation (no full page reloads)
- Vue 3 Composition API with `<script setup>`
- Tailwind CSS v4 with Flowbite components
- Dark mode toggle (localStorage + system preference)
- User authentication (login, signup, email verification, password reset)
- Admin user listing with server-side sorting, filtering, and pagination
- Contact form with Cloudflare Turnstile CAPTCHA
- Responsive split-card design with brand gradient
- JetBrains Mono + Inter typography
- Codeception tests (unit, functional, acceptance)
- PHPStan max level static analysis
- ECS coding standard

## Quick start

```bash
git clone https://github.com/yii2-framework/app-inertia-vue.git
cd app-inertia-vue
composer install
php yii migrate
```

Start the development servers:

```bash
npm run dev          # Vite dev server (terminal 1)
php yii serve        # PHP dev server (terminal 2)
```

Open `http://localhost:8080`. Default admin credentials: `admin` / `admin`.

**Important:** Change default credentials immediately. Do not use them in production.

## Documentation

- 📚 [Installation Guide](docs/installation.md)
- ⚙️ [Configuration Reference](docs/configuration.md)
- 💡 [Usage Examples](docs/examples.md)
- 🧪 [Testing Guide](docs/testing.md)


## Testing

```bash
composer test
```

Runs all Codeception suites (unit, functional, acceptance) with code coverage.

## Static analysis

```bash
composer static
```

## Package information

[![PHP](https://img.shields.io/badge/%3E%3D8.2-777BB4.svg?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/releases/8.2/en.php)
[![Latest Stable Version](https://img.shields.io/packagist/v/yii2-framework/app-inertia-vue.svg?style=for-the-badge&logo=packagist&logoColor=white&label=Stable)](https://packagist.org/packages/yii2-framework/app-inertia-vue)

## Quality code

[![Codecov](https://img.shields.io/codecov/c/github/yii2-framework/app-inertia-vue.svg?style=for-the-badge&logo=codecov&logoColor=white&label=Coverage)](https://codecov.io/github/yii2-framework/app-inertia-vue)
[![PHPStan Level Max](https://img.shields.io/badge/PHPStan-Level%20Max-4F5D95.svg?style=for-the-badge&logo=github&logoColor=white)](https://github.com/yii2-framework/app-inertia-vue/actions/workflows/static.yml)
[![StyleCI](https://img.shields.io/badge/StyleCI-Passed-44CC11.svg?style=for-the-badge&logo=github&logoColor=white)](https://github.styleci.io/repos/698621511?branch=main)

## Our social networks

[![Follow on X](https://img.shields.io/badge/-Follow%20on%20X-1DA1F2.svg?style=for-the-badge&logo=x&logoColor=white&labelColor=000000)](https://x.com/Terabytesoftw)

## License

[![License](https://img.shields.io/badge/License-BSD--3--Clause-brightgreen.svg?style=for-the-badge&logo=opensourceinitiative&logoColor=white&labelColor=555555)](LICENSE)
