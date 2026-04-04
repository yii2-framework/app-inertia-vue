<?php

declare(strict_types=1);

namespace app\tests\functional;

use app\models\User;
use app\tests\support\Fixtures\UserFixture;
use app\tests\support\FunctionalTester;
use PHPUnit\Framework\Assert;
use Yii;
use yii\helpers\Url;
use yii\mail\BaseMailer;
use yii\mail\MailEvent;

/**
 * Functional tests for {@see \app\controllers\UserController::actionRequestPasswordReset()} and
 * {@see \app\controllers\UserController::actionResetPassword()} via Inertia.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 0.1
 */
final class PasswordResetCest
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

    public function requestResetFailsWhenMailerErrors(FunctionalTester $I): void
    {
        // force mailer `send()` to fail via `EVENT_BEFORE_SEND`.
        $handler = static function (MailEvent $event): void {
            $event->isValid = false;
        };

        Yii::$app->mailer->on(BaseMailer::EVENT_BEFORE_SEND, $handler);

        try {
            $I->amOnPage(Url::toRoute('/user/request-password-reset'));
            $I->sendAjaxPostRequest(
                Url::toRoute('/user/request-password-reset'),
                ['PasswordResetRequestForm' => ['email' => 'okirlin@example.com']],
            );
            $I->seeResponseCodeIs(302);
        } finally {
            Yii::$app->mailer->off(BaseMailer::EVENT_BEFORE_SEND, $handler);
        }
    }

    public function requestResetSuccessfully(FunctionalTester $I): void
    {
        $I->amOnPage(Url::toRoute('/user/request-password-reset'));
        $I->sendAjaxPostRequest(
            Url::toRoute('/user/request-password-reset'),
            ['PasswordResetRequestForm' => ['email' => 'okirlin@example.com']],
        );
        $I->seeEmailIsSent();
        $I->seeResponseCodeIs(302);
    }

    public function requestResetWithEmptyEmail(FunctionalTester $I): void
    {
        $I->amOnPage(Url::toRoute('/user/request-password-reset'));
        $I->sendAjaxPostRequest(
            Url::toRoute('/user/request-password-reset'),
            ['PasswordResetRequestForm' => ['email' => '']],
        );
        $I->seeResponseCodeIs(302);
    }

    public function requestResetWithWrongEmail(FunctionalTester $I): void
    {
        $I->amOnPage(Url::toRoute('/user/request-password-reset'));
        $I->sendAjaxPostRequest(
            Url::toRoute('/user/request-password-reset'),
            ['PasswordResetRequestForm' => ['email' => 'nonexistent@example.com']],
        );
        $I->seeResponseCodeIs(302);
    }

    public function resetPasswordWithInvalidToken(FunctionalTester $I): void
    {
        $I->amOnPage(Url::toRoute(['/user/reset-password', 'token' => 'invalid_token_123']));
        $I->canSee('Wrong password reset token.');
    }

    public function resetPasswordWithValidToken(FunctionalTester $I): void
    {
        $user = User::findByUsername('okirlin');

        Assert::assertInstanceOf(
            User::class,
            $user,
            "Failed asserting that fixture user 'okirlin' exists.",
        );
        Assert::assertNotNull(
            $user->password_reset_token,
            "Failed asserting that fixture user 'okirlin' has a 'password reset token'.",
        );

        $token = $user->password_reset_token;

        $I->amOnPage(Url::toRoute(['/user/reset-password', 'token' => $token]));
        $I->seeResponseCodeIs(200);
        $I->sendAjaxPostRequest(
            Url::toRoute(['/user/reset-password', 'token' => $token]),
            ['ResetPasswordForm' => ['password' => 'newpassword123']],
        );
        $I->seeResponseCodeIs(302);

        $user->refresh();

        Assert::assertNull($user->password_reset_token, 'Password reset token should be cleared after reset.');
        Assert::assertTrue(
            Yii::$app->security->validatePassword('newpassword123', $user->password_hash),
            'Password should be updated to the new value.',
        );
    }
}
