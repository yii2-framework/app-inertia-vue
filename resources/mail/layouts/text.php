<?php

declare(strict_types=1);

/**
 * @var string $content Main view render result.
 * @var \yii\mail\MessageInterface $message Message being composed.
 * @var \yii\web\View $this View component instance.
 */
?>
<?php $this->beginPage() ?>
<?php $this->beginBody() ?>
<?= $content ?>
<?php $this->endBody() ?>
<?php $this->endPage() ?>
