<?php

declare(strict_types=1);

namespace app\models;

use Throwable;
use Yii;
use yii\base\Model;
use yii\mail\MailerInterface;

/**
 * Handles resending of verification email to inactive users.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 0.1
 */
final class ResendVerificationEmailForm extends Model
{
    /**
     * Email address of the user requesting a new verification email.
     */
    public string $email = '';

    /**
     * Returns the validation rules for the form attributes.
     */
    public function rules(): array
    {
        return [
            [
                'email',
                'trim',
            ],
            [
                'email',
                'required',
            ],
            [
                'email',
                'email',
            ],
            [
                'email',
                'exist',
                'targetClass' => User::class,
                'filter' => ['status' => User::STATUS_INACTIVE],
                'message' => 'Unable to process the request for the provided email address.',
            ],
        ];
    }

    /**
     * Sends confirmation email to user.
     *
     * @param MailerInterface $mailer Mailer component used to send the email.
     * @param string $supportEmail Support email address to use as the sender.
     * @param string $appName Application name to use in the email subject and sender name.
     *
     * @return bool Whether the email was sent successfully.
     */
    public function sendEmail(MailerInterface $mailer, string $supportEmail, string $appName): bool
    {
        $user = User::findOne(
            [
                'email' => $this->email,
                'status' => User::STATUS_INACTIVE,
            ],
        );

        if ($user === null) {
            return false;
        }

        $transaction = null;

        try {
            $transaction = Yii::$app->db->beginTransaction();

            $user->generateEmailVerificationToken();

            if (!$user->save(false)) {
                $transaction->rollBack();

                return false;
            }

            $sent = $mailer
                ->compose(['html' => 'emailVerify-html', 'text' => 'emailVerify-text'], ['user' => $user])
                ->setFrom([$supportEmail => "{$appName} robot"])
                ->setTo($this->email)
                ->setSubject("Account registration at {$appName}")
                ->send();

            if (!$sent) {
                $transaction->rollBack();

                return false;
            }

            $transaction->commit();

            return true;
        } catch (Throwable $e) {
            if ($transaction !== null && $transaction->isActive) {
                $transaction->rollBack();
            }

            Yii::error(
                $e->getMessage(),
                __METHOD__,
            );

            return false;
        }
    }
}
