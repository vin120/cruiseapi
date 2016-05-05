<?php

namespace app\controllers;

use Yii;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use yii\rest\ActiveController;
use yii\helpers\ArrayHelper;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\validators;
use yii\validators\EmailValidator;
use app\components\MyString;
use app\components\CruiseLineService;
use app\components\OrderService;
use app\components\MemberService;
use app\models\MemberBooking;

class MembershipController extends MyActiveController {
	public function actionFindmembership() {
		$mcode = isset ( $_POST ['mcode'] ) ? $_POST ['mcode'] : '';
		
		$response = array ();
		
		if (! empty ( $mcode )) {
			$response = MemberService::getMembershipByCodeAndPassport ( $mcode );
		} else {
			$response ['error'] = array (
					'error_code' => 1,
					'message' => 'mcode can not be empty' 
			);
		}
		
		return $response;
	}
	
	public function actionCardpay() {
		$mcode = isset ( $_POST ['mcode'] ) ? $_POST ['mcode'] : '';
		$order_num = isset ( $_POST ['order_num'] ) ? $_POST ['order_num'] : ''; 	// 订单号
		$passwd = isset ( $_POST ['passwd'] ) ? $_POST ['passwd'] : ''; 			// 密码
 		$datas = isset($_POST['datas']) ? $_POST['datas'] : '';						// 条形码
 		
 		//示例代码
// 		$datas = '
// 			{
// 				"data":[{
// 					"barcode":"111111",
// 					"price":"500",
// 					"count":"3"
// 				},
// 				{
// 					"barcode":"222222",
// 					"price":"200",
// 					"count":"1"
// 				}]
// 			}';

		$data_json = json_decode($datas,true);
		$data_array = $data_json['data'];
		$response = array ();
		
		
		if (!empty ( $mcode ) && !empty( $order_num ) && !empty( $datas )) { // && !empty($passwd)){
			$response = MemberService::memberCardpay ( $mcode,$order_num, $passwd,$data_array);
		} else {
			$response ['error'] = array (
					'error_code' => 1,
					'message' => 'mcode,order_num,amount,password,datas can not be empty' 
			);
		}
		return $response;
	}
	public function actionSalesreturn() {
		// 退货
		$response = array ();
		$mcode = isset ( $_POST ['mcode'] ) ? $_POST ['mcode'] : '';
		$order_num = isset ( $_POST ['order_num'] ) ? $_POST ['order_num'] : '';
		$datas = isset($_POST['datas']) ? $_POST['datas'] : '';

 		//示例代码
//  		$datas = '
// 			{
// 				"data":[{
// 					"barcode":"111111",
// 					"price":"500",
// 					"count_return":"3"
// 				},
// 				{
// 					"barcode":"222222",
// 					"price":"200",
// 					"count_return":"1"
// 				}]
// 			}';
		
		$data_json = json_decode($datas,true);
		$data_array = $data_json['data'];
		if(!empty($mcode) && !empty($order_num) && !empty($datas)){
			$response = MemberService::goodsreturn($mcode,$order_num,$data_array);
		}else {
			$response ['error'] = array (
					'error_code' => 1,
					'message' => 'mcode , order_num, datas, can not be empty'
			);
		}
		return $response;
	}
	
	
	
	public function actionDishpsay()
	{
		//点餐系统 支付
		$mcode = isset ( $_POST ['mcode'] ) ? $_POST ['mcode'] : '';
		$amount = isset ( $_POST ['amount'] ) ? $_POST ['amount'] * 100 : ''; 		// 总价
		$order_num = isset ( $_POST ['order_num'] ) ? $_POST ['order_num'] : ''; 	// 订单号
		
		$response = array ();
		
		if (! empty ( $mcode ) && ! empty ( $amount ) && !empty($order_num)) {
			$response = MemberService::memberDishpay ( $mcode, $amount,$order_num);
		} else {
			$response ['error'] = array (
					'error_code' => 1,
					'message' => 'mcode,amount,order_num can not be empty'
			);
		}
		return $response;
	}
	
	public function actionDishreturn()
	{
		// 点餐系统退货  
		//todo
// 		$response = array ();
// 		$mcode = isset ( $_POST ['mcode'] ) ? $_POST ['mcode'] : '';
// 		$order_num = isset ( $_POST ['order_num'] ) ? $_POST ['order_num'] : '';
// 		$datas = isset($_POST['datas']) ? $_POST['datas'] : '';
// 		$status = isset($_POST['status']) ? $_POST['status'] : ''; //1,单件退货    2.整单退货
		
// 		//示例代码
// // 		$datas = [
// // 				['barcode'=>333,'price'=>200,'count_return'=>1],
// // 		];
		
// 		$data = isset($datas[0]) ? $datas[0] : '';
		
// 		if(!empty($status) && !empty($mcode)){
// 			$response = MemberService::dishreturn($mcode,$order_num,$data,$status);
// 		}else {
// 			$response ['error'] = array (
// 					'error_code' => 1,
// 					'message' => 'status,barcode, can not be empty'
// 			);
// 		}
// 		return $response;
	}
	
	
	public function actionSearchmembership() {
		$search = isset ( $_POST ['search'] ) ? $_POST ['search'] : '';
		$response = array ();
		
		if (! empty ( $search )) {
			$response = MemberService::getMemberBySearch ( $search );
		} else {
			$response ['error'] = array (
					'error_code' => 1,
					'message' => 'search can not be empty' 
			);
		}
		return $response;
	}
	
	//获取所有会员的信息
	public function actionGetallmemberinfo()
	{
		$page = isset($_POST['page']) ? $_POST['page'] : 1;
		$page_size = isset($_POST['page_size']) ? $_POST['page_size'] : 20;
		
		$response = MemberService::getallmemberinfo($page,$page_size);
		
		return $response;
 	}
	
	
	public function actionLogin() {
		$name = isset ( $_POST ['name'] ) ? $_POST ['name'] : '';
		$passwd = isset ( $_POST ['passwd'] ) ? $_POST ['passwd'] : '';
		
		$emailValidator = new EmailValidator ();
		
		$emall_bool = $emailValidator->validate ( $name );
		
		if ($emall_bool) {
			$where_name = 'member_email = \'' . $name . '\'';
		} elseif (MyString::validateMobile ( $name )) {
			$where_name = 'mobile_number = \'' . $name . '\'';
		} else {
			$where_name = 'passport_number = \'' . $name . '\'';
		}
		$sql_value = 'SELECT member_id,smart_card_number,member_code as code,cn_name as name,passport_number,
				member_password,member_email as email,mobile_number as phone,(member_money/100) as money,member_credit as credit,sign,(overdraft_limit/100) as overdraft_limit,(curr_overdraft_amount/100) as curr_overdraft_amount 
				FROM vcos_member WHERE member_password=\'' . md5 ( $passwd ) . '\' AND ' . $where_name . '  LIMIT 1';
		$membership = Yii::$app->db->createCommand ( $sql_value )->queryOne ();
		
		$response = array ();
		
		if (empty ( $membership )) {
			$response ['error'] = array (
					'error_code' => 1,
					'message' => 'membership does not exist',
					'value' => $_POST 
			);
		} else {
			$cruise_line = CruiseLineService::getCruiseLineByCurrTime ();
			$member_room = '';
			if (! empty ( $cruise_line )) {
				$member_room = CruiseLineService::getCruiseAddress ( $membership ['code'], $cruise_line ['trip_id'] );
			}
			
			$sql_icon = ' SELECT icon FROM vcos_im_member WHERE member_id=\'' . $membership ['passport_number'] . '\' ';
			$icon = Yii::$app->db->createCommand ( $sql_icon )->queryOne ();
			
			
			if($member_room == false){
				$member_room = '';
			}
			$membership ['cruise_line'] = $cruise_line;
			$membership ['member_room'] = $member_room; // $member_room ;
			$membership ['icon'] = $icon ['icon'];
			unset ( $membership ['member_id'] );
			unset ( $membership ['member_password'] );
			$response ['data'] = $membership;
		}
		return $response;
	}
	public function actionChangepassword() {
		$sign = isset ( $_POST ['sign'] ) ? $_POST ['sign'] : '';
		$new_passwd = isset ( $_POST ['new_passwd'] ) ? $_POST ['new_passwd'] : '';
		$old_passwd = isset ( $_POST ['old_passwd'] ) ? $_POST ['old_passwd'] : '';
		
		$response = array ();
		
		if (! empty ( $sign ) && ! empty ( $new_passwd ) && ! empty ( $old_passwd )) {
			$sql_value = 'SELECT member_id,member_code,passport_number,cn_name,member_password,sign  
					FROM vcos_member WHERE member_password=\'' . md5 ( $old_passwd ) . '\' AND sign=\'' . $sign . '\'  LIMIT 1';
			
			$membership = Yii::$app->db->createCommand ( $sql_value )->queryOne ();
			if (! empty ( $membership )) {
				$new_sign = md5 ( md5 ( $membership ['member_code'] ) . md5 ( $membership ['passport_number'] ) . md5 ( md5 ( $new_passwd ) ) );
				
				$update_sql = 'UPDATE vcos_member SET member_password=\'' . md5 ( $new_passwd ) . '\' WHERE member_password=\'' . md5 ( $old_passwd ) . '\' AND sign=\'' . $sign . '\'';
				$command = Yii::$app->db->createCommand ( $update_sql );
				$command->execute ();
				
				$response ['data'] ['sign'] = $new_sign;
			} else {
				$response ['error'] = array (
						'error_code' => 1,
						'message' => 'membership does not exist' 
				);
			}
		} else {
			$response ['error'] = array (
					'error_code' => 1,
					'message' => 'sign,new_passwd,old_passwd can not be empty' 
			);
		}
		
		return $response;
	}
	
	public function actionGetmemberinfo()
	{
		$sign = isset ( $_POST ['sign'] ) ? $_POST ['sign'] : '';
		$response = array ();
		if (! empty ( $sign )) {
			$member = MemberService::getMemberinfobysign( $sign );
			if (! empty ( $member )) {
				$response['data'] = $member;
			}else {
				$response ['error'] = array ('error_code' => 1,'message' => 'membership does not exist');
			}
		}else {
			$response ['error'] = array ('error_code' => 1,'message' => 'sign can not be empty');
		}
		return $response;
	}
	
	public function actionMybooking() {
		$response = array ();
		
		$sign = isset ( $_POST ['sign'] ) ? $_POST ['sign'] : '';
		if (! empty ( $sign )) {
			$member = MemberService::getMemberbysign ( $sign );
			if (! empty ( $member )) {
				$memberBookingArray = OrderService::getMemberBooking ( $member->member_code );
				$response ['data'] = $memberBookingArray;
			} else {
				$response ['error'] = array (
						'error_code' => 1,
						'message' => 'membership does not exist' 
				);
			}
		} else {
			$response ['error'] = array (
					'error_code' => 1,
					'message' => 'sign can not be empty' 
			);
		}
		return $response;
	}
	public function actionMyorder() {
		$response = array ();
		
		$sign = isset ( $_POST ['sign'] ) ? $_POST ['sign'] : '';
		$status_type = isset ( $_POST ['status_type'] ) ? $_POST ['status_type'] : '';
		
		$curr_page = isset ( $_POST ['page'] ) ? $_POST ['page'] : '';
		$page_size = isset ( $_POST ['page_size'] ) ? $_POST ['page_size'] : '20';
		
		if (! empty ( $sign )) {
			$member = MemberService::getMemberbysign ( $sign );
			if (! empty ( $member )) {
				$count = OrderService::getMemberOrder ( $member->member_code, $status_type );
				$total_page = ceil ( $count / $page_size );
				if ($curr_page > $total_page || $curr_page < 1) {
					$curr_page = 1;
				}
				
				$order_array = OrderService::getMemberOrder ( $member->member_code, $status_type, false, $curr_page );
				
				$order_no_array = array ();
				foreach ( $order_array as $order ) {
					$order_no_array [] = $order ['order_serial_num'];
				}
				
				$order_detail_array = OrderService::getMemberOrderDetail ( $order_no_array );
				
				$temp_order_count = count ( $order_array );
				for($i = 0; $i < $temp_order_count; $i ++) {
					$temp_order_no = $order_array [$i] ['order_serial_num'];
					
					foreach ( $order_detail_array as $order_detail ) {
						if ($temp_order_no == $order_detail ['order_serial_num']) {
							$order_array [$i] ['order_detial_items'] [] = $order_detail;
						}
					}
				}
				$response ['data'] = $order_array;
			} else {
				$response ['error'] = array (
						'error_code' => 1,
						'message' => 'membership does not exist' 
				);
			}
		} else {
			$response ['error'] = array (
					'error_code' => 1,
					'message' => 'sign can not be empty' 
			);
		}
		
		return $response;
	}
	public function actionOrderinfo() {
		$order_serial_num = isset ( $_POST ['order_serial_num'] ) ? $_POST ['order_serial_num'] : '';
		$response = array ();
		if (! empty ( $order_serial_num )) {
			$order = OrderService::getOrderByNo ( $order_serial_num );
			
			$order_detail_array = OrderService::getMemberOrderDetail ( array (
					$order ['order_serial_num'] 
			) );
			
			$order ['detial_items'] = $order_detail_array;
			$response = $order;
		} else {
			$response ['error'] = array (
					'error_code' => 1,
					'message' => 'order serial num can not be empty' 
			);
		}
		
		return $response;
	}
	public function actionCancelbooking() {
		$sign = isset ( $_POST ['sign'] ) ? $_POST ['sign'] : '';
		$booking_no = isset ( $_POST ['booking_no'] ) ? $_POST ['booking_no'] : '';
		
		$remark = isset ( $_POST ['remark'] ) ? $_POST ['remark'] : '';
		
		$response = array ();
		
		if (! empty ( $sign ) && ! empty ( $booking_no )) {
			$member = MemberService::getMemberbysign ( $sign );
			if (! empty ( $member )) {
				$booking = MemberBooking::find ()->where ( [ 
						'booking_no' => $booking_no,
						'member_code' => $member->member_code,
						'status' => [ 
								1,
								2 
						] 
				] )->one ();
				if (! empty ( $booking )) {
					$booking->status = 5;
					$booking->remark = $remark;
					$count = $booking->save ();
					if ($count) {
						$response ['data'] = array (
								'code' => 1,
								'message' => 'cancel booking success' 
						);
					} else {
						$response ['error'] = array (
								'error_code' => 3,
								'message' => 'booking cancel fail' 
						);
					}
				} else {
					$response ['error'] = array (
							'error_code' => 2,
							'message' => 'booking does not exist' 
					);
				}
			} else {
				$response ['error'] = array (
						'error_code' => 1,
						'message' => 'membership does not exist' 
				);
			}
		} else {
			$response ['error'] = array (
					'error_code' => 1,
					'message' => 'sign or booking_no can not be empty' 
			);
		}
		return $response;
	}
	public function actionDelbooking() {
		$sign = isset ( $_POST ['sign'] ) ? $_POST ['sign'] : '';
		$booking_no = isset ( $_POST ['booking_no'] ) ? $_POST ['booking_no'] : '';
		
		$response = array ();
		if (! empty ( $sign ) && ! empty ( $booking_no )) {
			$member = MemberService::getMemberbysign ( $sign );
			if (! empty ( $member )) {
				$booking = MemberBooking::find ()->where ([
							'booking_no' => $booking_no,
							'member_code' => $member->member_code,
							'status' => [3,4], 
						])->one ();
				if (! empty ( $booking )) {
					$booking->status = 6;
					$count = $booking->save ();
					if ($count) {
						$response ['data'] = array (
								'code' => 1,
								'message'=>'delete booking success');
					}else {
						$response['error'] = array('error_code'=>3,'message'=>'booking delete fail');
					}
				}else {
					$response['error'] = array('error_code'=>2,'message'=>'booking does not exist');
				}
				
			}else {
				$response['error'] = array('error_code'=>1,'message'=>'member does not exist');
			}
		}else 
		{
			$response['error'] = array('error_code'=>1,'message'=>'sign or booking_no can not be empty');
		}
		return $response;
	}
	
}