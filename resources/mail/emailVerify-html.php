<?php

declare(strict_types=1);

use yii\helpers\Html;

/**
 * @var \app\models\User $user User instance.
 * @var \yii\web\View $this View component instance.
 */
$verifyLink = Yii::$app->urlManager->createAbsoluteUrl(['user/verify-email', 'token' => $user->verification_token]);
?>
<div class="verify-email">
    <p>Hello <?= Html::encode($user->username) ?>,</p>

    <p>Follow the link below to verify your email:</p>

    <p><?= Html::a(Html::encode($verifyLink), $verifyLink) ?></p>
</div>
