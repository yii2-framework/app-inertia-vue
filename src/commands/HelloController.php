<?php

declare(strict_types=1);

namespace app\commands;

use yii\console\{Controller, ExitCode};

/**
 * Echoes the first argument that you have entered.
 *
 * Provided as an example for learning how to create console commands.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 0.1
 */
final class HelloController extends Controller
{
    /**
     * Command echoes what you have entered as the message.
     *
     * @param string $message Message to be echoed.
     *
     * @return int Exit code
     */
    public function actionIndex(string $message = 'hello world'): int
    {
        echo "{$message}\n";

        return ExitCode::OK;
    }
}
