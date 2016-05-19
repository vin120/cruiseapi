<?php
    use app\modules\wifiservice\themes\basic\asset\ThemeAsset;
    ThemeAsset::register($this);
    $baseUrl = $this->assetBundles[ThemeAsset::className()]->baseUrl . '/';

    $this->title = "确认订单";
?>
<!--body begin-->
<body id="buyPackage">
    <!-- header start -->
    <header id="mainHeader">
        <ul>
            <li class="active"><a href="<?php echo Url::toRoute(['index']);?>?Name=<?php echo $Name;?>&PassportNO=<?php echo $PassportNO;?>&TenderType=<?php echo $TenderType?>">上网购买</a></li>
            <li><a href="<?php echo Url::toRoute(['getwifipackage']);?>?Name=<?php echo $Name;?>&PassportNO=<?php echo $PassportNO;?>&TenderType=<?php echo $TenderType?>">上网连接</a></li>
        </ul>
    </header>
    <!-- header end -->
    <!-- content start -->
    <div class="tabContent">
        <div id="welcome" class="pBox">
            <p>尊敬的旅客(<?php echo $Name;?>)，您好！</p>
            <p>欢迎选购辉煌号邮轮WiFi套餐。</p>
        </div>
        <ul id="packageList">
        <?php foreach ($wifi_items as $key => $wifi_item) :?>
            <li><input type="radio" id="wifi_item_id" name="wifi_item_id" value="<?php echo $wifi_item['wifi_id']?>" <?php if($key == 0){?>  checked="checked" <?php }?> ></input><?php echo $wifi_item['wifi_name']?><em class="em">（$<?php echo $wifi_item['sale_price'];?>）</em></li>
        <?php endforeach;?>
        </ul>
        <div class="btnBox">
            <input type="button" id="button" value="购买选择的套餐"></input>
        </div>
    </div>
    <!-- content end -->
</body>
<!--body end-->


