<?php

declare(strict_types=1);

namespace app\tests\unit\models;

use app\models\{PasswordResetRequestForm, User};
use app\tests\support\Fixtures\UserFixture;
use app\tests\support\UnitTester;
use Yii;
use yii\base\{Event, ModelEvent};
use yii\db\BaseActiveRecord;
use yii\mail\{BaseMailer, MailEvent, MessageInterface};
use yii\symfonymailer\Message;

/**
 * Unit tests for {@see \app\models\PasswordResetRequestForm} model.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 0.1
 */
final class PasswordResetRequestFormTest extends \Codeception\Test\Unit
{
    protected UnitTester|null $tester = null;

    /**
     * @phpstan-return array{user: array{class: string, dataFile: string}}
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

    public function testNotSendEmailsToInactiveUser(): void
    {
        $model = new PasswordResetRequestForm();

        $model->email = 'troy.becker@example.com';

        $supportEmail = Yii::$app->params['supportEmail'];

        verify($model->sendEmail(Yii::$app->mailer, $supportEmail, Yii::$app->name))
            ->false(
                "Failed asserting that sendEmail returns 'false' for an inactive user.",
            );
    }

    public function testReturnsFalseWhenMailerSendThrows(): void
    {
        $user = User::findByUsername('okirlin');

        self::assertInstanceOf(
            User::class,
            $user,
            "Failed asserting that fixture user 'okirlin' exists.",
        );

        // okirlin has a valid (non-expired) token — token-regeneration block is skipped ($transaction=null).
        $handler = static function (): void {
            throw new \RuntimeException('Simulated mailer transport failure.');
        };

        Yii::$app->mailer->on(BaseMailer::EVENT_BEFORE_SEND, $handler);

        $model = new PasswordResetRequestForm();

        $model->email = $user->email;

        $supportEmail = Yii::$app->params['supportEmail'];

        try {
            verify($model->sendEmail(Yii::$app->mailer, $supportEmail, Yii::$app->name))
                ->false(
                    "Failed asserting that sendEmail returns 'false' when mailer throws with no active transaction.",
                );
        } finally {
            Yii::$app->mailer->off(BaseMailer::EVENT_BEFORE_SEND, $handler);
        }
    }

    public function testReturnsFalseWhenMailerSendThrowsWithActiveTransaction(): void
    {
        $user = User::findByUsername('okirlin');

        self::assertInstanceOf(
            User::class,
            $user,
            "Failed asserting that fixture user 'okirlin' exists.",
        );

        // Set expired token so the token-regeneration block is entered and a transaction is started.
        // Save succeeds (no EVENT_BEFORE_UPDATE blocker), so $transaction is non-null and active
        // when the mailer throws — covering rollBack() inside the second catch block.
        $user->password_reset_token = 'expiredtoken_1000000000';

        self::assertTrue(
            $user->save(false),
            'Failed asserting that the expired token was persisted.',
        );

        $handler = static function (): void {
            throw new \RuntimeException('Simulated mailer failure with active transaction.');
        };

        Yii::$app->mailer->on(BaseMailer::EVENT_BEFORE_SEND, $handler);

        $model = new PasswordResetRequestForm();

        $model->email = $user->email;

        $supportEmail = Yii::$app->params['supportEmail'];

        try {
            verify($model->sendEmail(Yii::$app->mailer, $supportEmail, Yii::$app->name))
                ->false(
                    "Failed asserting that sendEmail returns 'false' when mailer throws with an active transaction.",
                );
        } finally {
            Yii::$app->mailer->off(BaseMailer::EVENT_BEFORE_SEND, $handler);
        }
    }

    public function testReturnsFalseWhenUserSaveThrowsDuringTokenRegeneration(): void
    {
        $user = User::findByUsername('okirlin');

        self::assertInstanceOf(
            User::class,
            $user,
            "Failed asserting that fixture user 'okirlin' exists.",
        );

        // Set expired token so the token-regeneration block (`$user->save()`) is entered.
        $user->password_reset_token = 'expiredtoken_1000000000';

        self::assertTrue(
            $user->save(false),
            'Failed asserting that the expired token was persisted.',
        );

        $handler = static function (): void {
            throw new \RuntimeException('Simulated DB failure during token regeneration.');
        };

        Event::on(User::class, BaseActiveRecord::EVENT_BEFORE_UPDATE, $handler);

        $model = new PasswordResetRequestForm();

        $model->email = $user->email;

        $supportEmail = Yii::$app->params['supportEmail'];

        try {
            verify($model->sendEmail(Yii::$app->mailer, $supportEmail, Yii::$app->name))
                ->false(
                    "Failed asserting that sendEmail returns 'false' when user save throws during token regeneration.",
                );
        } finally {
            Event::off(User::class, BaseActiveRecord::EVENT_BEFORE_UPDATE, $handler);
        }
    }

    public function testSendEmailRegeneratesExpiredToken(): void
    {
        $user = User::findByUsername('okirlin');

        self::assertInstanceOf(
            User::class,
            $user,
            "Failed asserting that fixture user 'okirlin' exists.",
        );

        // set an expired token (timestamp far in the past).
        $user->password_reset_token = 'expiredtoken_1000000000';

        self::assertTrue(
            $user->save(false),
            "Failed asserting that the 'expired token' was persisted.",
        );

        $model = new PasswordResetRequestForm();

        $model->email = $user->email;

        $supportEmail = Yii::$app->params['supportEmail'];

        verify($model->sendEmail(Yii::$app->mailer, $supportEmail, Yii::$app->name))
            ->notEmpty(
                'Failed asserting that email is sent after regenerating expired token.',
            );

        $user->refresh();

        verify($user->password_reset_token)
            ->notEquals(
                'expiredtoken_1000000000',
                'Failed asserting that the expired token was replaced with a new one.',
            );
    }

    public function testSendEmailReturnsFalseWhenSaveFails(): void
    {
        $user = User::findByUsername('okirlin');

        self::assertInstanceOf(
            User::class,
            $user,
            "Failed asserting that fixture user 'okirlin' exists.",
        );

        // set an expired token so `generatePasswordResetToken()` + `save()` path is triggered.
        $user->password_reset_token = 'expiredtoken_1000000000';

        self::assertTrue(
            $user->save(false),
            'Failed asserting that the expired token was persisted.',
        );

        // force `save()` to fail via `EVENT_BEFORE_SAVE` at the class level.
        $handler = static function (ModelEvent $event): void {
            $event->isValid = false;
        };

        Event::on(User::class, BaseActiveRecord::EVENT_BEFORE_UPDATE, $handler);

        $model = new PasswordResetRequestForm();

        $model->email = $user->email;

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

    public function testSendEmailRollsBackRegeneratedTokenWhenMailerFails(): void
    {
        $user = User::findByUsername('okirlin');

        self::assertInstanceOf(
            User::class,
            $user,
            "Failed asserting that fixture user 'okirlin' exists.",
        );

        $user->password_reset_token = 'expiredtoken_1000000000';

        self::assertTrue(
            $user->save(false),
            "Failed asserting that the 'expired token' was persisted.",
        );

        $handler = static function (MailEvent $event): void {
            $event->isValid = false;
        };

        Yii::$app->mailer->on(BaseMailer::EVENT_BEFORE_SEND, $handler);

        $model = new PasswordResetRequestForm();

        $model->email = $user->email;

        $supportEmail = Yii::$app->params['supportEmail'];

        try {
            verify($model->sendEmail(Yii::$app->mailer, $supportEmail, Yii::$app->name))
                ->false(
                    "Failed asserting that sendEmail returns 'false' when mail sending fails.",
                );
        } finally {
            Yii::$app->mailer->off(BaseMailer::EVENT_BEFORE_SEND, $handler);
        }

        $user->refresh();

        verify($user->password_reset_token)
            ->equals(
                'expiredtoken_1000000000',
                'Failed asserting that the regenerated token was rolled back when email sending fails.',
            );
    }

    public function testSendEmailSuccessfully(): void
    {
        $user = User::findByUsername('okirlin');

        self::assertInstanceOf(
            User::class,
            $user,
            "Failed asserting that fixture user 'okirlin' exists.",
        );

        $model = new PasswordResetRequestForm();

        $model->email = $user->email;

        $supportEmail = Yii::$app->params['supportEmail'];

        verify($model->sendEmail(Yii::$app->mailer, $supportEmail, Yii::$app->name))
            ->notEmpty(
                "Failed asserting that 'password reset' email is sent successfully.",
            );

        $user->refresh();

        verify($user->password_reset_token)
            ->notEmpty(
                "Failed asserting that user has a 'password reset token' after sending.",
            );

        /** @phpstan-var MessageInterface|null $emailMessage */
        $emailMessage = $this->tester?->grabLastSentEmail();

        verify($emailMessage)
            ->instanceOf(
                MessageInterface::class,
                'Failed asserting that a reset email was captured.',
            );
        verify($emailMessage?->getTo())
            ->arrayHasKey(
                $model->email,
                'Failed asserting that email is sent to the requested address.',
            );
        verify($emailMessage?->getFrom())
            ->arrayHasKey(
                $supportEmail,
                'Failed asserting that email is sent from the support address.',
            );

        /** @phpstan-var Message $emailMessage */
        $body = $emailMessage->getSymfonyEmail()->getHtmlBody() . $emailMessage->getSymfonyEmail()->getTextBody();

        verify($user->password_reset_token)
            ->notNull('Failed asserting that user has a password reset token.');

        /** @phpstan-var string $token */
        $token = $user->password_reset_token;

        verify($body)
            ->stringContainsString(
                $token,
                'Failed asserting that email body contains the password reset token.',
            );
    }

    public function testSendMessageWithWrongEmailAddress(): void
    {
        $model = new PasswordResetRequestForm();

        $model->email = 'not-existing-email@example.com';

        $supportEmail = Yii::$app->params['supportEmail'];

        verify($model->sendEmail(Yii::$app->mailer, $supportEmail, Yii::$app->name))
            ->false(
                "Failed asserting that sendEmail returns 'false' for a non-existing email address.",
            );
    }
}
