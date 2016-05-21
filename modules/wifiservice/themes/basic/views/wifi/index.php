<?php
	use yii\helpers\Html;
	use yii\helpers\Url;
    use app\modules\wifiservice\themes\basic\assets\ThemeAsset;
    ThemeAsset::register($this);
    $baseUrl = $this->assetBundles[ThemeAsset::className()]->baseUrl . '/';

    $this->title = "套餐选择";
?>
<body id="buyPackage">
	<!-- header start -->
	<header id="mainHeader">
		<ul>
			<li class="active"><a href="<?php echo Url::toRoute(['wifi/index'])?>?mcode=<?php echo $mcode?>">上网购买</a></li>
			<li><a href="<?php echo Url::toRoute(['wifi/loginstatus'])?>?mcode=<?php echo $mcode?>">上网连接</a></li>
		</ul>
	</header>
	<!-- header end -->
	<!-- content start -->
	<div class="tabContent">
		<div id="welcome" class="pBox">
			<p>尊敬的旅客(<?php echo $membership['cn_name'];?>)，您好</p>
			<p>欢迎WiFi套餐。</p>
		</div>
		<ul id="packageList">
		<?php foreach($wifi_items as $key => $wifi_item):?>
			<li><input type="radio" id="wifi_id"  name="wifi_id" value="<?php echo $wifi_item['wifi_id']?>"  <?php if($key == 0){?>  checked="checked" <?php }?>></input><?php echo $wifi_item['wifi_name']?><em class="em">（$<?php echo $wifi_item['wifi_flow']?>）</em></li>
		<?php endforeach;?>
		</ul>
		<div class="btnBox">
			<input type="button" id="button" value="购买选择的套餐"></input>
		</div>
	</div>
	<!-- content end -->
</body>
<script type="text/javascript">
window.onload = function(){ 

	$("#button").on("click",function(){
		var mcode = '<?php echo $mcode?>';
		var wifi_id = $("input[name='wifi_id']:checked").val();
		var csrfToken = '<?php echo Yii::$app->request->csrfToken?>';

		$.ajax({
			url: "<?php echo Url::toRoute(['wifi/wifipayment']);?>",
	        data: {mcode:mcode,wifi_id:wifi_id,_csrf:csrfToken},
	        type: 'post',
	        dataType: 'json',
	        success :successFunc,
	        error: errorFunc,
	    });

		function successFunc(response) {
			if(response.data){
            	//显示购买页面
            	location.href ="<?php echo Url::toRoute(['wifi/loginstatus']);?>?mcode=<?php echo $mcode;?>";
            }else{
            	//显示出错页面
            	location.href="<?php echo Url::toRoute(['wifi/payerror']);?>?mcode=<?php echo $mcode?>";
            }
		}

        function errorFunc(XMLHttpRequest, textStatus, errorThrown) {
        	console.log("error");
        }

		
	});

}
</script>
