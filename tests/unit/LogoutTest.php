<?php

declare(strict_types=1);

namespace app\tests\unit;

use app\controllers\UserController;
use app\models\User;
use app\tests\support\Fixtures\UserFixture;
use Yii;
use yii\web\{IdentityInterface, Response};

/**
 * Unit tests for {@see \app\controllers\UserController} logout action.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 0.1
 */
final class LogoutTest extends \Codeception\Test\Unit
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

    public function testLogoutRedirectsToHome(): void
    {
        $user = User::findIdentity(1);

        $controller = new UserController(
            'user',
            Yii::$app,
            Yii::$app->mailer,
        );

        Yii::$app->controller = $controller;

        self::assertNotNull(
            $user,
            "Failed asserting that the user identity with ID '1' exists.",
        );
        self::assertInstanceOf(
            IdentityInterface::class,
            $user,
            "Failed asserting that the identity is an instance of 'Identity' class.",
        );

        Yii::$app->user->login($user);

        self::assertFalse(
            Yii::$app->user->isGuest,
            'Failed asserting that user is logged in before logout.',
        );

        $response = $controller->actionLogout();

        self::assertInstanceOf(
            Response::class,
            $response,
            'Failed asserting that logout returns a Response instance.',
        );
        self::assertTrue(
            Yii::$app->user->isGuest,
            'Failed asserting that user is a guest after logout.',
        );
    }
}
