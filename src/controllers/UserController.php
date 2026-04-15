<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\{
    LoginForm,
    PasswordResetRequestForm,
    ResendVerificationEmailForm,
    ResetPasswordForm,
    SignupForm,
    User,
    UserSearch,
    VerifyEmailForm,
};
use Throwable;
use Yii;
use yii\base\InvalidArgumentException;
use yii\filters\{AccessControl, VerbFilter};
use yii\inertia\web\Controller;
use yii\mail\MailerInterface;
use yii\web\{BadRequestHttpException, Response};

/**
 * Handles user-related actions: login, logout, signup, password recovery, email verification, and user listing.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 0.1
 */
final class UserController extends Controller
{
    public function __construct($id, $module, private readonly MailerInterface $mailer, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    /**
     * Displays user list.
     *
     * @return Response Response object containing the rendered result of the action.
     */
    public function actionIndex(): Response
    {
        $searchModel = new UserSearch();

        /** @var array<string, mixed> $queryParams */
        $queryParams = Yii::$app->request->queryParams;

        $dataProvider = $searchModel->search($queryParams);

        /** @var User[] $models */
        $models = $dataProvider->getModels();

        $users = array_map(
            static fn(User $model): array => [
                'id' => $model->id,
                'username' => $model->username,
                'email' => $model->email,
                'status' => $model->status,
                'created_at' => $model->created_at,
            ],
            $models,
        );

        $pagination = $dataProvider->getPagination();
        $sort = $dataProvider->getSort();

        return $this->inertia(
            'User/Index',
            [
                'filters' => [
                    'username' => $searchModel->username,
                    'email' => $searchModel->email,
                    'status' => $searchModel->status,
                ],
                'pagination' => [
                    'totalCount' => $dataProvider->getTotalCount(),
                    'pageSize' => $pagination !== false ? $pagination->getPageSize() : 10,
                    'currentPage' => $pagination !== false ? $pagination->getPage() + 1 : 1,
                    'pageCount' => $pagination !== false ? $pagination->getPageCount() : 1,
                ],
                'sort' => [
                    'attributes' => $sort instanceof \yii\data\Sort ? $sort->getAttributeOrders() : [],
                ],
                'users' => $users,
            ],
        );
    }

    /**
     * Login action.
     *
     * @return Response Response object containing the rendered result of the action.
     */
    public function actionLogin(): Response
    {
        $model = new LoginForm();

        /** @var array<string, mixed> $post */
        $post = $this->request->post();

        if ($model->load($post) && $model->login()) {
            return $this->goHome();
        }

        if ($this->request->isPost && $model->hasErrors()) {
            Yii::$app->session->setFlash('errors', $model->getErrors());

            return $this->redirect(['user/login']);
        }

        return $this->inertia('User/Login');
    }

    /**
     * Logout action.
     *
     * @return Response Response object containing the rendered result of the action.
     */
    public function actionLogout(): Response
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Requests password reset.
     *
     * @return Response Response object containing the rendered result of the action.
     */
    public function actionRequestPasswordReset(): Response
    {
        $model = new PasswordResetRequestForm();

        /** @var array<string, mixed> $post */
        $post = $this->request->post();

        $params = Yii::$app->params;

        if ($model->load($post) && $model->validate()) {
            $sent = $model->sendEmail(
                $this->mailer,
                $params['supportEmail'],
                Yii::$app->name,
            );

            if ($sent) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            }

            Yii::$app->session->setFlash(
                'error',
                'Sorry, we are unable to reset password for the provided email address.',
            );

            return $this->redirect(['user/request-password-reset']);
        }

        if ($this->request->isPost && $model->hasErrors()) {
            Yii::$app->session->setFlash('errors', $model->getErrors());

            return $this->redirect(['user/request-password-reset']);
        }

        return $this->inertia('User/RequestPasswordReset');
    }

    /**
     * Resends verification email.
     *
     * @return Response Response object containing the rendered result of the action.
     */
    public function actionResendVerificationEmail(): Response
    {
        $model = new ResendVerificationEmailForm();

        /** @var array<string, mixed> $post */
        $post = $this->request->post();

        $params = Yii::$app->params;

        if ($model->load($post) && $model->validate()) {
            $sent = $model->sendEmail(
                $this->mailer,
                $params['supportEmail'],
                Yii::$app->name,
            );

            if ($sent) {
                Yii::$app->session->setFlash(
                    'success',
                    'Check your email for further instructions.',
                );

                return $this->goHome();
            }

            Yii::$app->session->setFlash(
                'error',
                'Sorry, we are unable to resend verification email for the provided email address.',
            );

            return $this->redirect(['user/resend-verification-email']);
        }

        if ($this->request->isPost && $model->hasErrors()) {
            Yii::$app->session->setFlash('errors', $model->getErrors());

            return $this->redirect(['user/resend-verification-email']);
        }

        return $this->inertia('User/ResendVerificationEmail');
    }

    /**
     * Resets password.
     *
     * @throws BadRequestHttpException if the token is invalid.
     *
     * @return Response Response object containing the rendered result of the action.
     */
    public function actionResetPassword(string $token): Response
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        /** @var array<string, mixed> $post */
        $post = $this->request->post();

        if ($model->load($post)) {
            try {
                $saved = $model->validate() && $model->resetPassword();
            } catch (Throwable $e) {
                Yii::error($e->getMessage(), __METHOD__);
                $saved = false;
            }

            if ($saved) {
                Yii::$app->session->setFlash(
                    'success',
                    'New password saved.',
                );

                return $this->goHome();
            }

            if ($model->hasErrors()) {
                Yii::$app->session->setFlash('errors', $model->getErrors());
            } else {
                Yii::$app->session->setFlash(
                    'error',
                    'Sorry, we are unable to save your new password at this time.',
                );
            }

            return $this->redirect(['user/reset-password', 'token' => $token]);
        }

        return $this->inertia('User/ResetPassword', ['token' => $token]);
    }

    /**
     * Signs user up.
     *
     * @return Response Response object containing the rendered result of the action.
     */
    public function actionSignup(): Response
    {
        $model = new SignupForm();

        /** @var array<string, mixed> $post */
        $post = $this->request->post();

        if ($model->load($post)) {
            $params = Yii::$app->params;

            $signed = $model->signup(
                $this->mailer,
                $params['supportEmail'],
                Yii::$app->name,
            );

            if ($signed === true) {
                Yii::$app->session->setFlash(
                    'success',
                    'Thank you for registration. Please check your inbox for verification email.',
                );

                return $this->goHome();
            }

            if ($model->hasErrors()) {
                Yii::$app->session->setFlash('errors', $model->getErrors());
            } else {
                Yii::$app->session->setFlash(
                    'error',
                    'Sorry, we are unable to complete your registration at this time.',
                );
            }

            return $this->redirect(['user/signup']);
        }

        return $this->inertia('User/Signup');
    }

    /**
     * Verifies email address.
     *
     * @throws BadRequestHttpException if the token is invalid.
     *
     * @return Response Response object containing the rendered result of the action.
     */
    public function actionVerifyEmail(string $token): Response
    {
        try {
            $model = new VerifyEmailForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->verifyEmail() !== null) {
            Yii::$app->session->setFlash(
                'success',
                'Your email has been confirmed!',
            );

            return $this->goHome();
        }

        Yii::$app->session->setFlash(
            'error',
            'Sorry, we are unable to verify your account with provided token.',
        );

        return $this->goHome();
    }

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => [
                    'index',
                    'login',
                    'logout',
                    'request-password-reset',
                    'resend-verification-email',
                    'reset-password',
                    'signup',
                    'verify-email',
                ],
                'rules' => [
                    [
                        'actions' => [
                            'login',
                            'request-password-reset',
                            'resend-verification-email',
                            'reset-password',
                            'signup',
                            'verify-email',
                        ],
                        'allow' => true,
                        'roles' => [
                            '?',
                        ],
                    ],
                    [
                        'actions' => [
                            'index',
                        ],
                        'allow' => true,
                        'roles' => [
                            'admin',
                        ],
                    ],
                    [
                        'actions' => [
                            'logout',
                        ],
                        'allow' => true,
                        'roles' => [
                            '@',
                        ],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => [
                        'get',
                    ],
                    'logout' => [
                        'post',
                    ],
                ],
            ],
        ];
    }
}
