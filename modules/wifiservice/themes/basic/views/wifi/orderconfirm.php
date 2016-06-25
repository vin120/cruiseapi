<?php
	use yii\helpers\Html;
	use yii\helpers\Url;
    use app\modules\wifiservice\themes\basic\assets\ThemeAsset;
    ThemeAsset::register($this);
    $baseUrl = $this->assetBundles[ThemeAsset::className()]->baseUrl . '/';

    $this->title = "支付确认";
?>
<body>
	<div id="buyPackage" class="bodyBox">
		<!-- header start -->
		<header id="mainHeader">
			<div class="clearfix">
				<span class="l">欢迎您，<?php echo $membership['cn_name']?></span>
				<a href="<?php echo Url::toRoute(['site/loginstatus'])?>" class="r">退出</a>
			</div>
			<div class="surplus">
				<span>剩余流量：</span>
				<span><em><?php echo $flow_info[0]?></em>.<?php echo $flow_info[1]?>M</span>
			</div>
			<ul>
				<li class="active"><a href="<?php echo Url::toRoute(['wifi/index'])?>">上网购买</a></li>
				<li><a href="<?php echo Url::toRoute(['wifi/connect'])?>">上网连接</a></li>
			</ul>
		</header>
		<!-- header end -->
		<!-- content start -->
		<div class="tabContent">
			<h2 class="title pBox">Wifi订单确认</h2>
			<div id="orderInfo" class="pBox">
				<p>商品名称：<?php echo $wifi_item['wifi_name'];?></p>
				<p>订单金额：<em class="em">￥<?php echo $wifi_item['sale_price'];?></em></p>
			</div>
			<div class="point pBox">
				<p>1.购买前请确认您的房卡中余额充足。</p>
				<p>2.支付成功后，系统将自动从您的房卡中扣除对应的余额。</p>
			</div>
			<div class="btnBox">
				<input type="button" id="button" value="立即支付"></input>
			</div>
		</div>
		<!-- content end -->
		</div>
</body>
<script type="text/javascript">
window.onload = function(){
$("#button").on("click",function(){
		
		var wifi_id = '<?php echo $wifi_item['wifi_id'];?>';
		var csrfToken = '<?php  echo Yii::$app->request->csrfToken?>';

		$.ajax({
			url: "<?php  echo Url::toRoute(['wifi/wifipayment']);?>",
	        data: {wifi_id:wifi_id,_csrf:csrfToken},
	        type: 'post',
	        dataType: 'json',
	        success :successFunc,
	        error: errorFunc,
	    });
	    
		function successFunc(response) {
			if(response.data){
            	//显示上网连接页面
            	location.href ="<?php echo Url::toRoute(['wifi/paymentsuccess']);?>";
            }else if(response.error){
                if(response.error.errorCode == 1){
                    //钱不够
                	//显示支付失败界面
                	location.href="<?php echo Url::toRoute(['wifi/paymentfail']);?>";
                }else if(response.error.errorCode == 2){
                    //出现错误
                    //显示支付出错界面
                	location.href="<?php echo Url::toRoute(['wifi/paymenterror']);?>";
                }
            }else {
            	//显示出错页面
            	location.href="<?php echo Url::toRoute(['wifi/paymenterror']);?>";
            }
		}

        function errorFunc(XMLHttpRequest, textStatus, errorThrown) {
        	console.log("error");
        	//显示出错页面
        	location.href="<?php echo Url::toRoute(['wifi/paymenterror']);?>";
        }
	});
	
}
</script>