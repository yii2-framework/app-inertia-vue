<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\base\InvalidArgumentException;
use yii\base\Model;

/**
 * Handles password reset with a valid token.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 0.1
 */
final class ResetPasswordForm extends Model
{
    /**
     * New plain text password to assign to the user.
     */
    public string $password = '';

    /**
     * User resolved from the password reset token, or `null` if not found.
     */
    private User|null $user = null;

    /**
     * Creates a form model given a token.
     *
     * @param string $token the password reset token.
     * @param array<string, mixed> $config name-value pairs that will be used to initialize the object properties.
     *
     * @throws InvalidArgumentException if token is empty or not valid.
     */
    public function __construct(string $token, array $config = [])
    {
        $token = trim($token);

        if ($token === '') {
            throw new InvalidArgumentException('Password reset token cannot be blank.');
        }

        $this->user = User::findByPasswordResetToken($token);

        if ($this->user === null) {
            throw new InvalidArgumentException('Wrong password reset token.');
        }

        parent::__construct($config);
    }

    /**
     * Resets the user password and invalidates the reset token.
     *
     * The caller MUST invoke {@see Model::validate()} before calling this method; `resetPassword()` does not validate
     * internally. The standard Yii2 controller pattern applies.
     *
     * Usage example:
     * ```php
     * $model->load($post) && $model->validate() && $model->resetPassword();
     * ```
     *
     * Bypassing validation skips the `required` and `min` constraints declared in {@see self::rules()} and may persist
     * an invalid password.
     *
     * @return bool `true` on successful save; `false` when the save fails. The null-user branch is a defensive
     * internal guard, since {@see self::__construct()} throws on invalid or unresolved tokens.
     *
     * @see \app\controllers\UserController::actionResetPassword() for the canonical usage.
     */
    public function resetPassword(): bool
    {
        if ($this->user === null) {
            return false;
        }

        $this->user->setPassword($this->password);
        $this->user->removePasswordResetToken();
        $this->user->generateAuthKey();

        return $this->user->save(false);
    }

    /**
     * Returns the validation rules for the form attributes.
     */
    public function rules(): array
    {
        return [
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
}
