<?php

declare(strict_types=1);

namespace app\tests\unit;

use app\controllers\SiteController;
use app\tests\support\Fixtures\UserFixture;
use Yii;
use yii\web\HttpException;
use yii\web\Response;

/**
 * Unit tests for {@see SiteController} error and contact actions.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 0.1
 */
final class SiteControllerTest extends \Codeception\Test\Unit
{
    /**
     * @phpstan-return array{user: array{class: string, dataFile: string}}
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

    public function testActionErrorWithHttpException(): void
    {
        $_SERVER['REQUEST_URI'] = '/site/error';
        $_SERVER['SERVER_NAME'] = 'localhost';

        $controller = new SiteController('site', Yii::$app, Yii::$app->mailer);
        Yii::$app->controller = $controller;

        Yii::$app->errorHandler->exception = new HttpException(404, 'Page not found');

        $response = $controller->actionError();

        self::assertInstanceOf(Response::class, $response);
    }

    public function testActionErrorWithGenericException(): void
    {
        $_SERVER['REQUEST_URI'] = '/site/error';
        $_SERVER['SERVER_NAME'] = 'localhost';

        $controller = new SiteController('site', Yii::$app, Yii::$app->mailer);
        Yii::$app->controller = $controller;

        Yii::$app->errorHandler->exception = new \RuntimeException('Something went wrong');

        $response = $controller->actionError();

        self::assertInstanceOf(Response::class, $response);
    }

    public function testActionIndex(): void
    {
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['SERVER_NAME'] = 'localhost';

        $controller = new SiteController('site', Yii::$app, Yii::$app->mailer);
        Yii::$app->controller = $controller;

        $response = $controller->actionIndex();

        self::assertInstanceOf(Response::class, $response);
    }

    public function testActionContactGet(): void
    {
        $_SERVER['REQUEST_URI'] = '/site/contact';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $controller = new SiteController('site', Yii::$app, Yii::$app->mailer);
        Yii::$app->controller = $controller;

        $response = $controller->actionContact();

        self::assertInstanceOf(Response::class, $response);
    }
}
