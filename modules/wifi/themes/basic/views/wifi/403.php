<?php
	use yii\helpers\Html;
	use yii\helpers\Url;
    use app\modules\wifiservice\themes\basic\assets\ThemeAsset;
    ThemeAsset::register($this);
    $baseUrl = $this->assetBundles[ThemeAsset::className()]->baseUrl . '/';

    $this->title = "403";
?>
<body>
	<div id="buyPackage" class="bodyBox">
		<!-- header start -->
		<header id="mainHeader">
			<div class="clearfix">
				<span class="l">欢迎您</span>
			</div>
			<div class="surplus">
				<span>剩余流量：</span>
				<span><em>0</em>.00M</span>
			</div>
		</header>
		<!-- header end -->
		<!-- content start -->
		<div class="tabContent">
			<div class="iconBox">
				<h2 class="error">访问被拒绝</h2>
				<p>很抱歉，您所访问的页面只能在船上使用</p>
			</div>
		</div>
	<!-- content end -->
</body>
