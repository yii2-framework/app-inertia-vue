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

    public function testResendToActiveUser(): void
    {
        $model = new ResendVerificationEmailForm();

        $model->attributes = ['email' => 'test2.test@example.com'];

        verify($model->validate())
            ->false(
                'Failed asserting that validation fails for an already active user.',
            );
        verify($model->hasErrors())
            ->true(
                'Failed asserting that validation errors are present.',
            );
        verify($model->getFirstError('email'))
            ->equals(
                'Unable to process the request for the provided email address.',
                'Failed asserting that active user email is rejected by the inactive-only filter.',
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
    }

    public function testWrongEmailAddress(): void
    {
        $model = new ResendVerificationEmailForm();

        $model->attributes = ['email' => 'aaa@bbb.cc'];

        verify($model->validate())
            ->false(
                'Failed asserting that validation fails for a non-existing email.',
            );
        verify($model->hasErrors())
            ->true(
                'Failed asserting that validation errors are present.',
            );
        verify($model->getFirstError('email'))
            ->equals(
                'Unable to process the request for the provided email address.',
                'Failed asserting that the error message matches for a non-existing email.',
            );
    }
}
