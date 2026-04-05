# Configuration reference

## Environment variables

Defined in `public/index.php`:

- `YII_ENV`: Set via `getenv('YII_ENV')`, defaults to `prod`. Values: `dev`, `test`, `prod`.
- `YII_DEBUG`: Automatically `true` when `YII_ENV` is not `prod`.

## Application parameters

All parameters are in `config/params.php`.

### Admin credentials

Used by the migration to seed the initial admin user:

| Parameter        | Default             | Description    |
| ---------------- | ------------------- | -------------- |
| `admin.username` | `admin`             | Admin username |
| `admin.password` | `admin`             | Admin password |
| `admin.email`    | `admin@example.com` | Admin email    |

**Important:** Change these before running migrations in shared or production environments.

### Cloudflare Turnstile

| Parameter             | Default  | Description              |
| --------------------- | -------- | ------------------------ |
| `turnstile.siteKey`   | Test key | Frontend widget key      |
| `turnstile.secretKey` | Test key | Backend verification key |

The default values are [Cloudflare test keys](https://developers.cloudflare.com/turnstile/troubleshooting/testing/) that always pass validation. Replace them with real keys from the [Cloudflare dashboard](https://dash.cloudflare.com/) for production.

### Email

| Parameter      | Default               | Description                    |
| -------------- | --------------------- | ------------------------------ |
| `adminEmail`   | `admin@example.com`   | Recipient for contact form     |
| `senderEmail`  | `noreply@example.com` | From address for outgoing mail |
| `senderName`   | `Example.com mailer`  | From name for outgoing mail    |
| `supportEmail` | `support@example.com` | Support contact address        |

### User

| Parameter                       | Default | Description                             |
| ------------------------------- | ------- | --------------------------------------- |
| `user.passwordMinLength`        | `8`     | Minimum password length for signup      |
| `user.passwordResetTokenExpire` | `3600`  | Password reset token lifetime (seconds) |

## Database

Default: SQLite at `runtime/db.sqlite`. Configuration in `config/db.php`.

To use MySQL or PostgreSQL, update the DSN:

```php
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=yii2_app',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
];
```

## Mail

Default: file-based transport (emails saved to `runtime/mail/`). Configuration in `config/web.php`.

To enable SMTP delivery, set `useFileTransport` to `false` and configure your SMTP settings.

## Dark mode

Controlled by `resources/js/Components/ThemeToggle.vue`:

- Respects system preference via `prefers-color-scheme`.
- Persists user choice to `localStorage` as `theme` key.
- Applies the `dark` class on the `<html>` element.
- System preference changes propagate when no localStorage override exists.

## Next steps

- 💡 [Usage Examples](examples.md)
- 🧪 [Testing Guide](testing.md)
