<?php

declare(strict_types=1);

namespace app\tests\acceptance;

use app\tests\support\AcceptanceTester;
use app\tests\support\Fixtures\UserFixture;
use yii\helpers\Url;

/**
 * Acceptance tests for the login page.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 0.1
 */
final class LoginCest
{
    /**
     * @return array{user: array{class: string, dataFile: string}}
     */
    public function _fixtures(): array
    {
        return [
            'user' => [
                'class' => UserFixture::class,
                // @phpstan-ignore binaryOp.invalid
                'dataFile' => codecept_data_dir() . 'login_data.php',
            ],
        ];
    }

    public function ensureThatLoginPageWorks(AcceptanceTester $I): void
    {
        $I->amOnPage(Url::toRoute('/user/login'));
        $I->seeResponseCodeIs(200);
        $I->seeInSource('"component":"User\/Login"');
    }
}
