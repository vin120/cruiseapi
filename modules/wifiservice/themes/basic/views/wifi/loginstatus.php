<?php
	use yii\helpers\Html;
	use yii\helpers\Url;
    use app\modules\wifiservice\themes\basic\assets\ThemeAsset;
    ThemeAsset::register($this);
    $baseUrl = $this->assetBundles[ThemeAsset::className()]->baseUrl . '/';

    $this->title = "登录状态";
?>


<script type="text/javascript">
window.onload = function(){
	var status = '<?php echo $status;?>';
	if( status=='0'){
		location.href ="<?php echo Url::toRoute(['wifi/disconnect']);?>?mcode=<?php echo $mcode;?>";
	}else{
		location.href ="<?php echo Url::toRoute(['wifi/connect']);?>?mcode=<?php echo $mcode;?>";
	}
}
</script>
