<?php

declare(strict_types=1);

namespace app\tests\unit\models;

use app\models\ContactForm;
use Codeception\Stub;
use Yii;

/**
 * Unit tests for {@see ContactForm} Turnstile CAPTCHA validation.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 0.1
 */
final class ContactFormValidateTurnstileTest extends \Codeception\Test\Unit
{
    public function testValidateTurnstileSkipsWhenModelHasErrors(): void
    {
        $model = new ContactForm();

        $model->addError('name', 'Name is required.');

        $model->turnstileToken = 'test-token';

        $model->validateTurnstile('turnstileToken', null);

        verify($model->hasErrors('turnstileToken'))
            ->false(
                'Turnstile validation should be skipped when model already has errors.',
            );
    }

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

        verify($model->hasErrors('turnstileToken'))
            ->false(
                'Turnstile validation should be skipped when secret key is empty in test environment.',
            );
    }

    public function testValidateTurnstileWithCloudflareFailKeys(): void
    {
        $originalKey = Yii::$app->params['turnstile.secretKey'];
        Yii::$app->params['turnstile.secretKey'] = '2x0000000000000000000000000000000AA';

        $model = new ContactForm();

        $model->turnstileToken = 'test-token';

        $model->validateTurnstile('turnstileToken', null);

        verify($model->hasErrors('turnstileToken'))
            ->true(
                'Turnstile validation should fail with Cloudflare always-fail test key.',
            );

        Yii::$app->params['turnstile.secretKey'] = $originalKey;
    }

    public function testValidateTurnstileWithCloudflareTestKeys(): void
    {
        $originalKey = Yii::$app->params['turnstile.secretKey'];
        Yii::$app->params['turnstile.secretKey'] = '1x0000000000000000000000000000000AA';

        $model = Stub::construct(
            ContactForm::class,
            [],
            ['fetchTurnstileResponse' => '{"success": true}'],
        );

        $model->turnstileToken = 'test-token';

        $model->validateTurnstile('turnstileToken', null);

        verify($model->hasErrors('turnstileToken'))
            ->false(
                'Turnstile validation should pass with Cloudflare always-pass test key.',
            );

        Yii::$app->params['turnstile.secretKey'] = $originalKey;
    }

    public function testValidateTurnstileWithFailureResponse(): void
    {
        $originalKey = Yii::$app->params['turnstile.secretKey'];
        Yii::$app->params['turnstile.secretKey'] = 'test-secret';

        $model = Stub::construct(
            ContactForm::class,
            [],
            ['fetchTurnstileResponse' => '{"success": false}'],
        );

        $model->turnstileToken = 'invalid-token';

        $model->validateTurnstile('turnstileToken', null);

        verify($model->hasErrors('turnstileToken'))
            ->true(
                'Turnstile validation should fail when API returns success: false.',
            );

        Yii::$app->params['turnstile.secretKey'] = $originalKey;
    }

    public function testValidateTurnstileWithInvalidJsonResponse(): void
    {
        $originalKey = Yii::$app->params['turnstile.secretKey'];
        Yii::$app->params['turnstile.secretKey'] = 'test-secret';

        $model = Stub::construct(
            ContactForm::class,
            [],
            ['fetchTurnstileResponse' => 'not-valid-json'],
        );

        $model->turnstileToken = 'some-token';

        $model->validateTurnstile('turnstileToken', null);

        verify($model->hasErrors('turnstileToken'))
            ->true(
                'Turnstile validation should fail when API returns invalid JSON.',
            );

        Yii::$app->params['turnstile.secretKey'] = $originalKey;
    }

    public function testValidateTurnstileWithNullResponse(): void
    {
        $originalKey = Yii::$app->params['turnstile.secretKey'];
        Yii::$app->params['turnstile.secretKey'] = 'test-secret';

        $model = Stub::construct(
            ContactForm::class,
            [],
            ['fetchTurnstileResponse' => null],
        );

        $model->turnstileToken = 'some-token';

        $model->validateTurnstile('turnstileToken', null);

        verify($model->hasErrors('turnstileToken'))
            ->true(
                'Turnstile validation should fail when HTTP request returns null.',
            );

        Yii::$app->params['turnstile.secretKey'] = $originalKey;
    }

    public function testValidateTurnstileWithSuccessResponse(): void
    {
        $originalKey = Yii::$app->params['turnstile.secretKey'];
        Yii::$app->params['turnstile.secretKey'] = 'test-secret';

        $model = Stub::construct(
            ContactForm::class,
            [],
            ['fetchTurnstileResponse' => '{"success": true}'],
        );

        $model->turnstileToken = 'valid-token';

        $model->validateTurnstile('turnstileToken', null);

        verify($model->hasErrors('turnstileToken'))
            ->false(
                'Turnstile validation should pass when API returns success.',
            );

        Yii::$app->params['turnstile.secretKey'] = $originalKey;
    }
}
