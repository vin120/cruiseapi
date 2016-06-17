<?php
	use yii\helpers\Html;
	use yii\helpers\Url;
    use app\modules\wifiservice\themes\basic\assets\ThemeAsset;
    ThemeAsset::register($this);
    $baseUrl = $this->assetBundles[ThemeAsset::className()]->baseUrl . '/';

    $this->title = "购买套餐";
?>
<body>
	<div id="buyPackage" class="bodyBox">
		<!-- header start -->
		<header id="mainHeader">
			<div class="clearfix">
				<span class="l">欢迎您，<?php echo $membership['cn_name']?>  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="<?php echo Url::toRoute(['wifi/changepassword']);?>"><u>修改密码</u></a></span>
				<a href="<?php echo Url::toRoute(['site/logout'])?>" class="r">退出</a>
			</div>
			<div class="surplus">
				<span>剩余流量：</span>
				<span><em><?php echo $flow_info[0]?></em>.<?php echo $flow_info[1]?>M</span>
			</div>
			<ul>
				<li class="active"><a href="<?php echo Url::toRoute(['wifi/index'])?>">上网购买</a></li>
				<li><a href="<?php echo Url::toRoute(['wifi/loginstatus'])?>">上网连接</a></li>
			</ul>
		</header>
		<!-- header end -->
		<!-- content start -->
		<div class="tabContent">
			<div>
				<ul id="packageList">
				<?php foreach($wifi_items as $key => $wifi_item):?>
					<li><input type="radio" id="wifi_id"  name="wifi_id" value="<?php echo $wifi_item['wifi_id']?>"  <?php if($key == 0){?>  checked="checked" <?php }?>></input><?php echo $wifi_item['wifi_name']?><em class="em">（$<?php echo $wifi_item['sale_price']?>）</em></li>
				<?php endforeach;?>
				</ul>
				<div class="btnBox">
					<input type="button" id="button" value="购买选择的套餐"></input>
				</div>
			</div>
			<div id="history">
				<h3>购买记录</h3>
				<table>
					<thead>
						<tr>
							<th>套餐名</th>
							<th>价格</th>
							<th>时间</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach($pay_log as $row ):?>
						<tr>
							<td><?php echo $row['name'];?></td>
							<td>$<?php echo $row['price'];?></td>
							<td><?php echo $row['pay_time']?></td>
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
	
	$("#button").on("click",function(){
		
		var wifi_id = $("input[name='wifi_id']:checked").val();
		location.href ="<?php  echo Url::toRoute(['wifi/orderconfirm']);?>?wifi_id="+wifi_id;
		
	});

}
</script>
