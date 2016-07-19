<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\modules\wifiservice\themes\basic\myasset\LoginAsset;
use yii\helpers\Url;

LoginAsset::register($this);

$this->title = 'wifi认证';
$this->params['breadcrumbs'][] = $this->title;

$baseUrl = $this->assetBundles[LoginAsset::className()]->baseUrl . '/';

$page_active = isset($active) ? $active : 0;
?>

	<div class="bodyBox">
		<div id="welcome" class="tc">
			<span class="imgBox"><img src="<?= $baseUrl ?>/images/logo.png"></span>
			<span>中华泰山号邮轮欢迎您！</span>
		</div>
		<div id="download" class="tc">
			<p>为了更好体验泰山号邮轮服务,请下载邮轮通APP</p>
			<p>（邮轮通提供:自助上网、邮轮信息、旅行日程、</p>
			<p> 服务介绍、内部聊天、旅游宝典、电子商务等服务）</p>
			<div>
				<span class="imgBox"><img src="<?= $baseUrl ?>/images/icon.png"></span>
				<button style="cursor:pointer" onclick="android_download()"><img src="<?= $baseUrl ?>/images/android.png"></button>
				<button style="cursor:pointer" onclick="ios_download()"><img src="<?= $baseUrl ?>/images/ios.png"></button>
			</div>
		</div>
		<div id="loginBox" class="box">
			<ul class="tabTitle tc">
				<li<?= $page_active == 1 ? ' class="active"' : ''?> >护照号登录</li>
				<li<?= $page_active == 0 ? ' class="active"' : ''?> >上网卡登录</li>
			</ul>
			<div class="tabContent">
				<div<?= $page_active == 1 ? ' class="active"' : ''?>>
				 	<?php $form = ActiveForm::begin(['action' => ['/wifiservice/site/login'],'method'=>'post','id' => 'passport-form']); ?>
					<input id="loginform-username" type="text" placeholder="护照号" 
					autofocus="autofocus" oninput="setCustomValidity('')" oninvalid="setCustomValidity('护照号不能为空')" 
					required="required"  name="LoginForm[username]" value="<?php $cookies = Yii::$app->request->cookies; if ($cookies->has('username')){ echo $cookies['username']; }?>">
					<input id="loginform-password"  type="password" placeholder="密码" 
					oninput="setCustomValidity('')" oninvalid="setCustomValidity('密码不能为空')" 
					required="required"  name="LoginForm[password]" value="<?php $cookies = Yii::$app->request->cookies; if ($cookies->has('password')){ echo $cookies['password']; }?>">
					
					<div id="passwordthis">
					</div>
                    <?php ActiveForm::end(); ?>
                    <a href= "<?php echo Url::toRoute(['/wifiservice/wifi/forgetpassword'])?>" class="remember">忘记密码？</a>
					<input type="button" id="passport_login" value="登录" style="cursor:pointer">
				</div>
				<div<?= $page_active == 0 ? ' class="active"' : ''?>>
					<?php $form = ActiveForm::begin(['action' => ['/wificard/wifi/login'],'method'=>'post','id' => 'card-form']); ?>
					<input type="text" placeholder="上网卡号" required="required" name="card" id="card">
					<input type="password" placeholder="密码" required="required" name="password" id="password">
					<div id="carderror">
					</div>
					<?php ActiveForm::end(); ?> 
					<input type="button" id="card_login"  value="登录" style="cursor:pointer">
				</div>
			</div>
			<div class="link tc">
				<a href="http://tsapi.cruisetone.com/notice.html">《上网须知》</a>
			</div>
		</div>
	</div>

<?php

$this->registerJs('
	var ipErrorMessage = \''.$model->getFirstError('ip') .'\';
	var errorMessage = \''.$model->getFirstError('password') .'\';
	var ios_address = \''.Yii::$app->params['ios_address'].'\';
	var android_address = \''.Yii::$app->params['android_address'].'\';
	
	var response = \''. Yii::$app->request->get('response').'\';
			
	function ios_download()
	{
		window.open(ios_address);
	}
	
	function android_download()
	{
		window.open(android_address);
	}
		
		
	window.onload=function(){
	
	if(ipErrorMessage != \'\'){
		$("#passwordthis").append("<strong class=\'point\' style=\'color:red;\'>连接的IP有误</strong>");
	}
		
	if(errorMessage != \'\'){
		$("#passwordthis").append("<strong class=\'point\' style=\'color:red;\'>护照号或者密码有误</strong>");
	}
		
	if(response != \'\'){
		$("#carderror").append("<strong class=\'point\' style=\'color:red;\'>"+ response +"</strong>");
	}

	
	$("#passport_login").on("click",function(){
		$("#passport-form").submit();
	});

	$("#card_login").on("click",function(){
		$("#card-form").submit();
	});

}	
		

', \yii\web\View::POS_END);

?>


