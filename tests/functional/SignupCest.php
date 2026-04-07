<?php

declare(strict_types=1);

namespace app\tests\functional;

use app\models\User;
use app\tests\support\FunctionalTester;
use Yii;
use yii\helpers\Url;
use yii\mail\{BaseMailer, MailEvent};

/**
 * Functional tests for {@see \app\controllers\UserController::actionSignup()} signup form via Inertia.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 0.1
 */
final class SignupCest
{
    public function signupFailsWhenMailerErrors(FunctionalTester $I): void
    {
        $handler = static function (MailEvent $event): void {
            $event->isValid = false;
        };

        Yii::$app->mailer->on(BaseMailer::EVENT_BEFORE_SEND, $handler);

        try {
            $I->amOnPage(Url::toRoute('/user/signup'));
            $I->sendAjaxPostRequest(
                Url::toRoute('/user/signup'),
                [
                    'SignupForm' => [
                        'username' => 'signup_fail_user',
                        'email' => 'signup.fail@example.com',
                        'password' => 'tester_password',
                    ],
                ],
            );

            $I->seeResponseCodeIs(302);
            $I->seeRecord(
                User::class,
                [
                    'username' => 'signup_fail_user',
                    'email' => 'signup.fail@example.com',
                    'status' => User::STATUS_INACTIVE,
                ],
            );
            $I->dontSeeEmailIsSent();
        } finally {
            Yii::$app->mailer->off(BaseMailer::EVENT_BEFORE_SEND, $handler);
        }

        $I->amOnPage(Url::toRoute('/user/signup'));
        $I->seeInSource('Sorry, we are unable to complete your registration at this time.');
    }

    public function signupSuccessfully(FunctionalTester $I): void
    {
        $I->amOnPage(Url::toRoute('/user/signup'));
        $I->sendAjaxPostRequest(
            Url::toRoute('/user/signup'),
            [
                'SignupForm' => [
                    'username' => 'tester',
                    'email' => 'tester.email@example.com',
                    'password' => 'tester_password',
                ],
            ],
        );

        $I->seeResponseCodeIs(302);

        $I->seeRecord(
            User::class,
            [
                'username' => 'tester',
                'email' => 'tester.email@example.com',
                'status' => User::STATUS_INACTIVE,
            ],
        );

        $I->seeEmailIsSent();
    }

    public function signupWithEmptyFields(FunctionalTester $I): void
    {
        $I->amOnPage(Url::toRoute('/user/signup'));
        $I->sendAjaxPostRequest(
            Url::toRoute('/user/signup'),
            ['SignupForm' => ['username' => '', 'email' => '', 'password' => '']],
        );
        $I->seeResponseCodeIs(302);
        $I->dontSeeEmailIsSent();
    }

    public function signupWithWrongEmail(FunctionalTester $I): void
    {
        $I->amOnPage(Url::toRoute('/user/signup'));
        $I->sendAjaxPostRequest(
            Url::toRoute('/user/signup'),
            [
                'SignupForm' => [
                    'username' => 'tester_wrong_email',
                    'email' => 'ttttt',
                    'password' => 'tester_password',
                ],
            ],
        );
        $I->seeResponseCodeIs(302);
        $I->dontSeeRecord(User::class, ['username' => 'tester_wrong_email']);
        $I->dontSeeEmailIsSent();
    }
}
