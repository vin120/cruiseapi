<?php
	use yii\helpers\Html;
	use yii\helpers\Url;
    use app\modules\wifiservice\themes\basic\assets\ThemeAsset;
    ThemeAsset::register($this);
    $baseUrl = $this->assetBundles[ThemeAsset::className()]->baseUrl . '/';

    $this->title = "断开出错";
?>
<body>
	<div id="buyPackage" class="bodyBox">
		<!-- header start -->
		<header id="mainHeader">
			<div class="clearfix">
				<span class="l">欢迎您，<?php echo $membership['cn_name']?></span>
				<a href="<?php echo Url::toRoute(['site/logout'])?>" class="r">退出</a>
			</div>
			<div class="surplus">
				<span>剩余流量：</span>
				<span><em><?php echo $flow_info[0]?></em>.<?php echo $flow_info[1]?>M</span>
			</div>
			<ul>
				<li><a href="<?php echo Url::toRoute(['wifi/index'])?>">上网购买</a></li>
				<li  class="active"><a href="<?php echo Url::toRoute(['wifi/loginstatus'])?>">上网连接</a></li>
			</ul>
		</header>
		<!-- header end -->
		<!-- content start -->
		<div class="tabContent">
			<div class="iconBox">
				<h2 class="error">断开出错</h2>
				<p>很抱歉，断开连接失败，</p>
				<p>请联系管理员</p>
			</div>
			<div class="btnBox">
				<input type="button" id="button" value="返回"></input>
			</div>
		</div>
		<!-- content end -->
	</div>
</body>

<script type="text/javascript">
window.onload = function(){
	
	$("#button").on("click",function(){
		location.href ="<?php echo Url::toRoute(['wifi/loginstatus']);?>";
	});
	
}
</script>
