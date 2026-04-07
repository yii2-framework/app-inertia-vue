<?php

declare(strict_types=1);

/**
 * @var \app\models\User $user User instance.
 * @var \yii\web\View $this View component instance.
 */
$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['user/reset-password', 'token' => $user->password_reset_token]);
?>
Hello <?= $user->username ?>,

Follow the link below to reset your password:

<?= $resetLink ?>
