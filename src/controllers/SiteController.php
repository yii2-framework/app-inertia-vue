<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\ContactForm;
use Throwable;
use Yii;
use yii\inertia\web\Controller;
use yii\mail\MailerInterface;
use yii\web\{HttpException, Response};

/**
 * Handles site pages: home, about, contact, and error actions.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 0.1
 */
final class SiteController extends Controller
{
    public function __construct($id, $module, private readonly MailerInterface $mailer, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    /**
     * Displays about page.
     *
     * @return Response Response object containing the rendered about page.
     */
    public function actionAbout(): Response
    {
        return $this->inertia(
            'Site/About',
        );
    }

    /**
     * Displays contact page.
     *
     * @return Response Response object containing the rendered contact page.
     */
    public function actionContact(): Response
    {
        $model = new ContactForm();

        /** @var array<string, mixed> $post */
        $post = $this->request->post();

        if ($model->load($post)) {
            $params = Yii::$app->params;

            try {
                $sent = $model->contact(
                    $this->mailer,
                    $params['adminEmail'],
                    $params['senderEmail'],
                    $params['senderName'],
                );
            } catch (Throwable $e) {
                Yii::error($e->getMessage(), __METHOD__);
                $sent = false;
            }

            if ($sent) {
                Yii::$app->session->setFlash(
                    'success',
                    'Thank you for contacting us. We will respond to you as soon as possible.',
                );

                return $this->redirect(['site/contact']);
            }

            if ($model->hasErrors()) {
                Yii::$app->session->setFlash('errors', $model->getErrors());
            } else {
                Yii::$app->session->setFlash(
                    'error',
                    'Sorry, we are unable to send your message at this time.',
                );
            }

            return $this->redirect(['site/contact']);
        }

        return $this->inertia(
            'Site/Contact',
        );
    }

    /**
     * Displays error page.
     *
     * @return Response Response object containing the rendered error page.
     */
    public function actionError(): Response
    {
        $exception = Yii::$app->errorHandler->exception;

        $statusCode = $exception instanceof HttpException ? $exception->statusCode : 500;
        $message = (YII_DEBUG && $exception instanceof Throwable)
            ? $exception->getMessage()
            : 'An internal server error occurred.';

        return $this->inertia(
            'Site/Error',
            [
                'status' => $statusCode,
                'message' => $message,
            ],
        );
    }

    /**
     * Displays homepage.
     *
     * @return Response Response object containing the rendered homepage.
     */
    public function actionIndex(): Response
    {
        return $this->inertia(
            'Site/Index',
        );
    }
}
