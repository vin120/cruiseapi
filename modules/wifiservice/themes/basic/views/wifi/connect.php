<?php
	use yii\helpers\Html;
	use yii\helpers\Url;
    use app\modules\wifiservice\themes\basic\assets\ThemeAsset;
    ThemeAsset::register($this);
    $baseUrl = $this->assetBundles[ThemeAsset::className()]->baseUrl . '/';

    $this->title = "网络连接";
?>

<body id="selectPackage">
	<!-- header start -->
	<header id="mainHeader">
		<ul class="tabTitle">
			<li><a href="<?php echo Url::toRoute(['wifi/index'])?>?mcode=<?php echo $mcode?>">上网购买</a></li>
			<li class="active"><a href="<?php echo Url::toRoute(['wifi/loginstatus'])?>?mcode=<?php echo $mcode?>">上网连接</a></li>
		</ul>
	</header>
	<!-- header end -->
	<!-- content start -->
	<div class="tabContent">
		<div class="iconBox">
			<h2>连接记录</h2>
			<?php foreach($log as $row):?>
			<p>连接时间：<?php echo $row['wifi_login_time']?></p>
			<p>断开时间：<?php echo $row['wifi_logout_time']?></p>
			<?php endforeach;?>
		</div>
		<div class="btnBox">
			<input type="button" id="button" value="立即联网"></input>
		</div>
	</div>
	<!-- content end -->
</body>

<script type="text/javascript">
window.onload = function(){
	var mcode = '<?php echo $mcode;?>';
	var csrfToken = '<?php echo Yii::$app->request->csrfToken?>';
	var status = '<?php echo $status;?>';
	if( status=='0'){
		location.href ="<?php echo Url::toRoute(['wifi/disconnect']);?>?mcode=<?php echo $mcode;?>";
	}
	
	$("#button").on("click",function(){
		$.ajax({
			url: "<?php echo Url::toRoute(['service/wificonnect']);?>",
	        data: {mcode:mcode,_csrf:csrfToken},
	        type: 'post',
	        dataType: 'json',
	        success : function(response) {
	        	if(response['success'] == true){
	        		location.href ="<?php echo Url::toRoute(['wifi/disconnect']);?>?mcode=<?php echo $mcode?>";
	        	}else{
					alert(response['Info']);
	        	}
	        },
	        error: function(XMLHttpRequest, textStatus, errorThrown) {
				console.log("error");
	        }
		});
	});
	
}
</script>