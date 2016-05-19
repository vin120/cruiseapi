<?php

namespace app\modules\wifiservice\controllers;

use Yii;
use yii\web\Controller;
use app\modules\wifiservice\components\MyCurl;
use app\modules\wifiservice\components\MyWifi;


class DefaultController extends Controller
{
    public function actionIndex()
    {
    	MyCurl::vcurl(Yii::$app->params['wifi_url'],'status=manage&opt=login&admin=bisheng&pwd=bs566570');
        $url = "http://192.168.9.250/jsp/fee_checkout/comstserver.awm";
        $check_out_params = "status=manage&opt=dbcs&subopt=checkout&dbName=usermanage_umb&admin=bisheng&account=abc123";
        $check_out_json = MyCurl::vcurl(Yii::$app->params['wifi_url'],$check_out_params);
        print_r(iconv('GB2312', 'UTF-8', $check_out_json));
        // return $this->render('index');
    }
}
