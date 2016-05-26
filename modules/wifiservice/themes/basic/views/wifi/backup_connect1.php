<?php
	use yii\helpers\Html;
	use yii\helpers\Url;
    use app\modules\wifiservice\themes\basic\assets\ThemeAsset;
    ThemeAsset::register($this);
    $baseUrl = $this->assetBundles[ThemeAsset::className()]->baseUrl . '/';

    $this->title = "购买套餐";
?>
<body>
	<div id="internetAccess" class="bodyBox">
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
				<li class="active"><a href="<?php echo Url::toRoute(['wifi/loginstatus'])?>">上网连接</a></li>
			</ul>
		</header>
		<!-- header end -->
		<!-- content start -->
		<div class="tabContent">
			<div class="btnBox">
				<input type="button" id="button" value="立即联网"></input>
			</div>
			<p class="point">
				说明：关闭或退出当前窗口，系统不会断开网络连接，只有点击“断开连接”，系统才会停止流量计费。
			</p>
			<div class="history">
				<h3>连接记录</h3>
				<table>
					<thead>
						<tr>
							<th>连接时间</th>
							<th>断开时间</th>
							<th>使用流量</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($log as $row):?>
						<tr>
							<td><?php echo $row['wifi_login_time']?></td>
							<td><?php echo $row['wifi_logout_time']== '' ? '---' : $row['wifi_logout_time']?></td>
							<td><?php echo $row['flow'] == '' ? '---' : $row['flow']?></td>
						</tr>
						<?php endforeach;?>
					</tbody>
				</table>
			</div>
		</div>
		<!-- content end -->
	</div>
</body>
<script type="text/javascript">
window.onload = function(){
	var status = '<?php echo $status?>';

	if(status == false){
		$(".btnBox").removeClass("disconnect").find("input").val("立即连接");
	}else{
		$(".btnBox").addClass("disconnect").find("input").val("断开连接");
	}
	
	$(".btnBox input").on("click",function(){
		if ($(this).parent().hasClass("disconnect")) {
			
			
			$(this).val("立即连接").parent().removeClass("disconnect");
		} else {

			
			$(this).val("断开连接").parent().addClass("disconnect");
		}
	});

	
}
</script>
