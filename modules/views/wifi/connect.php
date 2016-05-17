<?php 
	use yii\helpers\Html;
	use yii\helpers\Url;
?>
<!DOCTYPE html>
<html>
<head>
	<title>当前套餐</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,height=device-height,inital-scale=1.0,maximum-scale=1.0,user-scalable=no;" />
	<?=Html::cssFile('@web/css/public.css')?>
	<?=Html::cssFile('@web/css/pages.css')?>
</head>
<body id="currentPackage">
	<!-- header start -->
	<header id="mainHeader">
		<ul>
			<li><a href="#">上网购买</a></li>
			<li class="active"><a href="#">上网连接</a></li>
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
</html>
<?=Html::jsFile('@web/js/jquery-2.2.3.min.js')?>
<script type="text/javascript">
window.onload = function(){
	$.ajax({
		url: "<?php echo Url::toRoute(['service/checkoutflow']);?>",
        data: 'account=<?php echo $account?>',
        type: 'post',
        dataType: 'json',
        success : function(response) {
        	if(response['success'] == true){
            	var str = "<p>"+response['data']['flow']+"</p>";
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
	        data: '',
	        type: 'post',
	        dataType: 'json',
	        success : function(response) {
	        	if(response['success'] == true){
	        		location.href ="<?php echo Url::toRoute(['wifi/index']);?>?account=<?php echo $account?>";
	        	}
	        },
	        error: function(XMLHttpRequest, textStatus, errorThrown) {
				console.log("error");
	        }
		});
	});
}
</script>