<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Represents the login form model with username/password authentication.
 *
 * @property User|null $user
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 0.1
 */
class LoginForm extends Model
{
    /**
     * Plain text password submitted by the user.
     */
    public string $password = '';

    /**
     * Whether to keep the user logged in across browser sessions.
     */
    public bool $rememberMe = true;

    /**
     * Username submitted by the user.
     */
    public string $username = '';

    /**
     * Resolved user instance, or `null` when not yet loaded or not found.
     */
    private User|null $user = null;

    /**
     * Finds user by [[username]].
     *
     * @return User|null User `object`, or `null` if not found.
     */
    public function getUser(): User|null
    {
        if ($this->user === null) {
            $this->user = User::findByUsername($this->username);
        }

        return $this->user;
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return bool Whether the user is logged in successfully.
     */
    public function login(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $user = $this->getUser();

        if ($user === null) {
            return false;
        }

        return Yii::$app->user->login($user, $this->rememberMe ? 3600 * 24 * 30 : 0);
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
                [
                    'username',
                    'password',
                ],
                'required',
            ],
            [
                'rememberMe',
                'boolean',
            ],
            [
                'password',
                'validatePassword',
            ],
        ];
    }

    /**
     * Validates the password against the resolved user.
     *
     * Inline validator for the `password` attribute.
     *
     * @param string $attribute Attribute currently being validated.
     * @param mixed $params Validator parameters configured in {@see rules()}.
     */
    public function validatePassword(string $attribute, mixed $params): void
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if ($user === null || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }
}
