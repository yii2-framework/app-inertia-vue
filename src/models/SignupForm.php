<?php

declare(strict_types=1);

namespace app\models;

use Throwable;
use Yii;
use yii\base\Model;
use yii\mail\MailerInterface;

/**
 * Handles user registration with email verification.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 0.1
 */
final class SignupForm extends Model
{
    /**
     * Email address used to register the new user.
     */
    public string $email = '';
    /**
     * Plain text password chosen by the new user.
     */
    public string $password = '';
    /**
     * Username chosen by the new user.
     */
    public string $username = '';

    /**
     * Returns the validation rules for the form attributes.
     *
     * @return array Validation rules for the model properties.
     *
     * @phpstan-return array<array<mixed>>
     */
    public function rules(): array
    {
        return [
            [
                'username',
                'trim',
            ],
            [
                'username',
                'required',
            ],
            [
                'username',
                'unique',
                'targetClass' => User::class,
                'message' => 'This username has already been taken.',
            ],
            [
                'username',
                'string',
                'min' => 2,
                'max' => 255,
            ],

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
                'string',
                'max' => 255,
            ],
            [
                'email',
                'unique',
                'targetClass' => User::class,
                'message' => 'This email address has already been taken.',
            ],

            [
                'password',
                'required',
            ],
            [
                'password',
                'string',
                'min' => Yii::$app->params['user.passwordMinLength'],
            ],
        ];
    }

    /**
     * Signs user up.
     *
     * @param MailerInterface $mailer Mailer component used to send the email.
     * @param string $supportEmail Support email address to use as the sender.
     * @param string $appName Application name to use in the email subject and sender name.
     *
     * @return bool|null `true` on success, `false` on failure, or `null` if validation fails.
     */
    public function signup(MailerInterface $mailer, string $supportEmail, string $appName): bool|null
    {
        if (!$this->validate()) {
            return null;
        }

        $transaction = null;

        try {
            $transaction = Yii::$app->db->beginTransaction();

            $user = new User();

            $user->username = $this->username;
            $user->email = $this->email;

            $user->setPassword($this->password);
            $user->generateAuthKey();
            $user->generateEmailVerificationToken();

            if (!$user->save()) {
                $transaction->rollBack();

                return false;
            }

            $transaction->commit();
            $transaction = null;

            return $this->sendEmail($mailer, $user, $supportEmail, $appName);
        } catch (Throwable $e) {
            if ($transaction !== null && $transaction->isActive) {
                $transaction->rollBack();
            }

            Yii::error($e->getMessage(), __METHOD__);

            return false;
        }
    }

    /**
     * Sends confirmation email to user.
     *
     * @param MailerInterface $mailer Mailer component used to send the email.
     * @param User $user User to whom the email will be sent.
     * @param string $supportEmail Support email address to use as the sender.
     * @param string $appName Application name to use in the email subject and sender name.
     *
     * @return bool Whether the email was sent successfully.
     */
    protected function sendEmail(MailerInterface $mailer, User $user, string $supportEmail, string $appName): bool
    {
        return $mailer
            ->compose(['html' => 'emailVerify-html', 'text' => 'emailVerify-text'], ['user' => $user])
            ->setFrom([$supportEmail => "{$appName} robot"])
            ->setTo($this->email)
            ->setSubject("Account registration at {$appName}")
            ->send();
    }
}
