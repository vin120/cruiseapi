<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\modules\wifiservice\themes\basic\myasset\LoginAsset;
use yii\helpers\Url;

LoginAsset::register($this);

$this->title = 'wifi认证';
$this->params['breadcrumbs'][] = $this->title;

$baseUrl = $this->assetBundles[LoginAsset::className()]->baseUrl . '/';

?>
<body>
	<div class="bodyBox">
		<div id="welcome" class="tc">
			<span class="imgBox"><img src="<?= $baseUrl ?>/images/logo.png"></span>
			<span>中华泰山号邮轮欢迎您！</span>
		</div>
		<div id="download" class="tc">
			<p>当前连接的DNS网络出现异常</p>
			<p>如未恢复,请多次刷新此页面</p>
		</div>
		<div id="loginBox" style="background-color:rgb(240, 240, 240);">
			<input type="button" id="reflase" value="刷新" style="cursor:pointer">
		</div>
	</div>
</body>

<script type="text/javascript">
window.onload=function(){
	
	$("#reflase").on("click",function(){
		location.href ="<?php echo Url::toRoute(['/wifiservice/site/login']);?>";
	});
}
</script>
