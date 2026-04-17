<?php

declare(strict_types=1);

use app\models\User;
use app\tests\support\MailerBootstrap;
use yii\caching\FileCache;
use yii\inertia\{Manager, Vite};
use yii\inertia\vue\Bootstrap;
use yii\rbac\PhpManager;
use yii\symfonymailer\{Mailer, Message};
use yii\web\JsonParser;

/** @phpstan-var array<string, mixed> $params */
$params = require __DIR__ . '/params.php';

/**
 * Application configuration shared by all test types
 */
return [
    'id' => 'app-inertia-vue-tests',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => dirname(__DIR__) . '/node_modules',
    ],
    'basePath' => dirname(__DIR__),
    'bootstrap' => [
        Bootstrap::class,
        MailerBootstrap::class,
    ],
    'components' => [
        'assetManager' => [
            'basePath' => __DIR__ . '/../public/assets',
        ],
        'authManager' => [
            'class' => PhpManager::class,
        ],
        'cache' => [
            'class' => FileCache::class,
        ],
        'db' => require __DIR__ . '/test_db.php',
        'inertia' => [
            'class' => Manager::class,
            'rootView' => '@app/resources/views/app.php',
            'shared' => [
                'appName' => static fn(): string => Yii::$app->name,
                'auth' => static function (): array {
                    $user = Yii::$app->user;
                    $identity = $user->identity;

                    return [
                        'canViewUsers' => !$user->isGuest && $user->can('viewUsers'),
                        'isGuest' => $user->isGuest,
                        'user' => $identity instanceof User ? [
                            'id' => $identity->id,
                            'username' => $identity->username,
                            'email' => $identity->email,
                        ] : null,
                    ];
                },
                'turnstileSiteKey' => static function (): string {
                    return Yii::$app->params['turnstile.siteKey'];
                },
            ],
        ],
        'inertiaVue' => [
            'class' => Vite::class,
            'baseUrl' => '@web/build',
            'devMode' => true,
            'devServerUrl' => 'http://localhost:5173',
            'entrypoints' => [
                'resources/js/app.js',
            ],
            'manifestPath' => '@webroot/build/.vite/manifest.json',
        ],
        'mailer' => [
            'class' => Mailer::class,
            'messageClass' => Message::class,
            'useFileTransport' => true,
            'viewPath' => '@app/resources/mail',
        ],
        'request' => [
            'class' => \yii\inertia\web\Request::class,
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
    'controllerNamespace' => 'app\\controllers',
    'language' => 'en-US',
    'params' => [...$params, 'turnstile.secretKey' => ''],
];
