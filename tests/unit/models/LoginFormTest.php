<?php

declare(strict_types=1);

namespace app\tests\unit\models;

use app\models\LoginForm;
use app\tests\support\Fixtures\UserFixture;
use Yii;

/**
 * Unit tests for {@see \app\models\LoginForm} model.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 0.1
 */
final class LoginFormTest extends \Codeception\Test\Unit
{
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

    public function testGetUserQueriesDatabaseOnlyOnceWhenUserDoesNotExist(): void
    {
        $model = new LoginForm(
            [
                'username' => 'not_existing_username',
                'password' => 'irrelevant',
            ],
        );

        $originalLogger = Yii::getLogger();
        $realLogger = new \yii\log\Logger();

        Yii::setLogger($realLogger);

        try {
            verify($model->getUser())
                ->null(
                    "Failed asserting that 'getUser()' returns 'null' on the first call for a non-existent username.",
                );

            $countAfterFirst = $this->countDbQueries($realLogger);

            verify($model->getUser())
                ->null(
                    "Failed asserting that 'getUser()' returns 'null' on the second call for a non-existent username.",
                );

            $countAfterSecond = $this->countDbQueries($realLogger);

            verify($countAfterSecond)
                ->equals(
                    $countAfterFirst,
                    "Failed asserting that the second 'getUser()' call issues no additional DB queries; "
                    . "expected {$countAfterFirst}, got {$countAfterSecond}.",
                );
        } finally {
            Yii::setLogger($originalLogger);
        }
    }

    public function testLoginCorrect(): void
    {
        $model = new LoginForm(
            [
                'username' => 'okirlin',
                'password' => 'password_0',
            ],
        );

        verify($model->login())
            ->true(
                'Failed asserting that login succeeds with correct credentials.',
            );
        verify(Yii::$app->user->isGuest)
            ->false(
                "Failed asserting that 'user' is no longer a guest after login.",
            );
        verify($model->errors)
            ->arrayHasNotKey(
                'password',
                "Failed asserting that 'password' error does not exist after successful login.",
            );
    }

    public function testLoginDeletedAccount(): void
    {
        $model = new LoginForm(
            [
                'username' => 'troy.becker',
                'password' => 'password_0',
            ],
        );

        verify($model->login())
            ->false(
                'Failed asserting that login fails for a deleted account.',
            );
        verify(Yii::$app->user->isGuest)
            ->true(
                "Failed asserting that 'user' remains a 'guest' after deleted account login attempt.",
            );
    }

    public function testLoginInactiveAccount(): void
    {
        $model = new LoginForm(
            [
                'username' => 'test.test',
                'password' => 'Test1234',
            ],
        );

        verify($model->login())
            ->false(
                'Failed asserting that login fails for an inactive account.',
            );
        verify(Yii::$app->user->isGuest)
            ->true(
                "Failed asserting that 'user' remains a 'guest' after inactive account login attempt.",
            );
    }

    public function testLoginNoUser(): void
    {
        $model = new LoginForm(
            [
                'username' => 'not_existing_username',
                'password' => 'not_existing_password',
            ],
        );

        verify($model->login())
            ->false(
                'Failed asserting that login fails with non-existing username.',
            );
        verify(Yii::$app->user->isGuest)
            ->true(
                "Failed asserting that 'user' remains a 'guest' after failed login.",
            );
    }

    public function testLoginReturnsFalseWhenUserIsNull(): void
    {
        $model = $this->make(
            LoginForm::class,
            [
                'validate' => true,
                'getUser' => null,
            ],
        );

        verify($model->login())
            ->false(
                "Failed asserting that login returns 'false' when user is 'null' after validation.",
            );
    }

    public function testLoginWrongPassword(): void
    {
        $model = new LoginForm(
            [
                'username' => 'okirlin',
                'password' => 'wrong_password',
            ],
        );

        verify($model->login())
            ->false(
                'Failed asserting that login fails with wrong password.',
            );
        verify(Yii::$app->user->isGuest)
            ->true(
                "Failed asserting that 'user' remains a 'guest' after wrong password.",
            );
        verify($model->errors)
            ->arrayHasKey(
                'password',
                "Failed asserting that a 'password' validation error is present.",
            );
    }

    protected function _after(): void
    {
        Yii::$app->user->logout();
    }

    private function countDbQueries(\yii\log\Logger $logger): int
    {
        $count = 0;

        foreach ($logger->messages as $message) {
            if (!is_array($message)) {
                continue;
            }

            $category = $message[2] ?? null;

            if (is_string($category) && str_starts_with($category, 'yii\db\Command::query')) {
                $count++;
            }
        }

        return $count;
    }
}
