<?php

declare(strict_types=1);

/**
 * @var \app\models\User $user User instance.
 * @var \yii\web\View $this View component instance.
 */
$verifyLink = Yii::$app->urlManager->createAbsoluteUrl(['user/verify-email', 'token' => $user->verification_token]);
?>
Hello <?= $user->username ?>,

Follow the link below to verify your email:

<?= $verifyLink ?>
