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
        $cooldown = Yii::$app->params['user.resendVerificationEmailCooldown'];

        if ($cooldown > 0) {
            $cacheKey = 'resend-verification-email:' . sha1(trim($this->email));
            $cache = Yii::$app->cache;

            if ($cache->add($cacheKey, 1, $cooldown) === false && $cache->exists($cacheKey)) {
                Yii::info('Resend verification email rate-limited.', __METHOD__);

                return false;
            }
        }

        $user = User::findOne(
            [
                'email' => $this->email,
                'status' => User::STATUS_INACTIVE,
            ],
        );

        if ($user === null) {
            Yii::info('No inactive user found for submitted email.', __METHOD__);

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

            $transaction->commit();
            $transaction = null;
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

        $committedToken = User::find()
            ->select(['verification_token'])
            ->where(['id' => $user->id])
            ->scalar();

        if ($committedToken !== $user->verification_token) {
            Yii::warning(
                'Verification token was overwritten by a concurrent request before send; aborting.',
                __METHOD__,
            );

            return false;
        }

        try {
            return $mailer
                ->compose(['html' => 'emailVerify-html', 'text' => 'emailVerify-text'], ['user' => $user])
                ->setFrom([$supportEmail => "{$appName} robot"])
                ->setTo($this->email)
                ->setSubject("Account registration at {$appName}")
                ->send();
        } catch (Throwable $e) {
            Yii::error(
                $e->getMessage(),
                __METHOD__,
            );

            return false;
        }
    }
}
