<?php

declare(strict_types=1);

defined('YII_DEBUG') || define('YII_DEBUG', true);
defined('YII_ENV') || define('YII_ENV', 'test');

require dirname(__DIR__) . '/vendor/autoload.php';

// Run migrations on the test database.
$app = new yii\console\Application(
    [
        'id' => 'app-inertia-vue-test-migrate',
        'basePath' => dirname(__DIR__),
        'aliases' => ['@app/migrations' => dirname(__DIR__) . '/src/migrations'],
        'components' => ['db' => require dirname(__DIR__) . '/config/test_db.php'],
        'params' => require dirname(__DIR__) . '/config/params.php',
        'controllerMap' => [
            'migrate' => [
                'class' => yii\console\controllers\MigrateController::class,
                'migrationNamespaces' => ['app\\migrations'],
                'migrationPath' => null,
                'interactive' => false,
                'compact' => true,
            ],
        ],
    ],
);

$app->runAction('migrate/up');

$app = null;

// @phpstan-ignore assign.propertyType
Yii::$app = null;
