<?php
	use yii\helpers\Url;
    use app\modules\wifiservice\themes\basic\assets\ThemeAsset;
    ThemeAsset::register($this);
    $baseUrl = $this->assetBundles[ThemeAsset::className()]->baseUrl . '/';
    $this->title = "登录状态";
?>

<script type="text/javascript">
window.onload = function(){
	var status = '<?php echo $status;?>';
	if( status==false){
		location.href ="<?php echo Url::toRoute(['wifi/connect']);?>";
	}else{
		location.href ="<?php echo Url::toRoute(['wifi/disconnect']);?>";
	}
}
</script>
