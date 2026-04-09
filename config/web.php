<?php

declare(strict_types=1);

use app\models\User;
use yii\caching\FileCache;
use yii\inertia\{Manager, Vite};
use yii\inertia\vue\Bootstrap;
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
            'shared' => [
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
                'appName' => static fn(): string => Yii::$app->name,
                'turnstileSiteKey' => static function (): string {
                    return Yii::$app->params['turnstile.siteKey'];
                },
            ],
            'version' => static function (): string {
                $path = Yii::getAlias('@webroot/build/.vite/manifest.json');

                return is_file($path) ? (string) filemtime($path) : '';
            },
        ],
        'inertiaVue' => [
            'class' => Vite::class,
            'baseUrl' => '@web/build',
            'devMode' => YII_ENV === 'dev',
            'devServerUrl' => 'http://localhost:5173',
            'entrypoints' => [
                'resources/js/app.js',
            ],
            'manifestPath' => '@webroot/build/.vite/manifest.json',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => FileTarget::class,
                    'levels' => [
                        'error',
                        'warning',
                    ],
                ],
            ],
            'traceLevel' => YII_DEBUG ? 3 : 0,
        ],
        'mailer' => MailerInterface::class,
        'request' => [
            'class' => \yii\inertia\web\Request::class,
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
