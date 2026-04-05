<?php

declare(strict_types=1);

namespace app\tests\unit\models;

use app\models\ContactForm;

/**
 * Unit tests for {@see ContactForm} Turnstile CAPTCHA validation.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 0.1
 */
final class ContactFormValidateTurnstileTest extends \Codeception\Test\Unit
{
    public function testValidateTurnstileSkipsWhenSecretKeyIsEmptyInTestEnv(): void
    {
        $model = new ContactForm();
        $model->name = 'Test';
        $model->email = 'test@example.com';
        $model->phone = '(555) 123-4567';
        $model->subject = 'Test subject';
        $model->body = 'Test body';
        $model->turnstileToken = 'test-token';

        $model->validate(['turnstileToken']);

        verify($model->hasErrors('turnstileToken'))->false(
            'Turnstile validation should be skipped when secret key is empty in test environment.',
        );
    }

    public function testValidateTurnstileSkipsWhenModelHasErrors(): void
    {
        $model = new ContactForm();
        $model->addError('name', 'Name is required.');
        $model->turnstileToken = 'test-token';

        $model->validateTurnstile('turnstileToken', null);

        verify($model->hasErrors('turnstileToken'))->false(
            'Turnstile validation should be skipped when model already has errors.',
        );
    }

    public function testValidateTurnstileWithTestKeysCallsCloudflare(): void
    {
        $originalKey = \Yii::$app->params['turnstile.secretKey'];
        \Yii::$app->params['turnstile.secretKey'] = '1x0000000000000000000000000000000AA';

        $model = new ContactForm();
        $model->turnstileToken = 'XXXX.DUMMY.TOKEN.XXXX';

        $model->validateTurnstile('turnstileToken', null);

        // Cloudflare test secret key always returns success for any token.
        verify($model->hasErrors('turnstileToken'))->false(
            'Turnstile validation should pass with Cloudflare test secret key.',
        );

        \Yii::$app->params['turnstile.secretKey'] = $originalKey;
    }

    public function testValidateTurnstileWithInvalidJsonResponse(): void
    {
        $originalKey = \Yii::$app->params['turnstile.secretKey'];
        \Yii::$app->params['turnstile.secretKey'] = '2x0000000000000000000000000000000AA';

        $model = new ContactForm();
        $model->turnstileToken = 'invalid-token';

        $model->validateTurnstile('turnstileToken', null);

        // With the "always fail" test key, Turnstile returns success: false.
        verify($model->hasErrors('turnstileToken'))->true(
            'Turnstile validation should fail with always-fail test secret key.',
        );

        \Yii::$app->params['turnstile.secretKey'] = $originalKey;
    }
}
