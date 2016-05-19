<?php 
	use yii\helpers\Html;
	use yii\helpers\Url;
?>
<!DOCTYPE html>
<html>
<head>
	<title>选择套餐</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,height=device-height,inital-scale=1.0,maximum-scale=1.0,user-scalable=no;" />
	<?=Html::cssFile('@web/css/public.css')?>
	<?=Html::cssFile('@web/css/pages.css')?>
</head>
<body id="selectPackage">
	<!-- header start -->
		<header id="mainHeader">
		<ul class="tabTitle">
			<li><a href="#">上网购买</a></li>
			<li class="active"><a href="#">上网连接</a></li>
		</ul>
	</header>
	<!-- header end -->
	<!-- content start -->
	<div class="tabContent">
		<ul id="packageList">
		<!-- 
			<li>
				<a href="#">套餐一</a>
				<ul>
					<li><span class="num">1</span><span class="account">账号：123456</span><span>密码：123456</span><input name="aaa" type="radio"></input></li>
				</ul>
			</li>
		 -->
		</ul>
		<div class="btnBox">
			<input type="button" id="button" value="立即联网"></input>
		</div>
	</div>
	<!-- content end -->
	<?=Html::jsFile('@web/js/jquery-2.2.3.min.js')?>
	<?=Html::jsFile('@web/js/selectPackage.js')?>
</body>
</html>

<script type="text/javascript">
window.onload = function(){
	var account = '<?php echo $account;?>';
	$("#button").on("click",function(){
		$.ajax({
			url: "<?php echo Url::toRoute(['service/checkoutflow']);?>",
	        data: {account:account},
	        type: 'post',
	        dataType: 'json',
	        success : function(response) {
	        	if(response['success'] == true){
	        		location.href ="<?php echo Url::toRoute(['wifi/connect']);?>?account=<?php echo $account?>";
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