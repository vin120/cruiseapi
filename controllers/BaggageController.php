<?php

namespace app\controllers;


use Yii;
use app\components\MemberService;
use app\components\BaggageService;
use app\components\CruiseLineService;

class BaggageController extends MyActiveController
{

	public function actionFindbaggage()
	{
		$sign = isset($_POST['sign']) ? $_POST['sign'] : '';
		$check_type = isset($_POST['type']) ? $_POST['type'] : 'CheckIn';
		
		$response = array();
		
		if(!empty($sign)){
			$member = MemberService::getMemberbysign($sign);
			if(!empty($member))
			{
				$cruise_line = CruiseLineService::getCruiseLineByCurrTime();
				
				$baggage_array = BaggageService::getAllbaggage($member->member_code, 277, $check_type);   //TODO  277是固定写死的，应该用$cruise_line替换
				$response['data'] = $baggage_array;				
			}else{
				$response['error'] = array('error_code'=>1,'message'=>'Member does not exist');
			}
		}else{
			$response['error'] = array('error_code'=>1,'message'=>'sign can not be empty');
		}
		
		return  $response;
	}
	
	public function actionFindbaggageinfo()
	{
		$baggage_barcode = isset($_POST['baggage_barcode']) ? $_POST['baggage_barcode'] : '';
		
		$response = array();
		
		if(!empty($baggage_barcode)){
			$baggage_log_array = BaggageService::getBaggageInfo($baggage_barcode);
			$response['data'] = $baggage_log_array;
		}else{
			$response['error'] = array('error_code'=>1,'message'=>'baggage_barcode can not be empty');
		}
		
		return  $response;
	}
	
	public function actionBaggagefiling()
	{
		$sign = isset($_POST['sign']) ? $_POST['sign'] : '';
		$baggage_num = isset($_POST['baggage_num']) ? $_POST['baggage_num'] : '';
		$filing_time = date('Y-m-d H:i:s',time());
		$status = 1;
		
		$response = array();
		
		if(!empty($sign) && !empty($baggage_num)){
			$member = MemberService::getMemberbysign($sign);

			if(!empty($member))
			{
				$member_code = $member->member_code;
				BaggageService::saveBaggagefiling($member_code, $baggage_num,$filing_time,$status);
				$response['data'] = array('code'=>1,'message'=>'Save Baggagefiling success');
			}
			else
			{
				$response['error'] = array('error_code'=>1,'message'=>'member not exist');
			}
		}
		else 
		{
			$response['error'] = array('error_code'=>1,'message'=>'sign or baggage_num can not be empty');
		}
		return $response;
		
	}
	
	
	public function actionGetbaggagefiling()
	{
		$sign = isset($_POST['sign']) ? $_POST['sign'] : '';
		$response = array();
		if(!empty($sign)){
			$member = MemberService::getMemberbysign($sign);
		
			if(!empty($member)){
				$member_code = $member->member_code;
				$response = BaggageService::getBaggagefiling($member_code);
			}
			else{
				$response['error'] = array('error_code'=>1,'message'=>'member not exist');
			}
		}
		else{
			$response['error'] = array('error_code'=>1,'message'=>'sign can not be empty');
		}
		return $response;
	}
	
	
	public function actionDelbaggagefiling()
	{
		$sign = isset($_POST['sign']) ? $_POST['sign'] : '';
		$id = isset($_POST['id']) ? $_POST['id'] : '';
		$response = array();
		if(!empty($sign) && !empty($id)){
			$member = MemberService::getMemberbysign($sign);
		
			if(!empty($member)){
				$member_code = $member->member_code;
				$bool_del = BaggageService::delBaggagefiling($id,$member_code);
				if($bool_del){
					$response['data'] = 1;
				}else {
					$response['data'] = 2;
				}	
			}
			else{
				$response['error'] = array('error_code'=>1,'message'=>'member not exist');
			}
		}
		else{
			$response['error'] = array('error_code'=>1,'message'=>'sign or id  can not be empty');
		}
		return $response;
	}
}

