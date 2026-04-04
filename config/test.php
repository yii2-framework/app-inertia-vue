<?php

declare(strict_types=1);

use app\models\User;
use app\tests\support\MailerBootstrap;
use yii\inertia\Manager;
use yii\inertia\vue\Bootstrap;
use yii\inertia\vue\Vite;
use yii\rbac\PhpManager;
use yii\symfonymailer\Mailer;
use yii\symfonymailer\Message;
use yii\web\JsonParser;

/** @phpstan-var array<string, mixed> $params */
$params = require __DIR__ . '/params.php';

/**
 * Application configuration shared by all test types
 */
return [
    'id' => 'app-inertia-vue-tests',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'app\\controllers',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => dirname(__DIR__) . '/node_modules',
    ],
    'bootstrap' => [
        Bootstrap::class,
        MailerBootstrap::class,
    ],
    'language' => 'en-US',
    'components' => [
        'authManager' => [
            'class' => PhpManager::class,
        ],
        'assetManager' => [
            'basePath' => __DIR__ . '/../public/assets',
        ],
        'db' => require __DIR__ . '/test_db.php',
        'inertia' => [
            'class' => Manager::class,
            'rootView' => '@app/resources/views/app.php',
            'shared' => [
                'auth' => static function (): array {
                    $user = Yii::$app->user;
                    $identity = $user->identity;

                    return [
                        'user' => $identity instanceof User ? [
                            'id' => $identity->id,
                            'username' => $identity->username,
                            'email' => $identity->email,
                        ] : null,
                        'isGuest' => $user->isGuest,
                        'canViewUsers' => !$user->isGuest && $user->can('viewUsers'),
                    ];
                },
                'csrf' => static fn(): array => [
                    'param' => Yii::$app->request->csrfParam,
                    'token' => Yii::$app->request->getCsrfToken(),
                ],
                'appName' => static fn(): string => Yii::$app->name,
                'turnstileSiteKey' => static function (): string {
                    /** @phpstan-var string $key */
                    $key = Yii::$app->params['turnstile.siteKey'] ?? '';

                    return $key;
                },
            ],
        ],
        'inertiaVue' => [
            'class' => Vite::class,
            'manifestPath' => '@webroot/build/.vite/manifest.json',
            'baseUrl' => '@web/build',
            'entrypoints' => ['resources/js/app.js'],
            'devMode' => true,
            'devServerUrl' => 'http://localhost:5173',
        ],
        'mailer' => [
            'class' => Mailer::class,
            'messageClass' => Message::class,
            'useFileTransport' => true,
            'viewPath' => '@app/resources/mail',
        ],
        'request' => [
            'cookieValidationKey' => 'test',
            'enableCsrfValidation' => false,
            'parsers' => [
                'application/json' => JsonParser::class,
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ],
        'user' => [
            'identityClass' => User::class,
            'loginUrl' => [
                'user/login',
            ],
        ],
    ],
    'params' => [...$params, 'turnstile.secretKey' => ''],
];
