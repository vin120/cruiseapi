<?php
	use yii\helpers\Html;
	use yii\helpers\Url;
    use app\modules\wifiservice\themes\basic\assets\ThemeAsset;
    ThemeAsset::register($this);
    $baseUrl = $this->assetBundles[ThemeAsset::className()]->baseUrl . '/';

    $this->title = "网络练接";
?>
<body id="currentPackage">
	<!-- header start -->
	<header id="mainHeader">
		<ul>
			<li><a href="<?php echo Url::toRoute(['wifi/index'])?>?mcode=<?php echo $mcode?>">上网购买</a></li>
			<li class="active"><a href="<?php echo Url::toRoute(['wifi/loginstatus'])?>?mcode=<?php echo $mcode?>">上网连接</a></li>
		</ul>
	</header>
	<!-- header end -->
	<!-- content start -->
	<div class="tabContent">
		<div class="packageInfo">
		</div>
		<div class="btnBox">
			<input type="button" id="button" value="断开连接"></input>
		</div>
	</div>
	<!-- content end -->
</body>

<script type="text/javascript">
window.onload = function(){
	var mcode = '<?php echo $mcode?>';
	var csrfToken = '<?php echo Yii::$app->request->csrfToken?>';
	$.ajax({
		url: "<?php echo Url::toRoute(['service/checkoutflow']);?>",
        data: {mcode:mcode,_csrf:csrfToken},
        type: 'post',
        dataType: 'json',
        success : function(response) {
        	if(response['success'] == true){
            	var str = "<p>流入量: "+response['data']['in_flow']+"</p>";
            	str += "<p>流出量: "+response['data']['out_flow']+"</p>";
            	str += "<p>总流量: "+response['data']['total_flow']+"</p>";
        		$(".packageInfo").html(str);
        	}
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
			console.log("error");
        }
	});

	$("#button").on("click",function(){
		$.ajax({
			url: "<?php echo Url::toRoute(['service/wifidisconnect']);?>",
	        data: {mcode:mcode,_csrf:csrfToken},
	        type: 'post',
	        dataType: 'json',
	        success : function(response) {
	        	if(response['success'] == true){
	        		location.href ="<?php echo Url::toRoute(['wifi/loginstatus']);?>?mcode=<?php echo $mcode?>";
	        	}else{
					alert(response['Info']);
		        }
	        },
	        error: function(XMLHttpRequest, textStatus, errorThrown) {
				console.log("error");
				alert("出现错误");
	        }
		});
	});
}
</script>