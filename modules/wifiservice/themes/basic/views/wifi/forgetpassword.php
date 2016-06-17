<?php
use app\modules\wifiservice\themes\basic\myasset\LoginAsset;
use yii\helpers\Url;
LoginAsset::register($this);

$this->title = '忘记密码';
$this->params['breadcrumbs'][] = $this->title;
$baseUrl = $this->assetBundles[LoginAsset::className()]->baseUrl . '/';

?>
	<div id="password" class="bodyBox">
		<h1>修改密码</h1>
		<div class="formBox box">
			<div class="message">填写如下相关以验证</div>
			<div class="form">
				<form id="forgetpassword-form" name="forgetpassword-form" action="<?php echo Url::toRoute(['/wifiservice/wifi/forgetpasswordvalidate'])?>" method="post" >
					<input type="hidden" value="<?php echo Yii::$app->request->csrfToken?>" name="_csrf">
					<input type="text" name="passport_number" placeholder="护照号">
					<input type="text" name="mobile_number" placeholder="手机号">
					<div class="btnBox">
						<input type="submit" id="validate" value="验证" class="btn">
						<a href="<?php echo Url::toRoute(['/wifiservice/site/login'])?>"><input type="button" id="cancel"  value="取消" class="btn"></a>
					</div>
				</form>
			</div>
		</div>
	</div>
