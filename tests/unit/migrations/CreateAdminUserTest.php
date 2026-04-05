<?php

declare(strict_types=1);

namespace app\tests\unit\migrations;

use app\migrations\M260403000000CreateAdminUser;
use app\models\User;
use Yii;

/**
 * Unit tests for {@see M260403000000CreateAdminUser} migration.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 0.1
 */
final class CreateAdminUserTest extends \Codeception\Test\Unit
{
    public function testSafeDownDeletesAdminUser(): void
    {
        $db = Yii::$app->db;
        $migration = new M260403000000CreateAdminUser(['db' => $db]);

        /** @phpstan-var string $expectedUsername */
        $expectedUsername = Yii::$app->params['admin.username'] ?? 'admin';

        $db->createCommand()->delete('{{%user}}', ['username' => $expectedUsername])->execute();
        $migration->up();

        $admin = User::find()->where(['username' => $expectedUsername])->one();

        verify($admin)
            ->notNull(
                "Failed asserting that admin user exists after 'safeUp'.",
            );

        $migration->down();

        $admin = User::find()->where(['username' => $expectedUsername])->one();

        verify($admin)
            ->null(
                "Failed asserting that admin user is deleted after 'safeDown'.",
            );
    }

    public function testSafeUpCreatesAdminUser(): void
    {
        $db = Yii::$app->db;

        /** @phpstan-var string $expectedUsername */
        $expectedUsername = Yii::$app->params['admin.username'] ?? 'admin';
        /** @phpstan-var string $expectedEmail */
        $expectedEmail = Yii::$app->params['admin.email'] ?? 'admin@example.com';

        // clean up if admin already exists from fixtures.
        $db->createCommand()->delete('{{%user}}', ['username' => $expectedUsername])->execute();

        $migration = new M260403000000CreateAdminUser(['db' => $db]);

        $migration->up();

        $admin = User::find()->where(['username' => $expectedUsername])->one();

        self::assertInstanceOf(
            User::class,
            $admin,
            'Failed asserting that admin user exists.',
        );

        verify($admin->username)->equals($expectedUsername);
        verify($admin->email)->equals($expectedEmail);
        verify($admin->status)
            ->equals(
                User::STATUS_ACTIVE,
                "Failed asserting that 'status' is 'active'.",
            );
        /** @phpstan-var string $expectedPassword */
        $expectedPassword = Yii::$app->params['admin.password'] ?? 'admin';

        verify(Yii::$app->security->validatePassword($expectedPassword, $admin->password_hash))
            ->true(
                'Failed asserting that admin password matches configured value.',
            );

        // clean up for other tests.
        $migration->down();
    }
}
