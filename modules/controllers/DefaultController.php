<?php

namespace app\modules\controllers;

use Yii;
use yii\web\Controller;
use app\modules\components\MyCurl;


class DefaultController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index');
        
    	MyCurl::vlogin(Yii::$app->params['wifi_url'],'status=manage&opt=login&admin=bisheng&pwd=bs566570');
        $url = "http://192.168.9.250/jsp/fee_checkout/comstserver.awm";
        $check_out_params = "status=manage&opt=dbcs&subopt=checkout&dbName=usermanage_umb&admin=bisheng&account=abc123";
        $check_out_json = MyCurl::vpost(Yii::$app->params['wifi_url'],$check_out_params);
        print_r(iconv('GB2312', 'UTF-8', $check_out_json));
    }
    
}
