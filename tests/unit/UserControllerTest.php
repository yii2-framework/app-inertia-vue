<?php

declare(strict_types=1);

namespace app\tests\unit;

use app\controllers\UserController;
use app\models\User;
use app\tests\support\Fixtures\UserFixture;
use Yii;
use yii\base\{Event, ModelEvent};
use yii\db\BaseActiveRecord;
use yii\mail\{BaseMailer, MailEvent};
use yii\web\{BadRequestHttpException, Response};

/**
 * Unit tests for {@see UserController} all actions.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 0.1
 */
final class UserControllerTest extends \Codeception\Test\Unit
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

    public function testActionIndexReturnsResponse(): void
    {
        $_SERVER['REQUEST_URI'] = '/user';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $controller = new UserController('user', Yii::$app, Yii::$app->mailer);

        Yii::$app->controller = $controller;
        $response = $controller->actionIndex();

        self::assertInstanceOf(
            Response::class,
            $response,
            "Expected 'actionIndex' to return an instance of Response.",
        );
    }

    public function testActionLoginGet(): void
    {
        $_SERVER['REQUEST_URI'] = '/user/login';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $controller = new UserController('user', Yii::$app, Yii::$app->mailer);

        Yii::$app->controller = $controller;
        $response = $controller->actionLogin();

        self::assertInstanceOf(
            Response::class,
            $response,
            "Expected 'actionLogin' to return an inertia Response for GET request.",
        );
    }

    public function testActionLoginPostSuccess(): void
    {
        $_SERVER['REQUEST_URI'] = '/user/login';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        Yii::$app->request->setBodyParams([
            'LoginForm' => [
                'username' => 'admin',
                'password' => 'password_0',
            ],
        ]);

        $controller = new UserController('user', Yii::$app, Yii::$app->mailer);

        Yii::$app->controller = $controller;
        $response = $controller->actionLogin();

        self::assertInstanceOf(
            Response::class,
            $response,
            "Expected 'actionLogin' to redirect home on successful login.",
        );
    }

    public function testActionLoginPostValidationErrors(): void
    {
        $_SERVER['REQUEST_URI'] = '/user/login';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        Yii::$app->request->setBodyParams([
            'LoginForm' => [
                'username' => '',
                'password' => '',
            ],
        ]);

        $controller = new UserController('user', Yii::$app, Yii::$app->mailer);

        Yii::$app->controller = $controller;
        $response = $controller->actionLogin();

        self::assertInstanceOf(
            Response::class,
            $response,
            "Expected 'actionLogin' to redirect with errors flash on validation failure.",
        );
    }

    public function testActionLogout(): void
    {
        $_SERVER['REQUEST_URI'] = '/user/logout';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $controller = new UserController('user', Yii::$app, Yii::$app->mailer);

        Yii::$app->controller = $controller;
        $response = $controller->actionLogout();

        self::assertInstanceOf(
            Response::class,
            $response,
            "Expected 'actionLogout' to redirect home.",
        );
    }

    public function testActionRequestPasswordResetGet(): void
    {
        $_SERVER['REQUEST_URI'] = '/user/request-password-reset';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $controller = new UserController('user', Yii::$app, Yii::$app->mailer);

        Yii::$app->controller = $controller;
        $response = $controller->actionRequestPasswordReset();

        self::assertInstanceOf(
            Response::class,
            $response,
            "Expected 'actionRequestPasswordReset' to return an inertia Response for GET request.",
        );
    }

    public function testActionRequestPasswordResetPostMailerFailure(): void
    {
        $_SERVER['REQUEST_URI'] = '/user/request-password-reset';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        Yii::$app->request->setBodyParams([
            'PasswordResetRequestForm' => ['email' => 'okirlin@example.com'],
        ]);

        $handler = static function (MailEvent $event): void {
            $event->isValid = false;
        };

        Yii::$app->mailer->on(BaseMailer::EVENT_BEFORE_SEND, $handler);

        try {
            $controller = new UserController('user', Yii::$app, Yii::$app->mailer);

            Yii::$app->controller = $controller;
            $response = $controller->actionRequestPasswordReset();
        } finally {
            Yii::$app->mailer->off(BaseMailer::EVENT_BEFORE_SEND, $handler);
        }

        self::assertInstanceOf(
            Response::class,
            $response,
            "Expected 'actionRequestPasswordReset' to redirect with error flash when mailer fails.",
        );
    }

    public function testActionRequestPasswordResetPostSuccess(): void
    {
        $_SERVER['REQUEST_URI'] = '/user/request-password-reset';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        Yii::$app->request->setBodyParams([
            'PasswordResetRequestForm' => ['email' => 'okirlin@example.com'],
        ]);

        $controller = new UserController('user', Yii::$app, Yii::$app->mailer);

        Yii::$app->controller = $controller;
        $response = $controller->actionRequestPasswordReset();

        self::assertInstanceOf(
            Response::class,
            $response,
            "Expected 'actionRequestPasswordReset' to redirect home on successful email send.",
        );
    }

    public function testActionRequestPasswordResetPostValidationErrors(): void
    {
        $_SERVER['REQUEST_URI'] = '/user/request-password-reset';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        Yii::$app->request->setBodyParams([
            'PasswordResetRequestForm' => ['email' => 'nonexistent@example.com'],
        ]);

        $controller = new UserController('user', Yii::$app, Yii::$app->mailer);

        Yii::$app->controller = $controller;
        $response = $controller->actionRequestPasswordReset();

        self::assertInstanceOf(
            Response::class,
            $response,
            "Expected 'actionRequestPasswordReset' to redirect with errors flash on validation failure.",
        );
    }

    public function testActionResendVerificationEmailGet(): void
    {
        $_SERVER['REQUEST_URI'] = '/user/resend-verification-email';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $controller = new UserController('user', Yii::$app, Yii::$app->mailer);

        Yii::$app->controller = $controller;
        $response = $controller->actionResendVerificationEmail();

        self::assertInstanceOf(
            Response::class,
            $response,
            "Expected 'actionResendVerificationEmail' to return an inertia Response for GET request.",
        );
    }

    public function testActionResendVerificationEmailPostMailerFailure(): void
    {
        $_SERVER['REQUEST_URI'] = '/user/resend-verification-email';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        Yii::$app->request->setBodyParams([
            'ResendVerificationEmailForm' => ['email' => 'test.test@example.com'],
        ]);

        $handler = static function (MailEvent $event): void {
            $event->isValid = false;
        };

        Yii::$app->mailer->on(BaseMailer::EVENT_BEFORE_SEND, $handler);

        try {
            $controller = new UserController('user', Yii::$app, Yii::$app->mailer);

            Yii::$app->controller = $controller;
            $response = $controller->actionResendVerificationEmail();
        } finally {
            Yii::$app->mailer->off(BaseMailer::EVENT_BEFORE_SEND, $handler);
        }

        self::assertInstanceOf(
            Response::class,
            $response,
            "Expected 'actionResendVerificationEmail' to redirect with error flash when mailer fails.",
        );
    }

    public function testActionResendVerificationEmailPostSuccess(): void
    {
        $_SERVER['REQUEST_URI'] = '/user/resend-verification-email';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        Yii::$app->request->setBodyParams([
            'ResendVerificationEmailForm' => ['email' => 'test.test@example.com'],
        ]);

        $controller = new UserController('user', Yii::$app, Yii::$app->mailer);

        Yii::$app->controller = $controller;
        $response = $controller->actionResendVerificationEmail();

        self::assertInstanceOf(
            Response::class,
            $response,
            "Expected 'actionResendVerificationEmail' to redirect home on successful email send.",
        );
    }

    public function testActionResendVerificationEmailPostValidationErrors(): void
    {
        $_SERVER['REQUEST_URI'] = '/user/resend-verification-email';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        // admin@example.com has STATUS_ACTIVE, not STATUS_INACTIVE — 'exist' validator fails.
        Yii::$app->request->setBodyParams([
            'ResendVerificationEmailForm' => ['email' => 'admin@example.com'],
        ]);

        $controller = new UserController('user', Yii::$app, Yii::$app->mailer);

        Yii::$app->controller = $controller;
        $response = $controller->actionResendVerificationEmail();

        self::assertInstanceOf(
            Response::class,
            $response,
            "Expected 'actionResendVerificationEmail' to redirect with errors flash on validation failure.",
        );
    }

    public function testActionResetPasswordGet(): void
    {
        $_SERVER['REQUEST_URI'] = '/user/reset-password';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $user = User::findByUsername('okirlin');

        self::assertInstanceOf(User::class, $user, "Fixture user 'okirlin' must exist.");

        $token = $user->password_reset_token;

        self::assertNotNull($token, "Fixture user 'okirlin' must have a password reset token.");

        $controller = new UserController('user', Yii::$app, Yii::$app->mailer);

        Yii::$app->controller = $controller;
        $response = $controller->actionResetPassword($token);

        self::assertInstanceOf(
            Response::class,
            $response,
            "Expected 'actionResetPassword' to return an inertia Response for GET request with valid token.",
        );
    }

    public function testActionResetPasswordPostSaveFailure(): void
    {
        $_SERVER['REQUEST_URI'] = '/user/reset-password';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $user = User::findByUsername('okirlin');

        self::assertInstanceOf(User::class, $user, "Fixture user 'okirlin' must exist.");

        $token = $user->password_reset_token;

        self::assertNotNull($token, "Fixture user 'okirlin' must have a password reset token.");

        Yii::$app->request->setBodyParams([
            'ResetPasswordForm' => ['password' => 'newpassword123'],
        ]);

        $handler = static function (ModelEvent $event): void {
            $event->isValid = false;
        };

        Event::on(User::class, BaseActiveRecord::EVENT_BEFORE_UPDATE, $handler);

        try {
            $controller = new UserController('user', Yii::$app, Yii::$app->mailer);

            Yii::$app->controller = $controller;
            $response = $controller->actionResetPassword($token);
        } finally {
            Event::off(User::class, BaseActiveRecord::EVENT_BEFORE_UPDATE, $handler);
        }

        self::assertInstanceOf(
            Response::class,
            $response,
            "Expected 'actionResetPassword' to redirect with error flash when user save fails.",
        );
    }

    public function testActionResetPasswordPostSuccess(): void
    {
        $_SERVER['REQUEST_URI'] = '/user/reset-password';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $user = User::findByUsername('okirlin');

        self::assertInstanceOf(User::class, $user, "Fixture user 'okirlin' must exist.");

        $token = $user->password_reset_token;

        self::assertNotNull($token, "Fixture user 'okirlin' must have a password reset token.");

        Yii::$app->request->setBodyParams([
            'ResetPasswordForm' => ['password' => 'newpassword123'],
        ]);

        $controller = new UserController('user', Yii::$app, Yii::$app->mailer);

        Yii::$app->controller = $controller;
        $response = $controller->actionResetPassword($token);

        self::assertInstanceOf(
            Response::class,
            $response,
            "Expected 'actionResetPassword' to redirect home on successful password reset.",
        );
    }

    public function testActionResetPasswordPostThrowsDuringSave(): void
    {
        $_SERVER['REQUEST_URI'] = '/user/reset-password';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $user = User::findByUsername('okirlin');

        self::assertInstanceOf(User::class, $user, "Fixture user 'okirlin' must exist.");

        $token = $user->password_reset_token;

        self::assertNotNull($token, "Fixture user 'okirlin' must have a password reset token.");

        Yii::$app->request->setBodyParams([
            'ResetPasswordForm' => ['password' => 'newpassword123'],
        ]);

        $handler = static function (): void {
            throw new \RuntimeException('Simulated DB failure during password save.');
        };

        Event::on(User::class, BaseActiveRecord::EVENT_BEFORE_UPDATE, $handler);

        try {
            $controller = new UserController('user', Yii::$app, Yii::$app->mailer);

            Yii::$app->controller = $controller;
            $response = $controller->actionResetPassword($token);
        } finally {
            Event::off(User::class, BaseActiveRecord::EVENT_BEFORE_UPDATE, $handler);
        }

        self::assertInstanceOf(
            Response::class,
            $response,
            "Expected 'actionResetPassword' to return Response when save throws instead of propagating exception.",
        );
    }

    public function testActionResetPasswordPostValidationErrors(): void
    {
        $_SERVER['REQUEST_URI'] = '/user/reset-password';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $user = User::findByUsername('okirlin');

        self::assertInstanceOf(User::class, $user, "Fixture user 'okirlin' must exist.");

        $token = $user->password_reset_token;

        self::assertNotNull($token, "Fixture user 'okirlin' must have a password reset token.");

        // 'short' is 5 chars, below passwordMinLength of 8.
        Yii::$app->request->setBodyParams([
            'ResetPasswordForm' => ['password' => 'short'],
        ]);

        $controller = new UserController('user', Yii::$app, Yii::$app->mailer);

        Yii::$app->controller = $controller;
        $response = $controller->actionResetPassword($token);

        self::assertInstanceOf(
            Response::class,
            $response,
            "Expected 'actionResetPassword' to redirect with errors flash on validation failure.",
        );
    }

    public function testActionResetPasswordThrowsOnInvalidToken(): void
    {
        $_SERVER['REQUEST_URI'] = '/user/reset-password';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $controller = new UserController('user', Yii::$app, Yii::$app->mailer);

        Yii::$app->controller = $controller;

        $this->expectException(BadRequestHttpException::class);

        $controller->actionResetPassword('invalid-token');
    }

    public function testActionSignupGet(): void
    {
        $_SERVER['REQUEST_URI'] = '/user/signup';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $controller = new UserController('user', Yii::$app, Yii::$app->mailer);

        Yii::$app->controller = $controller;
        $response = $controller->actionSignup();

        self::assertInstanceOf(
            Response::class,
            $response,
            "Expected 'actionSignup' to return an inertia Response for GET request.",
        );
    }

    public function testActionSignupPostMailerFailure(): void
    {
        $_SERVER['REQUEST_URI'] = '/user/signup';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        Yii::$app->request->setBodyParams([
            'SignupForm' => [
                'username' => 'unit_mailer_fail_user',
                'email' => 'unit.mailer.fail@example.com',
                'password' => 'password123',
            ],
        ]);

        $handler = static function (MailEvent $event): void {
            $event->isValid = false;
        };

        Yii::$app->mailer->on(BaseMailer::EVENT_BEFORE_SEND, $handler);

        try {
            $controller = new UserController('user', Yii::$app, Yii::$app->mailer);

            Yii::$app->controller = $controller;
            $response = $controller->actionSignup();
        } finally {
            Yii::$app->mailer->off(BaseMailer::EVENT_BEFORE_SEND, $handler);
        }

        self::assertInstanceOf(
            Response::class,
            $response,
            "Expected 'actionSignup' to redirect with error flash when mailer fails.",
        );
    }

    public function testActionSignupPostSuccess(): void
    {
        $_SERVER['REQUEST_URI'] = '/user/signup';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        Yii::$app->request->setBodyParams([
            'SignupForm' => [
                'username' => 'unit_test_user',
                'email' => 'unit.test.user@example.com',
                'password' => 'password123',
            ],
        ]);

        $controller = new UserController('user', Yii::$app, Yii::$app->mailer);

        Yii::$app->controller = $controller;
        $response = $controller->actionSignup();

        self::assertInstanceOf(
            Response::class,
            $response,
            "Expected 'actionSignup' to redirect home on successful signup.",
        );
    }

    public function testActionSignupPostValidationErrors(): void
    {
        $_SERVER['REQUEST_URI'] = '/user/signup';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        Yii::$app->request->setBodyParams([
            'SignupForm' => [
                'username' => '',
                'email' => '',
                'password' => '',
            ],
        ]);

        $controller = new UserController('user', Yii::$app, Yii::$app->mailer);

        Yii::$app->controller = $controller;
        $response = $controller->actionSignup();

        self::assertInstanceOf(
            Response::class,
            $response,
            "Expected 'actionSignup' to redirect with errors flash on validation failure.",
        );
    }

    public function testActionVerifyEmailFailure(): void
    {
        $_SERVER['REQUEST_URI'] = '/user/verify-email';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $user = User::findOne(['username' => 'test.fail', 'status' => User::STATUS_INACTIVE]);

        self::assertInstanceOf(User::class, $user, "Fixture user 'test.fail' must exist.");

        $token = $user->verification_token;

        self::assertNotNull($token, "Fixture user 'test.fail' must have a verification token.");

        $handler = static function (ModelEvent $event): void {
            $event->isValid = false;
        };

        Event::on(User::class, BaseActiveRecord::EVENT_BEFORE_UPDATE, $handler);

        try {
            $controller = new UserController('user', Yii::$app, Yii::$app->mailer);

            Yii::$app->controller = $controller;
            $response = $controller->actionVerifyEmail($token);
        } finally {
            Event::off(User::class, BaseActiveRecord::EVENT_BEFORE_UPDATE, $handler);
        }

        self::assertInstanceOf(
            Response::class,
            $response,
            "Expected 'actionVerifyEmail' to redirect home with error flash when verification fails.",
        );
    }

    public function testActionVerifyEmailSuccess(): void
    {
        $_SERVER['REQUEST_URI'] = '/user/verify-email';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $user = User::findOne(['username' => 'test.test', 'status' => User::STATUS_INACTIVE]);

        self::assertInstanceOf(User::class, $user, "Fixture user 'test.test' must exist.");

        $token = $user->verification_token;

        self::assertNotNull($token, "Fixture user 'test.test' must have a verification token.");

        $controller = new UserController('user', Yii::$app, Yii::$app->mailer);

        Yii::$app->controller = $controller;
        $response = $controller->actionVerifyEmail($token);

        self::assertInstanceOf(
            Response::class,
            $response,
            "Expected 'actionVerifyEmail' to redirect home on successful email verification.",
        );
    }

    public function testActionVerifyEmailThrowsOnInvalidToken(): void
    {
        $_SERVER['REQUEST_URI'] = '/user/verify-email';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $controller = new UserController('user', Yii::$app, Yii::$app->mailer);

        Yii::$app->controller = $controller;

        $this->expectException(BadRequestHttpException::class);

        $controller->actionVerifyEmail('invalid-token');
    }
}
