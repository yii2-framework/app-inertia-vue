<?php

declare(strict_types=1);

namespace app\models;

use Throwable;
use Yii;
use yii\base\{InvalidArgumentException, Model};
use yii\helpers\Json;
use yii\mail\MailerInterface;

/**
 * Represents the contact form model with phone validation, Turnstile CAPTCHA, and email sending.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 0.1
 */
class ContactForm extends Model
{
    /**
     * Message body submitted by the user.
     */
    public string $body = '';

    /**
     * Sender email address.
     */
    public string $email = '';

    /**
     * Sender full name.
     */
    public string $name = '';

    /**
     * Sender phone number in `(999) 999-9999` format.
     */
    public string $phone = '';

    /**
     * Message subject.
     */
    public string $subject = '';

    /**
     * Cloudflare Turnstile CAPTCHA token submitted by the client.
     */
    public string $turnstileToken = '';

    /**
     * Returns the attribute labels for form fields.
     *
     * @return array Attribute labels for the model properties.
     *
     * @phpstan-return array<string, string>
     */
    public function attributeLabels(): array
    {
        return [
            'turnstileToken' => 'CAPTCHA verification',
        ];
    }

    /**
     * Sends an email to the specified email address using the information collected by this model.
     *
     * @param MailerInterface $mailer Mailer component used to send the email.
     * @param string $email Recipient email address.
     * @param string $senderEmail Sender email address used in the `From` header.
     * @param string $senderName Sender display name used in the `From` header.
     *
     * @return bool Whether the email was sent successfully.
     */
    public function contact(MailerInterface $mailer, string $email, string $senderEmail, string $senderName): bool
    {
        if ($this->validate()) {
            $messageBody = "Name: {$this->name}\nEmail: {$this->email}\nPhone: {$this->phone}\n\n{$this->body}";

            return $mailer->compose()
                ->setTo($email)
                ->setFrom([$senderEmail => $senderName])
                ->setReplyTo([$this->email => $this->name])
                ->setSubject($this->subject)
                ->setTextBody($messageBody)
                ->send();
        }

        return false;
    }

    /**
     * Returns the validation rules for the form attributes.
     *
     * @return array Validation rules for the model properties.
     *
     * @phpstan-return array<array<mixed>>
     */
    public function rules(): array
    {
        return [
            [
                [
                    'name',
                    'email',
                    'phone',
                    'subject',
                    'body',
                    'turnstileToken',
                ],
                'required',
            ],
            [
                'email',
                'email',
            ],
            [
                'phone',
                'match',
                'pattern' => '/^\(\d{3}\) \d{3}-\d{4}$/',
                'message' => 'Phone number must match (999) 999-9999 format.',
            ],
            [
                'turnstileToken',
                'validateTurnstile',
            ],
        ];
    }

    /**
     * Validates the Turnstile CAPTCHA token via the Cloudflare API.
     *
     * Inline validator for the `turnstileToken` attribute.
     *
     * @param string $attribute Attribute currently being validated.
     * @param mixed $params Validator parameters configured in {@see rules()}.
     */
    public function validateTurnstile(string $attribute, mixed $params): void
    {
        if ($this->hasErrors()) {
            return;
        }

        $secretKey = Yii::$app->params['turnstile.secretKey'];

        if ($secretKey === '') {
            if (YII_ENV_TEST) {
                return;
            }

            // @codeCoverageIgnoreStart
            Yii::error(
                'Turnstile secret key is not configured.',
                __METHOD__,
            );

            $this->addError($attribute, 'CAPTCHA verification is temporarily unavailable.');

            return;
            // @codeCoverageIgnoreEnd
        }

        $response = $this->fetchTurnstileResponse($secretKey);

        if ($response === null) {
            $this->addError($attribute, 'CAPTCHA verification failed. Please try again.');

            return;
        }

        try {
            /** @var array<string, mixed> $result */
            $result = Json::decode($response);
        } catch (InvalidArgumentException) {
            Yii::warning(
                'Turnstile response is not valid JSON.',
                __METHOD__,
            );

            $this->addError($attribute, 'CAPTCHA verification failed. Please try again.');

            return;
        }

        if (($result['success'] ?? false) !== true) {
            $this->addError($attribute, 'CAPTCHA verification failed. Please try again.');
        }
    }

    /**
     * Sends the token to the Cloudflare Turnstile API for verification.
     *
     * @param string $secretKey Cloudflare Turnstile secret key used to authenticate the request.
     *
     * @return string|null API response body, or `null` on failure.
     */
    protected function fetchTurnstileResponse(string $secretKey): string|null
    {
        try {
            $response = file_get_contents(
                'https://challenges.cloudflare.com/turnstile/v0/siteverify',
                false,
                stream_context_create([
                    'http' => [
                        'method' => 'POST',
                        'header' => 'Content-Type: application/x-www-form-urlencoded',
                        'timeout' => 5,
                        'content' => http_build_query([
                            'secret' => $secretKey,
                            'response' => $this->turnstileToken,
                        ]),
                    ],
                ]),
            );
        } catch (Throwable $exception) { // @codeCoverageIgnoreStart
            Yii::warning(
                sprintf('Turnstile verification request failed: %s', $exception->getMessage()),
                __METHOD__,
            );

            return null;
        } // @codeCoverageIgnoreEnd

        if ($response === false) { // @codeCoverageIgnoreStart
            Yii::warning(
                'Turnstile verification request failed.',
                __METHOD__,
            );

            return null;
        } // @codeCoverageIgnoreEnd

        return $response;
    }
}
