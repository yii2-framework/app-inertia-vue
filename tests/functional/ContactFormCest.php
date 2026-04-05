<?php

declare(strict_types=1);

namespace app\tests\functional;

use app\tests\support\FunctionalTester;
use Yii;
use yii\helpers\Url;
use yii\mail\{BaseMailer, MailEvent};

/**
 * Functional tests for {@see \app\controllers\SiteController::actionContact()} contact form via Inertia.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 0.1
 */
final class ContactFormCest
{
    public function openContactPage(FunctionalTester $I): void
    {
        $I->amOnPage(Url::toRoute('/site/contact'));
        $I->seeResponseCodeIs(200);
    }

    public function submitEmptyForm(FunctionalTester $I): void
    {
        $I->amOnPage(Url::toRoute('/site/contact'));
        $I->sendAjaxPostRequest(
            Url::toRoute('/site/contact'),
            [
                'ContactForm' => [
                    'name' => '',
                    'email' => '',
                    'phone' => '',
                    'subject' => '',
                    'body' => '',
                ],
            ],
        );
        $I->seeResponseCodeIs(302);
        $I->dontSeeEmailIsSent();
    }

    public function submitFormFailsWhenMailerErrors(FunctionalTester $I): void
    {
        $handler = static function (MailEvent $event): void {
            $event->isValid = false;
        };

        Yii::$app->mailer->on(BaseMailer::EVENT_BEFORE_SEND, $handler);

        try {
            $I->amOnPage(Url::toRoute('/site/contact'));
            $I->sendAjaxPostRequest(
                Url::toRoute('/site/contact'),
                [
                    'ContactForm' => [
                        'name' => 'tester',
                        'email' => 'tester@example.com',
                        'phone' => '(555) 123-4567',
                        'subject' => 'test subject',
                        'body' => 'test content',
                        'turnstileToken' => 'test-token',
                    ],
                ],
            );
            $I->seeResponseCodeIs(302);
            $I->dontSeeEmailIsSent();
        } finally {
            Yii::$app->mailer->off(BaseMailer::EVENT_BEFORE_SEND, $handler);
        }

        $I->amOnPage(Url::toRoute('/site/contact'));
        $I->seeInSource('Sorry, we are unable to send your message at this time.');
    }

    public function submitFormSuccessfully(FunctionalTester $I): void
    {
        $I->amOnPage(Url::toRoute('/site/contact'));
        $I->sendAjaxPostRequest(
            Url::toRoute('/site/contact'),
            [
                'ContactForm' => [
                    'name' => 'tester',
                    'email' => 'tester@example.com',
                    'phone' => '(555) 123-4567',
                    'subject' => 'test subject',
                    'body' => 'test content',
                    'turnstileToken' => 'test-token',
                ],
            ],
        );
        $I->seeEmailIsSent();
        $I->seeResponseCodeIs(302);
    }

    public function submitFormWithIncorrectEmail(FunctionalTester $I): void
    {
        $I->amOnPage(Url::toRoute('/site/contact'));
        $I->sendAjaxPostRequest(
            Url::toRoute('/site/contact'),
            [
                'ContactForm' => [
                    'name' => 'tester',
                    'email' => 'tester.email',
                    'phone' => '(555) 123-4567',
                    'subject' => 'test subject',
                    'body' => 'test content',
                ],
            ],
        );
        $I->seeResponseCodeIs(302);
        $I->dontSeeEmailIsSent();
    }
}
