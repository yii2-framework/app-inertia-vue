<?php

declare(strict_types=1);

// note: make sure this file is not accessible when deployed to production.
$clientIp = $_SERVER['REMOTE_ADDR'] ?? '';

if (!in_array($clientIp, ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit('Forbidden');
}

defined('YII_DEBUG') || define('YII_DEBUG', true);
defined('YII_ENV') || define('YII_ENV', 'test');

require __DIR__ . '/../vendor/autoload.php';

$c3 = dirname(__DIR__) . '/c3.php';

if (file_exists($c3)) {
    require_once $c3;
}

$config = require __DIR__ . '/../config/test.php';

(new yii\web\Application($config))->run();
