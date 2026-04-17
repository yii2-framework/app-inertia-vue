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
 * Functional tests for {@see \app\controllers\UserController::actionResendVerificationEmail()} via Inertia.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 0.1
 */
final class ResendVerificationEmailCest
{
    public function _before(FunctionalTester $I): void
    {
        Yii::$app->cache->flush();
    }

    /**
     * @return array{user: array{class: string, dataFile: string}}
     */
    public function _fixtures(): array
    {
        return [
            'user' => [
                'class' => UserFixture::class,
                // @phpstan-ignore-next-line
                'dataFile' => codecept_data_dir() . 'user.php',
            ],
        ];
    }

    public function checkAlreadyVerifiedEmail(FunctionalTester $I): void
    {
        $I->amOnPage(Url::toRoute('/user/resend-verification-email'));
        $I->sendAjaxPostRequest(
            Url::toRoute('/user/resend-verification-email'),
            ['ResendVerificationEmailForm' => ['email' => 'test2.test@example.com']],
        );
        $I->seeResponseCodeIs(302);
    }

    public function checkEmptyField(FunctionalTester $I): void
    {
        $I->amOnPage(Url::toRoute('/user/resend-verification-email'));
        $I->sendAjaxPostRequest(
            Url::toRoute('/user/resend-verification-email'),
            ['ResendVerificationEmailForm' => ['email' => '']],
        );
        $I->seeResponseCodeIs(302);
    }

    public function checkPage(FunctionalTester $I): void
    {
        $I->amOnPage(Url::toRoute('/user/resend-verification-email'));
        $I->seeResponseCodeIs(200);
    }

    public function checkRateLimitBlocksRapidResend(FunctionalTester $I): void
    {
        $I->amOnPage(Url::toRoute('/user/resend-verification-email'));
        $I->sendAjaxPostRequest(
            Url::toRoute('/user/resend-verification-email'),
            ['ResendVerificationEmailForm' => ['email' => 'test.test@example.com']],
        );
        $I->seeResponseCodeIs(302);
        $I->seeEmailIsSent(1);

        $I->sendAjaxPostRequest(
            Url::toRoute('/user/resend-verification-email'),
            ['ResendVerificationEmailForm' => ['email' => 'test.test@example.com']],
        );
        $I->seeResponseCodeIs(302);
        $I->seeEmailIsSent(1);
    }

    public function checkResendWithExpiredTokenGeneratesNewToken(FunctionalTester $I): void
    {
        $user = User::findOne(['username' => 'test.test']);

        Assert::assertInstanceOf(
            User::class,
            $user,
            "Failed asserting that fixture user 'test.test' exists.",
        );

        // set an expired verification token.
        $user->verification_token = 'expiredtoken_1000000000';

        Assert::assertTrue(
            $user->save(false),
            "Failed asserting that the 'expired' verification 'token' was persisted.",
        );

        verify(User::isVerificationTokenValid($user->verification_token))
            ->false('Failed asserting that the token is expired before resend.');

        $I->amOnPage(Url::toRoute('/user/resend-verification-email'));
        $I->sendAjaxPostRequest(
            Url::toRoute('/user/resend-verification-email'),
            ['ResendVerificationEmailForm' => ['email' => 'test.test@example.com']],
        );
        $I->canSeeEmailIsSent();
        $I->seeResponseCodeIs(302);

        $user->refresh();

        verify(User::isVerificationTokenValid($user->verification_token))
            ->true('Failed asserting that a fresh verification token was generated after resend.');
        verify($user->verification_token)
            ->notEquals(
                'expiredtoken_1000000000',
                'Failed asserting that the expired token was replaced.',
            );
    }

    public function checkSendFailsWhenMailerErrors(FunctionalTester $I): void
    {
        $user = User::findOne(['username' => 'test.test']);

        Assert::assertInstanceOf(
            User::class,
            $user,
            "Failed asserting that fixture user 'test.test' exists.",
        );

        $originalToken = $user->verification_token;

        // force mailer `send()` to fail via `EVENT_BEFORE_SEND`.
        $handler = static function (MailEvent $event): void {
            $event->isValid = false;
        };

        Yii::$app->mailer->on(BaseMailer::EVENT_BEFORE_SEND, $handler);

        try {
            $I->amOnPage(Url::toRoute('/user/resend-verification-email'));
            $I->sendAjaxPostRequest(
                Url::toRoute('/user/resend-verification-email'),
                ['ResendVerificationEmailForm' => ['email' => 'test.test@example.com']],
            );
            $I->seeResponseCodeIs(302);

            $user->refresh();

            verify($user->verification_token)
                ->notEquals(
                    $originalToken,
                    'Failed asserting that the verification token was regenerated and committed before the mailer failed.',
                );
            verify($user->verification_token)
                ->notNull(
                    'Failed asserting that the verification token is preserved after mailer failure.',
                );
        } finally {
            Yii::$app->mailer->off(BaseMailer::EVENT_BEFORE_SEND, $handler);
        }
    }

    public function checkSendSuccessfully(FunctionalTester $I): void
    {
        $I->amOnPage(Url::toRoute('/user/resend-verification-email'));
        $I->sendAjaxPostRequest(
            Url::toRoute('/user/resend-verification-email'),
            ['ResendVerificationEmailForm' => ['email' => 'test.test@example.com']],
        );
        $I->canSeeEmailIsSent();
        $I->seeRecord(
            User::class,
            [
                'email' => 'test.test@example.com',
                'username' => 'test.test',
                'status' => User::STATUS_INACTIVE,
            ],
        );
        $I->seeResponseCodeIs(302);
    }

    public function checkWrongEmail(FunctionalTester $I): void
    {
        $I->amOnPage(Url::toRoute('/user/resend-verification-email'));
        $I->sendAjaxPostRequest(
            Url::toRoute('/user/resend-verification-email'),
            ['ResendVerificationEmailForm' => ['email' => 'wrong@email.com']],
        );
        $I->seeResponseCodeIs(302);
    }

    public function checkWrongEmailFormat(FunctionalTester $I): void
    {
        $I->amOnPage(Url::toRoute('/user/resend-verification-email'));
        $I->sendAjaxPostRequest(
            Url::toRoute('/user/resend-verification-email'),
            ['ResendVerificationEmailForm' => ['email' => 'abcd.com']],
        );
        $I->seeResponseCodeIs(302);
    }
}
