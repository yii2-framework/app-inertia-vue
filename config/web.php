<?php

declare(strict_types=1);

use app\models\User;
use yii\caching\FileCache;
use yii\inertia\Manager;
use yii\inertia\vue\Bootstrap;
use yii\inertia\vue\Vite;
use yii\log\FileTarget;
use yii\mail\MailerInterface;
use yii\rbac\PhpManager;
use yii\symfonymailer\Mailer;

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'app-inertia-vue',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => dirname(__DIR__) . '/node_modules',
    ],
    'basePath' => dirname(__DIR__),
    'bootstrap' => [
        'log',
        Bootstrap::class,
    ],
    'components' => [
        'authManager' => [
            'class' => PhpManager::class,
        ],
        'cache' => [
            'class' => FileCache::class,
        ],
        'db' => $db,
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'inertia' => [
            'class' => Manager::class,
            'rootView' => '@app/resources/views/app.php',
            'version' => static function (): string {
                $path = Yii::getAlias('@webroot/build/.vite/manifest.json');

                return is_file($path) ? (string) filemtime($path) : '';
            },
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
            'devMode' => YII_ENV === 'dev',
            'devServerUrl' => 'http://localhost:5173',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => FileTarget::class,
                    'levels' => [
                        'error',
                        'warning',
                    ],
                ],
            ],
        ],
        'mailer' => MailerInterface::class,
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '',
            'parsers' => [
                'application/json' => \yii\web\JsonParser::class,
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ],
        'user' => [
            'identityClass' => User::class,
            'enableAutoLogin' => true,
            'loginUrl' => [
                'user/login',
            ],
        ],
    ],
    'container' => [
        'singletons' => [
            MailerInterface::class => [
                'class' => Mailer::class,
                // send all mails to a file by default.
                'useFileTransport' => true,
                'viewPath' => '@app/resources/mail',
            ],
        ],
    ],
    'controllerNamespace' => 'app\\controllers',
    'params' => $params,
];

return $config;
