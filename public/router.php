<?php

/**
 * Router script for PHP built-in server with pretty URLs.
 *
 * Usage: php -S localhost:8080 -t public public/router.php
 */

$path = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$decodedPath = rawurldecode($path);
$publicDir = realpath(__DIR__);
$candidate = realpath(__DIR__ . $decodedPath);

// Serve existing static files directly (CSS, JS, images, etc.)
if (
    $decodedPath !== '/'
    && $publicDir !== false
    && $candidate !== false
    && strncmp($candidate, $publicDir . DIRECTORY_SEPARATOR, strlen($publicDir . DIRECTORY_SEPARATOR)) === 0
    && is_file($candidate)
) {
    return false;
}

// Route everything else through index-test.php for test environment
require __DIR__ . '/index-test.php';
