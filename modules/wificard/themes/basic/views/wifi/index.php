<?php
	use yii\helpers\Html;
	use yii\helpers\Url;
    use app\modules\wificard\themes\basic\assets\ThemeAsset;
    ThemeAsset::register($this);
    $baseUrl = $this->assetBundles[ThemeAsset::className()]->baseUrl . '/';

    $this->title = "购买套餐";
?>
	<div id="buyPackage" class="bodyBox">
		<!-- header start -->
		<header id="mainHeader">
			<div class="clearfix">
				<span class="l">欢迎您</span>
			</div>
			<div class="surplus">
				<span>剩余流量：</span>
				<span><em><?php echo $flow_info[0]?></em>.<?php echo $flow_info[1]?>M</span>
			</div>
		</header>
		<!-- header end -->
		<!-- content start -->
		<div class="tabContent">
			<div>
				<br><br>
				<div class="btnBox">
					<input type="button" id="button" value="断开连接"></input>
				</div>
			</div>
		</div>
		<!-- content end -->
	</div>
	
<?php

$this->registerJs('
	window.onload = function(){	
		var csrfToken = \''.Yii::$app->request->csrfToken.'\';
			var card = \''. Yii::$app->request->get('card').'\';
			
			$("#button").on("click",function(){
				$.ajax({
					url: "'.Url::toRoute(['/wificard/wifi/disconnect']).'",
			        data: {_csrf:csrfToken,card:card},
			        type: \'post\',
			        dataType: \'json\',
			        success : function(response) {
			        	if(response["success"] == true){
			        		location.href ="'.Url::toRoute(['/wifiservice/site/login']).'";
			        	}else{
			        		location.href ="'. Url::toRoute(['/wifiservice/site/login']).'";
			        	}
			        },
			        error: function(XMLHttpRequest, textStatus, errorThrown) {
			        	location.href ="'. Url::toRoute(['/wifiservice/site/login']).'";
			        }
				});
			});
		}
		
', \yii\web\View::POS_END);

?>
	
	
