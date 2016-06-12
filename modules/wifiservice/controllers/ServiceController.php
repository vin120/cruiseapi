<?php

namespace app\modules\wifiservice\controllers;

use Yii;
use app\models\Member;
use yii\web\Controller;
use app\components\MemberService;
use app\modules\wifiservice\components\MyCurl;
use app\modules\wifiservice\components\MyWifi;

class ServiceController extends Controller
{
	
	//check out flow via comst system  api
    public function actionCheckoutflow()
    {
        $sign = Yii::$app->admin->identity->sign;
//         $member = MemberService::getMemberbysign($sign);
        $type = Yii::$app->admin->identity->member_type;
        if($type == 1){
        	//会员
        	$membership = MemberService::getMemberbysign($sign);
        }else {
        	$membership =  MemberService::getCrewBySign($sign);
        }
    	
        //查流量
        $check_out_json = MyCurl::CheckFlow($membership['passport_number']);
   
    	$check_out_array = json_decode($check_out_json,true);
		
		if($check_out_array['success']){
			$arr = explode("<br>", $check_out_array['data']['feeInfo']);
			
			//剔除不必要的字符
            $check_out_array['data']['in_flow'] = str_replace('MB','',explode(": ",$arr[5])[1]);
            $check_out_array['data']['out_flow'] = str_replace('MB','',explode(": ",$arr[6])[1]);
			$check_out_array['data']['total_flow'] = str_replace('MB','',explode(": ",$arr[7])[1]);
			$json = json_encode($check_out_array);
			
			echo $json;
		}else{
			//出现错误时
			echo $check_out_json;
		}
    }
    
    //wifi connect via comst system api
    public function actionWificonnect()
    { 
    	$sign = Yii::$app->admin->identity->sign;
//         $member = MemberService::getMemberbysign($sign);
    	$type = Yii::$app->admin->identity->member_type;
    	if($type == 1){
    		//会员
    		$membership = MemberService::getMemberbysign($sign);
    	}else {
    		$membership =  MemberService::getCrewBySign($sign);
    	}
        
        //先查看comst 中有没有这个用户
        $find_res = MyCurl::FindUser($membership['passport_number']);
        $find_res = json_decode($find_res,true);
        if($find_res['data']){
        	//查流量
        	$check_out_json = MyCurl::CheckFlow($membership['passport_number']);
        	$check_out_array = json_decode($check_out_json,true);
        	$arr = explode("<br>", $check_out_array['data']['feeInfo']);
        	
        	//剔除不必要的字符
        	$wifi_online_in_flow = str_replace('MB','',explode(": ",$arr[5])[1]);
        	$wifi_online_out_flow = str_replace('MB','',explode(": ",$arr[6])[1]);
        	$wifi_online_total_flow = str_replace('MB','',explode(": ",$arr[7])[1]);
        	
        	//连接网络
        	$username = $membership['passport_number'];
        	$sql = "SELECT * FROM vcos_comst_wifi WHERE username ='$username'";
        	$password = Yii::$app->db->createCommand($sql)->queryOne()['password'];
        	
        	$online_json = MyCurl::Connect($membership['passport_number'],$password);
        	$online_arr = json_decode($online_json,true);
        	if($online_arr['success']){
        		//write login log to db
        		MyWifi::WriteWifiLoginLogToDB($membership,$wifi_online_in_flow,$wifi_online_out_flow,$wifi_online_total_flow);
        		echo $online_json;
        	}else{
        		echo '{"success":false,"Info":"流量不足，请及时充值"}';
        	}
        	
        }else{
        	echo '{"success":false,"Info":"用户不存在，请先购买流量包"}';
        }     
    }
    
    
    
    //disconnect wifi via comst system api
    public function actionWifidisconnect()
    {
    	$sign = Yii::$app->admin->identity->sign;
//         $member = MemberService::getMemberbysign($sign);
        $type = Yii::$app->admin->identity->member_type;
        if($type == 1){
        	//会员
        	$membership = MemberService::getMemberbysign($sign);
        }else {
        	$membership =  MemberService::getCrewBySign($sign);
        }
        //先查看comst 中有没有这个用户
        $find_res = MyCurl::FindUser($membership['passport_number']);
        $find_res = json_decode($find_res,true);
        if($find_res['data']){

        	//查找comst中$passport对应的idRec
        	$idRec = MyCurl::FindidRec($membership['passport_number']);
        	
        	//查流量
        	$check_out_json = MyCurl::CheckFlow($membership['passport_number']);
        	$check_out_array = json_decode($check_out_json,true);
        	$arr = explode("<br>", $check_out_array['data']['feeInfo']);
        	
        	//剔除不必要的字符
        	$wifi_online_in_flow = str_replace('MB','',explode(": ",$arr[5])[1]);
        	$wifi_online_out_flow = str_replace('MB','',explode(": ",$arr[6])[1]);
        	$wifi_online_total_flow = str_replace('MB','',explode(": ",$arr[7])[1]);
        	 
        	
        	//断开连接网络
        	$disc_json = MyCurl::DisConnect($idRec);
        	
        	//断开连接记录写入DB
        	MyWifi::WriteWifiLogoutLogToDB($membership,$wifi_online_in_flow,$wifi_online_out_flow,$wifi_online_total_flow);
        	echo $disc_json;
        }else{
        	echo '{"success":false,"Info":"用户不存在，此帐号没有连接网络，请先连接网络"}';
        }
        
    }
    
}
