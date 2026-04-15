# Changelog

All notable changes to this project will be documented in this file.

The format is based on Keep a Changelog, and this project adheres to Semantic Versioning.

## 0.1.0 Under development

- feat: basic application template for Yii2 and Inertia Vue packages with Codeception, Docker, Tailwind CSS v4, Flowbite, and Cloudflare Turnstile.
- fix: roll back regenerated password reset token when email delivery fails, add generic error flash on mailer/save failures in controllers, and replace sort anchor tags with button elements for accessibility.
- feat: migrate to Inertia `v3` with progress indicator, view transitions, link prefetching, and remove unused `csrf` shared prop.
- fix: send verification email outside the DB transaction in `SignupForm::signup()` to avoid holding locks during mailer I/O; the user row is preserved if the mailer fails after commit.
- chore: bump `inertia` and `inertia-vue` and switch `config/web.php` to register the canonical `\yii\inertia\Vite` component via the Vue `Bootstrap`.
- chore: upgrade Vite to `v8` and `@vitejs/plugin-vue` to `v6` for compatibility.
- chore: add contact-submitted success screen with card layout and update screenshots.
- chore: add `.prettierrc.json`, sync linter configs with `app-inertia-react`, and apply Prettier formatting.
- chore: migrate package to `yii2-extensions` organization and raise minimum PHP requirement to `8.3`.
