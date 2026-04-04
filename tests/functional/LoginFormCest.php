<?php

declare(strict_types=1);

namespace app\tests\functional;

use app\tests\support\Fixtures\UserFixture;
use app\tests\support\FunctionalTester;
use yii\helpers\Url;

/**
 * Functional tests for {@see \app\controllers\UserController::actionLogin()} login form via Inertia.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 0.1
 */
final class LoginFormCest
{
    /**
     * @phpstan-return array{user: array{class: string, dataFile: string}}
     */
    public function _fixtures(): array
    {
        return [
            'user' => [
                'class' => UserFixture::class,
                // @phpstan-ignore-next-line
                'dataFile' => codecept_data_dir() . 'login_data.php',
            ],
        ];
    }

    public function checkEmptySubmission(FunctionalTester $I): void
    {
        $I->amOnPage(Url::toRoute('/user/login'));
        $I->sendAjaxPostRequest(
            Url::toRoute('/user/login'),
            ['LoginForm' => ['username' => '', 'password' => '']],
        );
        $I->seeResponseCodeIs(302);
        $I->seeInCurrentUrl('/user/login');
    }

    public function checkInactiveAccount(FunctionalTester $I): void
    {
        $I->amOnPage(Url::toRoute('/user/login'));
        $I->sendAjaxPostRequest(
            Url::toRoute('/user/login'),
            ['LoginForm' => ['username' => 'test.test', 'password' => 'Test1234']],
        );
        $I->seeResponseCodeIs(302);
        $I->seeInCurrentUrl('/user/login');
    }

    public function checkValidLogin(FunctionalTester $I): void
    {
        $I->amOnPage(Url::toRoute('/user/login'));
        $I->sendAjaxPostRequest(
            Url::toRoute('/user/login'),
            ['LoginForm' => ['username' => 'erau', 'password' => 'password_0']],
        );
        $I->seeResponseCodeIs(302);
    }

    public function checkWrongPassword(FunctionalTester $I): void
    {
        $I->amOnPage(Url::toRoute('/user/login'));
        $I->sendAjaxPostRequest(
            Url::toRoute('/user/login'),
            ['LoginForm' => ['username' => 'erau', 'password' => 'wrong']],
        );
        $I->seeResponseCodeIs(302);
        $I->seeInCurrentUrl('/user/login');
    }
}
