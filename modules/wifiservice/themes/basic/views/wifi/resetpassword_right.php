<?php
use app\modules\wifiservice\themes\basic\myasset\LoginAsset;
use yii\helpers\Url;
LoginAsset::register($this);

$this->title = '修改成功';
$this->params['breadcrumbs'][] = $this->title;
$baseUrl = $this->assetBundles[LoginAsset::className()]->baseUrl . '/';

?>
<div id="password" class="bodyBox">
	<div class="iconBox">
		<span class="icon"><img src="<?= $baseUrl ?>/images/right.png"></span>
		<h1>修改成功</h1>
		<p>新密码：<?php echo $password?></p>
	</div>
	<div class="btnBox">
		<a href="<?php echo Url::toRoute(['/wifiservice/wifi/index'])?>"><input type="button" value="确认"></input></a>
	</div>
</div>