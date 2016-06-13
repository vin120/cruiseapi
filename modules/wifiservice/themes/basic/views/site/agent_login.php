<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\modules\wifiservice\themes\basic\myasset\LoginAsset;
use yii\helpers\Url;

LoginAsset::register($this);

$this->title = 'wifi认证';
$this->params['breadcrumbs'][] = $this->title;

$baseUrl = $this->assetBundles[LoginAsset::className()]->baseUrl . '/';


// print_r($model->getFirstError('password'));


//$curr_language = Yii::$app->language;
?>

	<div class="bodyBox">
		<div id="welcome" class="tc">
			<span class="imgBox"><img src="<?= $baseUrl ?>/images/logo.png"></span>
			<span>中华泰山号邮轮欢迎您！</span>
		</div>
		<div id="download" class="tc">
			<p>为了更好体验泰山号邮轮上网服务，</p>
			<p>请下载邮轮通APP进行上网。</p>
			<div>
				<span class="imgBox"><img src="<?= $baseUrl ?>/images/icon.png"></span>
				<button style="cursor:pointer" onclick="ios_download()"><img src="<?= $baseUrl ?>/images/ios.png"></button>
				<button style="cursor:pointer" onclick="android_download()"><img src="<?= $baseUrl ?>/images/android.png"></button>
			</div>
		</div>
		<div id="loginBox">
			<ul class="tabTitle tc">
				<li class="active">护照号登录</li>
				<li>上网卡登录</li>
			</ul>
			<div class="tabContent">
				<div class="active">
				 	<?php $form = ActiveForm::begin(['action' => ['/wifiservice/site/login'],'method'=>'post','id' => 'passport-form']); ?>
					<input id="loginform-username" type="text" placeholder="护照号" 
					autofocus="autofocus" oninput="setCustomValidity('')" oninvalid="setCustomValidity('护照号不能为空')" 
					required="required"  name="LoginForm[username]">
					<input id="loginform-password"  type="password" placeholder="密码" 
					oninput="setCustomValidity('')" oninvalid="setCustomValidity('密码不能为空')" 
					required="required"  name="LoginForm[password]">
					
					<div id="passwordthis">
					</div>
                    <?php ActiveForm::end(); ?>
					<input type="button" id="passport_login" value="登录" style="cursor:pointer">
				</div>
				<div>
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
		
	var errorMessage = \''.$model->getFirstError('password') .'\';
	var ios_address = \''.Yii::$app->params['ios_address'].'\';
	var android_address = \''.Yii::$app->params['android_address'].'\';
			
			
	function ios_download()
	{
		window.open(ios_address);
	}
	
	function android_download()
	{
		window.open(android_address);
	}
		
		
	window.onload=function(){
	
	if(errorMessage != \'\'){
		$("#passwordthis").append("<strong class=\'\' style=\'color:red;\'>护照号或者密码有误</strong>");
	}

	
	$("#passport_login").on("click",function(){
		$("#passport-form").submit();
	});


	var url = \''.Url::toRoute(['/wificard/wifi/login']).'\';
	$("#card_login").on("click",function(){
		var card = $("#card").val();
		
		$.ajax({
            cache: true,
            type: "POST",
            url:url,
            dataType: \'json\',
            data:$(\'#card-form\').serialize(),// 你的formid
            async: false,
            error: function(XMLHttpRequest, textStatus, errorThrown) {
             	$("#carderror").html("<strong class=\'\' style=\'color:red;\'>出现错误</strong>");
            },
            success: function(response) {

				if(response.data){
					location.href ="'.Url::toRoute(['/wificard/wifi/index']).'?card="+card;
				}else if(response.error["errorCode"] ==1 ){
					$("#carderror").html("<strong class=\'\' style=\'color:red;\'>卡号或者密码有误</strong>");
				}else if (response.error["errorCode"] ==2){
					$("#carderror").html("<strong class=\'\' style=\'color:red;\'>卡号登录出现错误</strong>");
				}
           }
        });
	});
}	
		

', \yii\web\View::POS_END);

?>


