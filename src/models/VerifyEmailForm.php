<?php

declare(strict_types=1);

namespace app\models;

use yii\base\InvalidArgumentException;
use yii\base\Model;

/**
 * Handles email verification after user registration.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 0.1
 */
final class VerifyEmailForm extends Model
{
    /**
     * User resolved from the verification token, or `null` if not found.
     */
    private User|null $user = null;

    /**
     * Creates a form model with given token.
     *
     * @param string $token Verification token.
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
            throw new InvalidArgumentException('Verify email token cannot be blank.');
        }

        $this->user = User::findByVerificationToken($token);

        if ($this->user === null) {
            throw new InvalidArgumentException('Wrong verify email token.');
        }

        parent::__construct($config);
    }

    /**
     * Verifies email.
     *
     * @return User|null Saved model or `null` if saving fails.
     */
    public function verifyEmail(): User|null
    {
        if ($this->user === null) {
            return null;
        }

        $this->user->status = User::STATUS_ACTIVE;
        $this->user->verification_token = null;

        return $this->user->save(false) ? $this->user : null;
    }
}
