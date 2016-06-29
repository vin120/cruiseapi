<?php

namespace app\modules\wifiservice\controllers;

use Yii;
use yii\web\Controller;
use app\modules\wifiservice\components\MyCurl;


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
}