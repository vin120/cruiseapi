<?php
use app\modules\wifiservice\themes\basic\myasset\LoginAsset;
use yii\helpers\Url;
LoginAsset::register($this);

$this->title = '验证失败';
$this->params['breadcrumbs'][] = $this->title;
$baseUrl = $this->assetBundles[LoginAsset::className()]->baseUrl . '/';
?>
<div id="password" class="bodyBox">
	<div class="iconBox">
		<span class="icon"><img src="<?= $baseUrl ?>/images/wrong.png"></span>
		<h1>验证失败</h1>
		<p>护照号或手机号码错误</p>
	</div>
	<div class="btnBox">
		<a href="<?php echo Url::toRoute(['/wifiservice/wifi/forgetpassword'])?>"><input type="button" value="返回"></input></a>
	</div>
</div>