<?php
use app\modules\wifiservice\themes\basic\myasset\LoginAsset;
use yii\helpers\Url;
LoginAsset::register($this);

$this->title = '验证成功';
$this->params['breadcrumbs'][] = $this->title;
$baseUrl = $this->assetBundles[LoginAsset::className()]->baseUrl . '/';
?>
<div id="password" class="bodyBox">
	<div class="iconBox">
		<span class="icon"><img src="<?= $baseUrl ?>/images/right.png"></span>
		<h1>验证成功</h1>
		<p>新密码：<?php echo $new_password?></p>
	</div>
	<div class="btnBox">
		<a href="<?php echo Url::toRoute(['/wifiservice/site/login'])?>"><input type="button" value="立即登录"></input></a>
	</div>
</div>