<?php

declare(strict_types=1);

namespace app\tests\unit;

use app\controllers\{SiteController, UserController};
use app\tests\support\Fixtures\UserFixture;
use Yii;
use yii\web\Response;

/**
 * Unit tests for {@see \app\controllers\UserController} login action and {@see \app\controllers\SiteController} about
 * action via Inertia responses.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 0.1
 */
final class LoginTest extends \Codeception\Test\Unit
{
    /**
     * @return array{user: array{class: string, dataFile: string}}
     */
    public function _fixtures(): array
    {
        return [
            'user' => [
                'class' => UserFixture::class,
                // @phpstan-ignore binaryOp.invalid
                'dataFile' => codecept_data_dir() . 'user.php',
            ],
        ];
    }

    public function testActionAboutReturnsInertiaResponse(): void
    {
        $_SERVER['REQUEST_URI'] = '/site/about';
        $_SERVER['SERVER_NAME'] = 'localhost';

        $controller = new SiteController(
            'site',
            Yii::$app,
            Yii::$app->mailer,
        );

        Yii::$app->controller = $controller;

        $response = $controller->actionAbout();

        self::assertInstanceOf(
            Response::class,
            $response,
            'Failed asserting that about action returns a Response instance.',
        );
    }

    public function testLoginPageReturnsInertiaResponseForGuest(): void
    {
        $_SERVER['REQUEST_URI'] = '/user/login';
        $_SERVER['SERVER_NAME'] = 'localhost';

        $controller = new UserController(
            'user',
            Yii::$app,
            Yii::$app->mailer,
        );

        Yii::$app->controller = $controller;
        Yii::$app->user->logout();

        $response = $controller->actionLogin();

        self::assertInstanceOf(
            Response::class,
            $response,
            'Failed asserting that login action returns a Response instance for guests.',
        );
    }
}
