<?php

declare(strict_types=1);

namespace app\tests\unit;

use app\controllers\SiteController;
use app\tests\support\Fixtures\UserFixture;
use Yii;
use yii\mail\{BaseMailer, MailEvent};
use yii\web\{HttpException, Response};

/**
 * Unit tests for {@see SiteController} all actions.
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

    public function testActionAbout(): void
    {
        $_SERVER['REQUEST_URI'] = '/site/about';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $controller = new SiteController('site', Yii::$app, Yii::$app->mailer);

        Yii::$app->controller = $controller;
        $response = $controller->actionAbout();

        self::assertInstanceOf(
            Response::class,
            $response,
            "Expected 'actionAbout' to return an instance of Response.",
        );
    }

    public function testActionContactGet(): void
    {
        $_SERVER['REQUEST_URI'] = '/site/contact';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $controller = new SiteController('site', Yii::$app, Yii::$app->mailer);

        Yii::$app->controller = $controller;
        $response = $controller->actionContact();

        self::assertInstanceOf(
            Response::class,
            $response,
            "Expected 'actionContact' to return an instance of Response for 'GET' request.",
        );
    }

    public function testActionContactPostMailerFailure(): void
    {
        $_SERVER['REQUEST_URI'] = '/site/contact';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        Yii::$app->request->setBodyParams([
            'ContactForm' => [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '(555) 123-4567',
                'subject' => 'Test Subject',
                'body' => 'Test body content.',
                'turnstileToken' => 'test-token',
            ],
        ]);

        $handler = static function (MailEvent $event): void {
            $event->isValid = false;
        };

        Yii::$app->mailer->on(BaseMailer::EVENT_BEFORE_SEND, $handler);

        try {
            $controller = new SiteController('site', Yii::$app, Yii::$app->mailer);

            Yii::$app->controller = $controller;
            $response = $controller->actionContact();
        } finally {
            Yii::$app->mailer->off(BaseMailer::EVENT_BEFORE_SEND, $handler);
        }

        self::assertInstanceOf(
            Response::class,
            $response,
            "Expected 'actionContact' to redirect with error flash when mailer fails.",
        );
    }

    public function testActionContactPostMailerThrows(): void
    {
        $_SERVER['REQUEST_URI'] = '/site/contact';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        Yii::$app->request->setBodyParams([
            'ContactForm' => [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '(555) 123-4567',
                'subject' => 'Test Subject',
                'body' => 'Test body content.',
                'turnstileToken' => 'test-token',
            ],
        ]);

        $handler = static function (): void {
            throw new \RuntimeException('Simulated mailer transport exception.');
        };

        Yii::$app->mailer->on(BaseMailer::EVENT_BEFORE_SEND, $handler);

        try {
            $controller = new SiteController('site', Yii::$app, Yii::$app->mailer);

            Yii::$app->controller = $controller;
            $response = $controller->actionContact();
        } finally {
            Yii::$app->mailer->off(BaseMailer::EVENT_BEFORE_SEND, $handler);
        }

        self::assertInstanceOf(
            Response::class,
            $response,
            "Expected 'actionContact' to return Response when mailer throws instead of propagating exception.",
        );
    }

    public function testActionContactPostSuccess(): void
    {
        $_SERVER['REQUEST_URI'] = '/site/contact';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        Yii::$app->request->setBodyParams([
            'ContactForm' => [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '(555) 123-4567',
                'subject' => 'Test Subject',
                'body' => 'Test body content.',
                'turnstileToken' => 'test-token',
            ],
        ]);

        $controller = new SiteController('site', Yii::$app, Yii::$app->mailer);

        Yii::$app->controller = $controller;
        $response = $controller->actionContact();

        self::assertInstanceOf(
            Response::class,
            $response,
            "Expected 'actionContact' to redirect with success flash on successful email send.",
        );
    }

    public function testActionContactPostValidationErrors(): void
    {
        $_SERVER['REQUEST_URI'] = '/site/contact';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        Yii::$app->request->setBodyParams([
            'ContactForm' => [
                'name' => '',
                'email' => '',
                'phone' => '',
                'subject' => '',
                'body' => '',
                'turnstileToken' => '',
            ],
        ]);

        $controller = new SiteController('site', Yii::$app, Yii::$app->mailer);

        Yii::$app->controller = $controller;
        $response = $controller->actionContact();

        self::assertInstanceOf(
            Response::class,
            $response,
            "Expected 'actionContact' to redirect with errors flash on validation failure.",
        );
    }

    public function testActionErrorWithGenericException(): void
    {
        $_SERVER['REQUEST_URI'] = '/site/error';
        $_SERVER['SERVER_NAME'] = 'localhost';

        $controller = new SiteController('site', Yii::$app, Yii::$app->mailer);

        Yii::$app->controller = $controller;
        Yii::$app->errorHandler->exception = new \RuntimeException('Something went wrong');
        $response = $controller->actionError();

        self::assertInstanceOf(
            Response::class,
            $response,
            "Expected 'actionError' to return an instance of Response for generic exception.",
        );
    }

    public function testActionErrorWithHttpException(): void
    {
        $_SERVER['REQUEST_URI'] = '/site/error';
        $_SERVER['SERVER_NAME'] = 'localhost';

        $controller = new SiteController('site', Yii::$app, Yii::$app->mailer);

        Yii::$app->controller = $controller;
        Yii::$app->errorHandler->exception = new HttpException(404, 'Page not found');
        $response = $controller->actionError();

        self::assertInstanceOf(
            Response::class,
            $response,
            "Expected 'actionError' to return an instance of Response for HTTP exception.",
        );
    }

    public function testActionErrorWithNullException(): void
    {
        $_SERVER['REQUEST_URI'] = '/site/error';
        $_SERVER['SERVER_NAME'] = 'localhost';

        $controller = new SiteController('site', Yii::$app, Yii::$app->mailer);

        Yii::$app->controller = $controller;
        Yii::$app->errorHandler->exception = null;
        $response = $controller->actionError();

        self::assertInstanceOf(
            Response::class,
            $response,
            "Expected 'actionError' to return a generic error Response when exception is null.",
        );
    }

    public function testActionIndex(): void
    {
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['SERVER_NAME'] = 'localhost';

        $controller = new SiteController('site', Yii::$app, Yii::$app->mailer);

        Yii::$app->controller = $controller;
        $response = $controller->actionIndex();

        self::assertInstanceOf(
            Response::class,
            $response,
            "Expected 'actionIndex' to return an instance of Response.",
        );
    }
}
