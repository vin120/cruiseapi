<?php 
	use yii\helpers\Html;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html>
<head>
    <title><?= Html::encode($this->title) ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,height=device-height,inital-scale=1.0,maximum-scale=1.0,user-scalable=no;" />
    <?php $this->head() ?>
</head>
<?php $this->beginBody() ?>
<?= $content ?>
<?php $this->endBody() ?>
</html>
<?php $this->endPage() ?>
 