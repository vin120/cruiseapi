<?php

namespace app\modules\wifiservice\controllers;

use Yii;
use yii\web\Controller;
use app\modules\wifiservice\components\MyCurl;
use app\modules\wifiservice\components\MyWifi;


class InteractionController extends Controller
{
	
	public $enableCsrfValidation = false;
	
	//查看流量
	public function actionCheckflow()
	{
		$passport = Yii::$app->request->post('passport');
		$flow = MyCurl::CheckFlow($passport);
		$flow_array = json_decode($flow,true);
		//剩余流量
		if($flow_array['success']){
			$arr = explode("<br>", $flow_array['data']['feeInfo']);
			$title = explode(": ",$arr[0])[1];	//标题：按流量计费
			$last_charge_time = explode(": ",$arr[1])[1];	//上次结算时间
			$price = explode(": ",$arr[2])[1];	//wifi单价
			$last_charge_money = str_replace('元','',explode(": ",$arr[3])[1]);	//上次交费金额
			$last_left_money = explode(": ",$arr[4])[1];	//上次交费前余额
			$flow_in = str_replace('MB','',explode(": ",$arr[5])[1]);	//流进流量
			$flow_out = str_replace('MB','',explode(": ",$arr[6])[1]);	//流出流量
			$total_used_flow = str_replace('MB','',explode(": ",$arr[7])[1]);	//用户使用了的总流量
			$used_money = str_replace('元','',explode(": ",$arr[8])[1]);		//用户已经使用了的钱
			$refund_money = str_replace('元','',explode(": ",$arr[9])[1]);		//期间退费
			$money = str_replace('元','',explode(": ",$arr[10])[1]);		//用户的当前余额
			
			$response = '{"success":"true","data":{"title":"'.$title.'","last_charge_time":"'.$last_charge_time.'","price":"'.$price.'","last_charge_money":"'.$last_charge_money.'","last_left_money":"'.$last_left_money.'","flow_in":"'.$flow_in.'","flow_out":"'.$flow_out.'","total_used_flow":"'.$total_used_flow.'","used_money":"'.$used_money.'","refund_money":"'.$refund_money.'","money":"'.$money.'"}}';
			
		}else{
			$response = '{"success":"false","message":"用户不存在"}';
		}
		return $response;
	}
	
	//充值流量
	public function actionRechargewifi()
	{
		$passport = Yii::$app->request->post('passport');
		$price = Yii::$app->request->post('price');
		
		if(is_numeric($price)){
			
			//先断开用户，防止出现连接记录负数的情况
			MyCurl::DisConnect($passport);
			
			
			$sql = " SELECT * FROM vcos_member_crew WHERE passport_number='$passport' LIMIT 1";
			$member = Yii::$app->mdb->createCommand($sql)->queryOne();
			
			//查流量
			$check_out_json = MyCurl::CheckFlow($member['passport_number']);
			$check_out_array = json_decode($check_out_json,true);
			
			$arr = explode("<br>", $check_out_array['data']['feeInfo']);
			
			//剔除不必要的字符
			$wifi_online_in_flow = str_replace('MB','',explode(": ",$arr[5])[1]);
			$wifi_online_out_flow = str_replace('MB','',explode(": ",$arr[6])[1]);
			$wifi_online_total_flow = str_replace('MB','',explode(": ",$arr[7])[1]);
			//断开连接记录写入DB
			MyWifi::WriteWifiLogoutLogToDB($member,$wifi_online_in_flow,$wifi_online_out_flow,$wifi_online_total_flow);
			
			
			//充值
			$recharge = MyCurl::RechargeWifi($passport,$price);
			$recharge_array = json_decode($recharge,true);
			if($recharge_array['success']){
				$arr = explode("<br>", $recharge_array['data']['feeInfo']);
				$title = explode(": ",$arr[0])[1];	//标题：按流量计费
				$last_charge_time = explode(": ",$arr[1])[1];	//上次结算时间
				$price = explode(": ",$arr[2])[1];	//wifi单价
				$last_charge_money = str_replace('元','',explode(": ",$arr[3])[1]);	//上次交费金额
				$last_left_money = explode(": ",$arr[4])[1];	//上次交费前余额
				$flow_in = str_replace('MB','',explode(": ",$arr[5])[1]);	//流进流量
				$flow_out = str_replace('MB','',explode(": ",$arr[6])[1]);	//流出流量
				$total_used_flow = str_replace('MB','',explode(": ",$arr[7])[1]);	//用户使用了的总流量
				$used_money = str_replace('元','',explode(": ",$arr[8])[1]);		//用户已经使用了的钱
				$refund_money = str_replace('元','',explode(": ",$arr[9])[1]);		//期间退费
				$money = str_replace('元','',explode(": ",$arr[10])[1]);		//用户的当前余额
			
				$response = '{"success":"true","data":{"title":"'.$title.'","last_charge_time":"'.$last_charge_time.'","price":"'.$price.'","last_charge_money":"'.$last_charge_money.'","last_left_money":"'.$last_left_money.'","flow_in":"'.$flow_in.'","flow_out":"'.$flow_out.'","total_used_flow":"'.$total_used_flow.'","used_money":"'.$used_money.'","refund_money":"'.$refund_money.'","money":"'.$money.'"}}';
			}else{
				$response = '{"success":"false","message":"充值失败"}';
			}
		}else{
			$response = '{"success":"false","message":"充值失败"}';
		}
		
// 		$time = date('Y-m-d H:i:s',time());
// 		$sql = "INSERT INTO vcos_wifi_test_tab (`passport`,`params`,`response`,`time`) VALUES ('$passport','recharge','$response','$time') ";
// 		Yii::$app->db->createCommand($sql)->execute();
		
		return $response;
	}
	
	
	public function actionInitaccount()
	{
		$passport = Yii::$app->request->post('passport');
		$create_id = Yii::$app->request->post('create_id');	//操作员id
		
		$sql = " SELECT member_type FROM vcos_member_crew WHERE passport_number='$passport'";
		$type = Yii::$app->mdb->createCommand($sql)->queryOne()['member_type'];
		
		$flow = MyCurl::CheckFlow($passport);
		$flow_array = json_decode($flow,true);
		//剩余余额
		if($flow_array['success']){
			$arr = explode("<br>", $flow_array['data']['feeInfo']);
			$left_money = str_replace('元','',explode(": ",$arr[10])[1]);		//用户的当前余额
		}else {
			$left_money = 0;
		}
		
		//先断开用户，防止出现连接记录负数的情况
		MyCurl::DisConnect($passport);
		$sql = " SELECT * FROM vcos_member_crew WHERE passport_number='$passport' LIMIT 1";
		$member = Yii::$app->mdb->createCommand($sql)->queryOne();
		//查流量
		$check_out_json = MyCurl::CheckFlow($member['passport_number']);
		$check_out_array = json_decode($check_out_json,true);
		$arr = explode("<br>", $check_out_array['data']['feeInfo']);
		//剔除不必要的字符
		$wifi_online_in_flow = str_replace('MB','',explode(": ",$arr[5])[1]);
		$wifi_online_out_flow = str_replace('MB','',explode(": ",$arr[6])[1]);
		$wifi_online_total_flow = str_replace('MB','',explode(": ",$arr[7])[1]);
		//断开连接记录写入DB
		MyWifi::WriteWifiLogoutLogToDB($member,$wifi_online_in_flow,$wifi_online_out_flow,$wifi_online_total_flow);
		
		$init = MyCurl::InitAccount($passport);
		$init_array = json_decode($init,true);
		
		if($init_array['success']){
			$arr = explode("<br>", $init_array['data']['feeInfo']);
			$title = explode(": ",$arr[0])[1];	//标题：按流量计费
			$last_charge_time = explode(": ",$arr[1])[1];	//上次结算时间
			$price = explode(": ",$arr[2])[1];	//wifi单价
			$last_charge_money = str_replace('元','',explode(": ",$arr[3])[1]);	//上次交费金额
			$last_left_money = explode(": ",$arr[4])[1];	//上次交费前余额
			$flow_in = str_replace('MB','',explode(": ",$arr[5])[1]);	//流进流量
			$flow_out = str_replace('MB','',explode(": ",$arr[6])[1]);	//流出流量
			$total_used_flow = str_replace('MB','',explode(": ",$arr[7])[1]);	//用户使用了的总流量
			$used_money = str_replace('元','',explode(": ",$arr[8])[1]);		//用户已经使用了的钱
			$refund_money = str_replace('元','',explode(": ",$arr[9])[1]);		//期间退费
			$money = str_replace('元','',explode(": ",$arr[10])[1]);		//用户的当前余额
		
			$time = time();
			$init_date = date("Y-m-d",$time);
			$init_time = date("H:i:s",$time);
			
			$sql = " INSERT INTO `vcos_wifi_account_init_log` (`init_type`,`passport_no`,`init_date`,`init_time`,`init_amount`,`account_info`,`create_id`) VALUES ('$type','$passport','$init_date','$init_time','$left_money','$init','$create_id')";
			Yii::$app->db->createCommand($sql)->execute();
			
			$response = '{"success":"true","data":{"title":"'.$title.'","last_charge_time":"'.$last_charge_time.'","price":"'.$price.'","last_charge_money":"'.$last_charge_money.'","last_left_money":"'.$last_left_money.'","flow_in":"'.$flow_in.'","flow_out":"'.$flow_out.'","total_used_flow":"'.$total_used_flow.'","used_money":"'.$used_money.'","refund_money":"'.$refund_money.'","money":"'.$money.'"}}';
		}else{
			$response = '{"success":"false","message":"初始化失败"}';
		}
		
		return $response;
	}

	/**
     * 查询网站访问记录
     *
     * @return json
     */
    public function actionCheckuserwebaccesslog()
    {//echo "111";die();
    	$account = Yii::$app->request->post("account");	//帐户名
    	$begTime = Yii::$app->request->post("begTime"); //起始时间
    	$endTime = Yii::$app->request->post("endTime"); //截止时间
    	$path = Yii::$app->request->post("path"); //地址，第一次查询时不传，点击下一页必传
    	$recPos = Yii::$app->request->post("recPos"); //起始id号，第一次查询时不传，点击下一页必传

        //模拟登录
        MyCurl::vcurl(Yii::$app->params['wifi_url'].'comstserver.awm?','status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);

        //格式转换，将y-m-d格式换成y年m月d日
        $change_begTime = date('y-m-d', strtotime($begTime));
        $change_endTime = date('y-m-d', strtotime($endTime));
        $begTime = preg_replace('/-/', '月', preg_replace('/-/', '年', $change_begTime, 1), 1).'日';
        $endTime = preg_replace('/-/', '月', preg_replace('/-/', '年', $change_endTime, 1), 1).'日';
        $begTime = iconv('UTF-8','GB2312//IGNORE', $begTime);
        $endTime = iconv('UTF-8','GB2312//IGNORE', $endTime);

        $find_params = "status=manage&subopt=query&opt=dbcs&dbName=userlog_ulb&direct=1&IsAccount=on&account=$account
                        &begTime=$begTime&endTime=$begTime&path=$path&idRec=$recPos&admin=".Yii::$app->params['wifi_login_name'];
        $url = Yii::$app->params['wifi_url']."log_manage/comstserver.awm?";
        $find_json = MyCurl::vcurl($url,$find_params);
        // $find_json = json_decode($find_json,true);

        return $find_json;
    }    

    /**
     * 导出网站访问记录,根据条件查询记录总量
     *
     * @return json
     */
    public static function actionExportuserwebaccesslog()
    {
    	//初始化参数
    	$account = Yii::$app->request->post("account");	//帐户名
    	$begTime = Yii::$app->request->post("begTime"); //起始时间
    	$endTime = Yii::$app->request->post("endTime"); //截止时间
    	$path = ''; //地址，第一次查询为空，后续页数查询时，其为上一次查询结果的返回参数
    	$recPos = ''; //起始id号，第一次查询为空，后续页数查询时，其为上一次查询结果的返回参数
        $url = Yii::$app->params['wifi_url']."log_manage/comstserver.awm?";
        $per_page_num = 15;

        //模拟登录
        MyCurl::vcurl(Yii::$app->params['wifi_url'].'comstserver.awm?','status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);

        //格式转换，将y-m-d格式换成y年m月d日
        $change_begTime = date('y-m-d', strtotime($begTime));
        $change_endTime = date('y-m-d', strtotime($endTime));
        $begTime = preg_replace('/-/', '月', preg_replace('/-/', '年', $change_begTime, 1), 1).'日';
        $endTime = preg_replace('/-/', '月', preg_replace('/-/', '年', $change_endTime, 1), 1).'日';
        $begTime = iconv('UTF-8','GB2312//IGNORE', $begTime);
        $endTime = iconv('UTF-8','GB2312//IGNORE', $endTime);

    	$result_data = array();
    	$i = 1;
        do {
        	$find_params = "status=manage&subopt=query&opt=dbcs&dbName=userlog_ulb&direct=1&IsAccount=on&account=$account
                        &begTime=$begTime&endTime=$begTime&path=".$path."&idRec=".$recPos."&admin=".Yii::$app->params['wifi_login_name'];
        
	        $find_json = MyCurl::vcurl($url,$find_params);
	        $find_json = iconv('UTF-8','GB2312//IGNORE', $find_json);
	        $find_json = json_decode($find_json,true);
	        $result_data = array_merge($result_data, $find_json['data']); //追加数组
	        $path = $find_json['path'];
    		$recPos = $find_json['recPos'];
    		$i++;
        } while (count($find_json['data']) == $per_page_num);

        return json_encode($result_data);
    }     
}