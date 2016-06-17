<?php
use app\modules\wifiservice\themes\basic\myasset\LoginAsset;
use yii\helpers\Url;
LoginAsset::register($this);

$this->title = '修改密码';
$this->params['breadcrumbs'][] = $this->title;
$baseUrl = $this->assetBundles[LoginAsset::className()]->baseUrl . '/';

?>
<div id="password" class="bodyBox">
	<h1>修改密码</h1>
	<div class="formBox box">
		<div class="form">
		<form id="changepassword-form" name="changepassword-form" action="<?php echo Url::toRoute(['/wifiservice/wifi/changepasswordvalidate'])?>" method="post" >
			<input type="hidden" value="<?php echo Yii::$app->request->csrfToken?>" name="_csrf">
			<input type="text" id="password" name="password" placeholder="输入新密码">
			<input type="text" id="password_again" name="password_again" placeholder="确认新密码">
			<div class="btnBox">
				<input type="submit" name="" value="确认修改密码" class="btn">
				<a href="<?php echo Url::toRoute(['/wifiservice/wifi/index'])?>"><input type="button" name="" value="返回" class="btn"></a>
			</div>
		</form>
		</div>
	</div>
</div>