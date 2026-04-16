<?php

declare(strict_types=1);

namespace app\models;

use Throwable;
use Yii;
use yii\base\Model;
use yii\mail\MailerInterface;

/**
 * Handles password reset request via email.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 0.1
 */
final class PasswordResetRequestForm extends Model
{
    /**
     * Email address of the user requesting the password reset.
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
                'filter' => ['status' => User::STATUS_ACTIVE],
                'message' => 'Unable to process the request for the provided email address.',
            ],
        ];
    }

    /**
     * Sends an email with a link, for resetting the password.
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
                'status' => User::STATUS_ACTIVE,
                'email' => $this->email,
            ],
        );

        if ($user === null) {
            return false;
        }

        $transaction = null;

        if (!User::isPasswordResetTokenValid($user->password_reset_token)) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                $user->generatePasswordResetToken();

                if (!$user->save()) {
                    $transaction->rollBack();

                    return false;
                }
            } catch (Throwable $e) {
                $transaction->rollBack();

                Yii::error($e->getMessage(), __METHOD__);

                return false;
            }
        }

        try {
            $sent = $mailer
                ->compose(['html' => 'passwordResetToken-html', 'text' => 'passwordResetToken-text'], ['user' => $user])
                ->setFrom([$supportEmail => "{$appName} robot"])
                ->setTo($this->email)
                ->setSubject("Password reset for {$appName}")
                ->send();

            if (!$sent) {
                if ($transaction !== null && $transaction->isActive) {
                    $transaction->rollBack();
                }

                return false;
            }

            if ($transaction !== null && $transaction->isActive) {
                $transaction->commit();
            }

            return true;
        } catch (Throwable $e) {
            if ($transaction !== null && $transaction->isActive) {
                $transaction->rollBack();
            }

            Yii::error($e->getMessage(), __METHOD__);

            return false;
        }
    }
}
