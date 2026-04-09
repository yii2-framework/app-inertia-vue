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
     * @param array $config name-value pairs that will be used to initialize the object properties.
     *
     * @throws InvalidArgumentException if token is empty or not valid.
     *
     * @phpstan-param array<string, mixed> $config
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
     * Resets password.
     *
     * @return bool Whether the password was reset successfully
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
     *
     * @return array Validation rules for the model properties.
     *
     * @phpstan-return array<array<mixed>>
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
