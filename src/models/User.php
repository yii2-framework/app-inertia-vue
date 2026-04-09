<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * Provides database-backed identity implementation for authentication.
 *
 * @property int $id
 * @property string $username
 * @property string $password_hash
 * @property string|null $password_reset_token
 * @property string|null $verification_token
 * @property string $email
 * @property string $auth_key
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 * @property string $password write-only password
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 0.1
 */
class User extends ActiveRecord implements IdentityInterface
{
    /**
     * User is active and allowed to log in.
     */
    public const STATUS_ACTIVE = 10;
    /**
     * User is deleted and not allowed to log in.
     */
    public const STATUS_DELETED = 0;
    /**
     * User is inactive and not allowed to log in.
     */
    public const STATUS_INACTIVE = 9;

    /**
     * Returns the behaviors attached to this ActiveRecord.
     *
     * @return array List of behavior configurations indexed by behavior name or class.
     *
     * @phpstan-return array<array{class: class-string}|class-string>
     */
    public function behaviors(): array
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * Finds user by password reset token.
     *
     * @param string $token Password reset token to be looked for.
     *
     * @return self|null User corresponding to the provided password reset token, or `null` if no such user exists or
     * the token is invalid.
     */
    public static function findByPasswordResetToken(string $token): self|null
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne(
            [
                'password_reset_token' => $token,
                'status' => self::STATUS_ACTIVE,
            ],
        );
    }

    /**
     * Finds user by username.
     *
     * @param string $username Username to be looked for.
     *
     * @return self|null User corresponding to the provided username, or `null` if no such user exists or the user is
     * not active.
     */
    public static function findByUsername(string $username): self|null
    {
        return static::findOne(
            [
                'username' => $username,
                'status' => self::STATUS_ACTIVE,
            ],
        );
    }

    /**
     * Finds user by verification email token.
     *
     * @param string $token Verification email token to be looked for.
     *
     * @return self|null User corresponding to the provided verification email token, or `null` if no such user exists
     * or the token is invalid.
     */
    public static function findByVerificationToken(string $token): self|null
    {
        if (!static::isVerificationTokenValid($token)) {
            return null;
        }

        return static::findOne(
            [
                'verification_token' => $token,
                'status' => self::STATUS_INACTIVE,
            ],
        );
    }

    /**
     * Finds an identity by the given ID.
     *
     * @param int|string $id ID to be looked for.
     *
     * @return self|null User corresponding to the provided ID, or `null` if no such user exists or the user is not
     * active.
     */
    public static function findIdentity($id): self|null
    {
        return static::findOne(
            [
                'id' => $id,
                'status' => self::STATUS_ACTIVE,
            ],
        );
    }

    /**
     * Finds an identity by the given access token.
     *
     * @param mixed $token Access token to look up.
     * @param mixed $type Type identifier of the token, used to differentiate token contexts.
     *
     * @throws NotSupportedException Always, since access token authentication is not implemented.
     */
    public static function findIdentityByAccessToken($token, $type = null): never
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Generates the "remember me" authentication key and assigns it to the user.
     */
    public function generateAuthKey(): void
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates a new email verification token and assigns it to the user.
     */
    public function generateEmailVerificationToken(): void
    {
        $this->verification_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Generates a new password reset token and assigns it to the user.
     */
    public function generatePasswordResetToken(): void
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Returns the authentication key used to validate the user identity cookie.
     *
     * @return string Current user `auth_key` value.
     */
    public function getAuthKey(): string
    {
        return $this->auth_key;
    }

    /**
     * Returns the primary key value identifying this user.
     *
     * @return int|string Current user ID.
     */
    public function getId(): int|string
    {
        /* @phpstan-ignore-next-line */
        return $this->getPrimaryKey();
    }

    /**
     * Checks if password reset token is valid.
     *
     * @param string|null $token Token to be validated.
     *
     * @return bool `true` if the token is valid, `false` otherwise.
     */
    public static function isPasswordResetTokenValid(string|null $token): bool
    {
        return self::isTokenValid($token, 'user.passwordResetTokenExpire', 3600);
    }

    /**
     * Checks if verification email token is valid.
     *
     * @param string|null $token Token to be validated.
     *
     * @return bool `true` if the token is valid, `false` otherwise.
     */
    public static function isVerificationTokenValid(string|null $token): bool
    {
        return self::isTokenValid($token, 'user.emailVerificationTokenExpire', 86400);
    }

    /**
     * Clears the password reset token from the user.
     */
    public function removePasswordResetToken(): void
    {
        $this->password_reset_token = null;
    }

    /**
     * Returns the validation rules for the model attributes.
     *
     * @return array Validation rules for the model properties.
     *
     * @phpstan-return array<array<mixed>>
     */
    public function rules(): array
    {
        return [
            [
                'status',
                'default',
                'value' => self::STATUS_INACTIVE,
            ],
            [
                'status',
                'in',
                'range' => [
                    self::STATUS_ACTIVE,
                    self::STATUS_INACTIVE,
                    self::STATUS_DELETED,
                ],
            ],
        ];
    }

    /**
     * Generates password hash from password and sets it to the model.
     *
     * @param string $password Password to be hashed and set.
     */
    public function setPassword(string $password): void
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Returns the database table name associated with this ActiveRecord class.
     *
     * @return string Name of the database table associated with this ActiveRecord class.
     */
    public static function tableName(): string
    {
        return '{{%user}}';
    }

    /**
     * Validates the given authentication key against the stored one.
     *
     * @param string $authKey Auth key to be validated.
     *
     * @return bool `true` if the auth key is valid, `false` otherwise.
     */
    public function validateAuthKey($authKey): bool
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password.
     *
     * @param string $password Password to be validated.
     *
     * @return bool `true` if the password is valid, `false` otherwise.
     */
    public function validatePassword(string $password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Validates a timestamped token against a configurable expiration period.
     *
     * @param string|null $token Token to be validated.
     * @param string $paramKey Application parameter name that specifies the token expiration period.
     * @param int $defaultExpire Default expiration period in seconds, used if the application parameter is not set.
     *
     * @return bool `true` if the token is valid, `false` otherwise.
     */
    private static function isTokenValid(string|null $token, string $paramKey, int $defaultExpire): bool
    {
        if ($token === null || $token === '') {
            return false;
        }

        $searchToken = strrpos($token, '_');

        if ($searchToken === false) {
            return false;
        }

        $timestampPart = substr($token, $searchToken + 1);

        if ($timestampPart === '' || !ctype_digit($timestampPart)) {
            return false;
        }

        $timestamp = (int) $timestampPart;

        $expire = (int) (Yii::$app->params[$paramKey] ?? $defaultExpire);

        return $timestamp + $expire >= time();
    }
}
