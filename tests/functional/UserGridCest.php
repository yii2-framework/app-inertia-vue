<?php

declare(strict_types=1);

namespace app\tests\functional;

use app\tests\support\Fixtures\UserFixture;
use app\tests\support\FunctionalTester;
use yii\helpers\Url;

/**
 * Functional tests for {@see \app\controllers\UserController::actionIndex()} user listing via Inertia.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 0.1
 */
final class UserGridCest
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

    public function checkAdminCanAccessUserList(FunctionalTester $I): void
    {
        $this->loginAsAdmin($I);

        $I->amOnPage(Url::toRoute('/user/index'));
        $I->seeResponseCodeIs(200);
    }

    public function checkAdminCanLogout(FunctionalTester $I): void
    {
        $this->loginAsAdmin($I);

        $I->sendAjaxPostRequest(Url::toRoute('/user/logout'));
        $I->seeResponseCodeIs(302);
    }

    public function checkGuestRedirectsToLogin(FunctionalTester $I): void
    {
        $I->amOnPage(Url::toRoute('/user/index'));
        $I->seeInCurrentUrl('/user/login');
    }

    public function checkNonAdminCannotAccessUserList(FunctionalTester $I): void
    {
        $I->amOnPage(Url::toRoute('/user/login'));
        $I->sendAjaxPostRequest(
            Url::toRoute('/user/login'),
            [
                'LoginForm' => [
                    'username' => 'okirlin',
                    'password' => 'password_0',
                ],
            ],
        );

        $I->amOnPage(Url::toRoute('/user/index'));
        $I->seeResponseCodeIs(403);
    }

    private function loginAsAdmin(FunctionalTester $I): void
    {
        $I->amOnPage(Url::toRoute('/user/login'));
        $I->sendAjaxPostRequest(
            Url::toRoute('/user/login'),
            [
                'LoginForm' => [
                    'username' => 'admin',
                    'password' => 'password_0',
                ],
            ],
        );
    }
}
