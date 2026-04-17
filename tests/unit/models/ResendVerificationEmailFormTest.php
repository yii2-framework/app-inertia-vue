<?php

declare(strict_types=1);

namespace app\tests\unit\models;

use app\models\{ResendVerificationEmailForm, User};
use app\tests\support\Fixtures\UserFixture;
use app\tests\support\UnitTester;
use RuntimeException;
use Yii;
use yii\base\{Event, ModelEvent};
use yii\db\BaseActiveRecord;
use yii\mail\MessageInterface;

/**
 * Unit tests for {@see \app\models\ResendVerificationEmailForm} model.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 0.1
 */
final class ResendVerificationEmailFormTest extends \Codeception\Test\Unit
{
    protected UnitTester|null $tester = null;

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

    public function testEmptyEmailAddress(): void
    {
        $model = new ResendVerificationEmailForm();

        $model->attributes = ['email' => ''];

        verify($model->validate())
            ->false(
                'Failed asserting that validation fails for an empty email.',
            );
        verify($model->hasErrors())
            ->true(
                'Failed asserting that validation errors are present.',
            );
        verify($model->getFirstError('email'))
            ->equals(
                'Email cannot be blank.',
                'Failed asserting that the blank email error message is correct.',
            );
    }

    public function testExceptionDuringSaveRollsBackTransaction(): void
    {
        $fixtureUser = User::findOne(['username' => 'test.test']);

        self::assertInstanceOf(
            User::class,
            $fixtureUser,
            "Failed asserting that fixture user 'test.test' exists.",
        );

        $originalToken = $fixtureUser->verification_token;

        $handler = static function (): void {
            throw new RuntimeException('Forced exception during user save.');
        };

        Event::on(User::class, BaseActiveRecord::EVENT_BEFORE_UPDATE, $handler);

        try {
            $model = new ResendVerificationEmailForm();

            $model->attributes = ['email' => 'test.test@example.com'];

            $supportEmail = Yii::$app->params['supportEmail'];

            verify($model->sendEmail(Yii::$app->mailer, $supportEmail, Yii::$app->name))
                ->false(
                    "Failed asserting that sendEmail returns 'false' when the transaction block throws.",
                );
        } finally {
            Event::off(User::class, BaseActiveRecord::EVENT_BEFORE_UPDATE, $handler);
        }

        $user = User::findOne(['username' => 'test.test']);

        self::assertInstanceOf(
            User::class,
            $user,
            "Failed asserting that fixture user 'test.test' exists.",
        );
        self::assertSame(
            $originalToken,
            $user->verification_token,
            'Failed asserting that transaction rollback preserved the original verification token.',
        );
        self::assertSame(
            [],
            $this->tester?->grabSentEmails() ?? [],
            'Failed asserting that no email was dispatched after transaction rollback.',
        );
    }

    public function testInvalidEmailFormatFailsValidation(): void
    {
        $model = new ResendVerificationEmailForm();

        $model->attributes = ['email' => 'not-an-email'];

        verify($model->validate())
            ->false(
                'Failed asserting that validation fails for an invalid email format.',
            );
        verify($model->getFirstError('email'))
            ->equals(
                'Email is not a valid email address.',
                'Failed asserting that invalid email format surfaces the format error.',
            );
    }

    public function testRateLimitBlocksRapidResend(): void
    {
        $supportEmail = Yii::$app->params['supportEmail'];

        $first = new ResendVerificationEmailForm();

        $first->attributes = ['email' => 'test.test@example.com'];

        verify($first->sendEmail(Yii::$app->mailer, $supportEmail, Yii::$app->name))
            ->true(
                'Failed asserting that first sendEmail call succeeds before rate-limit engages.',
            );

        $second = new ResendVerificationEmailForm();

        $second->attributes = ['email' => 'test.test@example.com'];

        verify($second->sendEmail(Yii::$app->mailer, $supportEmail, Yii::$app->name))
            ->false(
                "Failed asserting that second sendEmail call returns 'false' while cooldown is active.",
            );

        $this->tester?->seeEmailIsSent(1);
    }

    public function testRateLimitDoesNotBlockLegitimateCaseAfterMismatchedCase(): void
    {
        $supportEmail = Yii::$app->params['supportEmail'];

        $mismatched = new ResendVerificationEmailForm();

        $mismatched->attributes = ['email' => 'TEST.TEST@EXAMPLE.COM'];

        verify($mismatched->sendEmail(Yii::$app->mailer, $supportEmail, Yii::$app->name))
            ->false(
                "Failed asserting that 'sendEmail' returns 'false' for a case-mismatched address (lookup misses).",
            );

        $legit = new ResendVerificationEmailForm();

        $legit->attributes = ['email' => 'test.test@example.com'];

        verify($legit->sendEmail(Yii::$app->mailer, $supportEmail, Yii::$app->name))
            ->true(
                'Failed asserting that a legitimate case-correct resend is not blocked by a prior case-mismatched request.',
            );
    }

    public function testRateLimitFailsOpenWhenCacheBackendWriteFails(): void
    {
        $failingCache = new class extends \yii\caching\ArrayCache {
            public function add($key, $value, $duration = 0, $dependency = null)
            {
                return false;
            }

            public function exists($key)
            {
                return false;
            }
        };

        $originalCache = Yii::$app->get('cache');

        Yii::$app->set('cache', $failingCache);

        try {
            $model = new ResendVerificationEmailForm();

            $model->attributes = ['email' => 'test.test@example.com'];

            $supportEmail = Yii::$app->params['supportEmail'];

            verify($model->sendEmail(Yii::$app->mailer, $supportEmail, Yii::$app->name))
                ->true(
                    "Failed asserting that 'sendEmail' fails open when the cache backend rejects writes.",
                );
        } finally {
            Yii::$app->set('cache', $originalCache);
        }
    }

    public function testResendToActiveUser(): void
    {
        $model = new ResendVerificationEmailForm();

        $model->attributes = ['email' => 'test2.test@example.com'];

        verify($model->validate())
            ->true(
                'Failed asserting that validation passes for an active user (enumeration-safe).',
            );

        $supportEmail = Yii::$app->params['supportEmail'];

        verify($model->sendEmail(Yii::$app->mailer, $supportEmail, Yii::$app->name))
            ->false(
                "Failed asserting that 'sendEmail' returns 'false' for an active user under the inactive-only filter.",
            );
    }

    public function testSendEmailReturnsFalseWhenSaveFails(): void
    {
        $handler = static function (ModelEvent $event): void {
            $event->isValid = false;
        };

        Event::on(User::class, BaseActiveRecord::EVENT_BEFORE_UPDATE, $handler);

        $model = new ResendVerificationEmailForm();

        $model->attributes = ['email' => 'test.test@example.com'];

        $supportEmail = Yii::$app->params['supportEmail'];

        try {
            verify($model->sendEmail(Yii::$app->mailer, $supportEmail, Yii::$app->name))
                ->false(
                    "Failed asserting that sendEmail returns 'false' when user save fails.",
                );
        } finally {
            Event::off(User::class, BaseActiveRecord::EVENT_BEFORE_UPDATE, $handler);
        }
    }

    public function testSendEmailToNonExistingInactiveUser(): void
    {
        $model = new ResendVerificationEmailForm();

        $model->email = 'nonexistent@example.com';

        $supportEmail = Yii::$app->params['supportEmail'];

        verify($model->sendEmail(Yii::$app->mailer, $supportEmail, Yii::$app->name))
            ->false(
                "Failed asserting that sendEmail returns 'false' when inactive user is not found.",
            );
    }

    public function testStaleTokenDetectedBeforeSend(): void
    {
        $sentinelToken = 'concurrent_overwrite_sentinel_token_value';

        $handler = static function (Event $event) use ($sentinelToken): void {
            /** @var User $sender */
            $sender = $event->sender;

            User::updateAll(
                ['verification_token' => $sentinelToken],
                ['id' => $sender->id],
            );
        };

        Event::on(User::class, BaseActiveRecord::EVENT_AFTER_UPDATE, $handler);

        try {
            $model = new ResendVerificationEmailForm();

            $model->attributes = ['email' => 'test.test@example.com'];

            $supportEmail = Yii::$app->params['supportEmail'];

            verify($model->sendEmail(Yii::$app->mailer, $supportEmail, Yii::$app->name))
                ->false(
                    "Failed asserting that sendEmail returns 'false' when the token was overwritten before send.",
                );
        } finally {
            Event::off(User::class, BaseActiveRecord::EVENT_AFTER_UPDATE, $handler);
        }

        $user = User::findOne(['username' => 'test.test']);

        self::assertInstanceOf(
            User::class,
            $user,
            "Failed asserting that fixture user 'test.test' exists.",
        );
        self::assertSame(
            $sentinelToken,
            $user->verification_token,
            'Failed asserting that the concurrent overwrite was persisted in DB.',
        );
        self::assertSame(
            [],
            $this->tester?->grabSentEmails() ?? [],
            'Failed asserting that no email was dispatched when a stale token was detected.',
        );
    }

    public function testSuccessfullyResend(): void
    {
        $model = new ResendVerificationEmailForm();

        $model->attributes = ['email' => 'test.test@example.com'];

        verify($model->validate())
            ->true(
                'Failed asserting that validation passes for an inactive user email.',
            );
        verify($model->hasErrors())
            ->false(
                'Failed asserting that no validation errors are present.',
            );

        $supportEmail = Yii::$app->params['supportEmail'];

        verify($model->sendEmail(Yii::$app->mailer, $supportEmail, Yii::$app->name))
            ->true(
                'Failed asserting that verification email is resent successfully.',
            );

        $this->tester?->seeEmailIsSent();

        /** @var MessageInterface|null $mail */
        $mail = $this->tester?->grabLastSentEmail();

        verify($mail)
            ->instanceOf(
                MessageInterface::class,
                'Failed asserting that a verification email was captured.',
            );
        verify($mail?->getTo())
            ->arrayHasKey(
                'test.test@example.com',
                'Failed asserting that email is sent to the inactive user.',
            );
        verify($mail?->getFrom())
            ->arrayHasKey(
                $supportEmail,
                "Failed asserting that email is sent 'from' the support address.",
            );
        verify($mail?->getSubject())
            ->equals(
                'Account registration at ' . Yii::$app->name,
                "Failed asserting that email 'subject' matches the registration template.",
            );

        $user = User::findOne(['username' => 'test.test']);

        self::assertInstanceOf(
            User::class,
            $user,
            "Failed asserting that fixture user 'test.test' exists.",
        );
        self::assertNotNull(
            $user->verification_token,
            "Failed asserting that fixture user 'test.test' has a verification token.",
        );

        /** @var \yii\symfonymailer\Message $mail */
        verify($mail->getSymfonyEmail()->getTextBody())
            ->stringContainsString(
                $user->verification_token,
                "Failed asserting that email 'body' contains the verification 'token'.",
            );
    }

    public function testThrowRuntimeExceptionWhenMailerFailsDuringSendEmail(): void
    {
        $handler = static function (): void {
            throw new RuntimeException('Mailer transport failure');
        };

        Yii::$app->mailer->on(\yii\mail\BaseMailer::EVENT_BEFORE_SEND, $handler);

        $model = new ResendVerificationEmailForm();

        $model->attributes = ['email' => 'test.test@example.com'];

        $supportEmail = Yii::$app->params['supportEmail'];

        try {
            verify($model->sendEmail(Yii::$app->mailer, $supportEmail, Yii::$app->name))
                ->false(
                    "Failed asserting that sendEmail returns 'false' when mailer throws exception.",
                );
        } finally {
            Yii::$app->mailer->off(\yii\mail\BaseMailer::EVENT_BEFORE_SEND, $handler);
        }

        $user = User::findOne(['username' => 'test.test']);

        self::assertInstanceOf(
            User::class,
            $user,
            "Failed asserting that fixture user 'test.test' exists.",
        );
        self::assertNotNull(
            $user->verification_token,
            'Failed asserting that verification token is preserved after mailer exception.',
        );
    }

    public function testTokenPersistedWhenMailerSendReturnsFalse(): void
    {
        $fixtureUser = User::findOne(['username' => 'test.test']);

        self::assertInstanceOf(
            User::class,
            $fixtureUser,
            "Failed asserting that fixture user 'test.test' exists.",
        );

        $originalToken = $fixtureUser->verification_token;

        $handler = static function (\yii\mail\MailEvent $event): void {
            $event->isValid = false;
        };

        Yii::$app->mailer->on(\yii\mail\BaseMailer::EVENT_BEFORE_SEND, $handler);

        $model = new ResendVerificationEmailForm();

        $model->attributes = ['email' => 'test.test@example.com'];

        $supportEmail = Yii::$app->params['supportEmail'];

        try {
            verify($model->sendEmail(Yii::$app->mailer, $supportEmail, Yii::$app->name))
                ->false(
                    "Failed asserting that sendEmail returns 'false' when mailer send returns 'false'.",
                );
        } finally {
            Yii::$app->mailer->off(\yii\mail\BaseMailer::EVENT_BEFORE_SEND, $handler);
        }

        $user = User::findOne(['username' => 'test.test']);

        self::assertInstanceOf(
            User::class,
            $user,
            "Failed asserting that fixture user 'test.test' exists.",
        );
        self::assertNotNull(
            $user->verification_token,
            'Failed asserting that verification token is preserved when mailer send returns false.',
        );
        self::assertNotSame(
            $originalToken,
            $user->verification_token,
            'Failed asserting that verification token was regenerated and committed before mailer failure.',
        );
    }

    public function testUnknownEmailAddressValidatesAndSendEmailReturnsFalse(): void
    {
        $model = new ResendVerificationEmailForm();

        $model->attributes = ['email' => 'aaa@bbb.cc'];

        verify($model->validate())
            ->true(
                'Failed asserting that validation passes for an unknown email (enumeration-safe).',
            );

        $supportEmail = Yii::$app->params['supportEmail'];

        verify($model->sendEmail(Yii::$app->mailer, $supportEmail, Yii::$app->name))
            ->false(
                "Failed asserting that 'sendEmail' returns 'false' for an unknown email address.",
            );
    }

    protected function _before(): void
    {
        Yii::$app->cache->flush();
    }
}
