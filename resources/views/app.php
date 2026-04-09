<?php

declare(strict_types=1);

use yii\helpers\Html;
use yii\inertia\Page;
use yii\inertia\vue\Vite;
use yii\web\View;

/**
 * @var string $id Unique ID for the root element.
 * @var Page $page Page data.
 * @var string $pageJson JSON-encoded page data.
 * @var View $this View component instance.
 * @var Vite $vite Vite asset manager instance.
 */
$vite = Yii::$app->inertiaVue;

$this->beginPage();
?>
<!DOCTYPE html>
<html lang="<?= Html::encode(Yii::$app->language) ?>" class="h-full">
<head>
    <meta charset="<?= Html::encode(Yii::$app->charset) ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title data-inertia><?= Html::encode(Yii::$app->name) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Inter:wght@300..800&display=swap" rel="stylesheet">
    <script>
    (function(){
        var t = localStorage.getItem('theme');
        if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    })();
    </script>
    <?= Html::csrfMetaTags() ?>
    <?php $this->head(); ?>
    <?= $vite->renderTags() ?>
</head>
<body class="flex flex-col h-full bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
<?php $this->beginBody(); ?>
<div id="<?= Html::encode($id) ?>">
    <script type="application/json"><?= $pageJson ?></script>
</div>
<?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage(); ?>
