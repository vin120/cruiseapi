<?php
	use yii\helpers\Html;
	use yii\helpers\Url;
    use app\modules\wifiservice\themes\basic\assets\ThemeAsset;
    ThemeAsset::register($this);
    $baseUrl = $this->assetBundles[ThemeAsset::className()]->baseUrl . '/';

    $this->title = "网络连接";
?>

<body>
	<div id="internetAccess" class="bodyBox">
		<!-- header start -->
		<header id="mainHeader">
			<div class="clearfix">
				<span class="l">欢迎您，张三</span>
				<a href="#" class="r">退出</a>
			</div>
			<div class="surplus">
				<span>剩余流量：</span>
				<span><em>123</em>.26M</span>
			</div>
			<ul>
				<li><a href="<?php echo Url::toRoute(['wifi/index'])?>">上网购买</a></li>
				<li class="active"><a href="<?php echo Url::toRoute(['wifi/loginstatus'])?>">上网连接</a></li>
			</ul>
		</header>
		<!-- content start -->
		<div class="tabContent">
			<div class="btnBox">
				<input type="button" value="立即联网"></input>
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
						<tr>
							<td>2016/01/23 12:20:20</td>
							<td>2016/01/23 12:20:20</td>
							<td>100.65M</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<!-- content end -->
	</div>
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