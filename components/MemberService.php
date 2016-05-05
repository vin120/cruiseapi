<?php

namespace app\components;

use app\models\Member;
use Yii;

/**
 * Description of MemberService
 *
 * @author Rock.Lei
 */
class MemberService {
	public static function getMemberbysign($sign) {
		$member = Member::find ()->select ( [ 
				'member_id',
				'smart_card_number',
				'member_code',
				'member_name',
				'cn_name',
				'passport_number',
				'member_password',
				'member_email',
				'mobile_number',
				'member_money',
				'member_credit',
				'overdraft_limit',
				'curr_overdraft_amount',
				'sign' 
		] )->where ( [ 
				'sign' => $sign 
		] )->one ();
		
		return $member;
	}

	
	public static function getMemberinfobysign($sign)
	{
		$sql = 'SELECT member_id,smart_card_number,member_code as code,cn_name as name,passport_number,
						member_email as email,mobile_number as phone,(member_money/100) as money,member_credit as credit,
						sign,(overdraft_limit/100) as overdraft_limit,(curr_overdraft_amount/100) as curr_overdraft_amount
						FROM vcos_member WHERE sign=\''.$sign.'\' ';
		$member = Yii::$app->db->createCommand ($sql)->queryOne ();
		return $member;
	}
	
	public static function memberCardpay ( $code_or_passport,$order_num, $passwd,$datas) {
		// $amount 商品价格
		$response = array ();
		if (self::checkMembershipCode ( $code_or_passport )) {
			$member = Member::find ()->select ([ 
					'member_money',
					'member_code',
					'passport_number',
					'member_password',
					'overdraft_limit',
					'curr_overdraft_amount' 
			])->where ([ 
					'member_code' => $code_or_passport 
			])->one ();
		} else {
			$member = Member::find ()->select ( [ 
					'member_money',
					'member_code',
					'passport_number',
					'member_password',
					'overdraft_limit',
					'curr_overdraft_amount' 
			] )->where ( [ 
					'passport_number' => $code_or_passport 
			] )->one ();
		}
		
		if ($member) {
// 			if ($passwd != '888888') {
// 				if (md5 ( $passwd ) != $member->member_password) {
// 					return $response ['error'] = array (
// 							'error_code' => 2,
// 							'message' => 'password wrong' 
// 					);
// 					die ();
// 				}
// 			}
			
			$amount = 0;
			foreach ($datas as $data ){
				$amount += $data['price'] * $data['count'] * 100;
			}
			
			
			//判断  会员余额 + 信用额度 是否够支付账单 
			if ($amount <= $member->overdraft_limit - $member->curr_overdraft_amount + $member->member_money && $member->member_money >= 0) {
				
				// if   member_money > amout      
				//         member_money -= amount
				
				//会员余额够支付，直接支付
				if ($amount <= $member->member_money) {
					$money = $member->member_money - $amount;
					$params_money = [
							':member_money' => $money,
							':member_code' => $member->member_code 
					];
					$sql_money = ' UPDATE vcos_member SET member_money= :member_money WHERE member_code=:member_code';
					
					// 保存到 vcos_order_pay_log
					$params_orderlog = [ 
							':member_code' => $member->member_code,
							':order_num' => $order_num,
							':amount' => $amount,
							':overdraft_amount' => 0,
							':pay_time' => date ( "Y-m-d H:i:s", time () ) 
					];
					$sql_orderlog = ' INSERT INTO vcos_order_pay_log (member_code,order_num,amount,overdraft_amount,pay_time )
							VALUES (:member_code,:order_num,:amount,:overdraft_amount,:pay_time)';
					
					
					$response ['data'] = array (
						'status' => 1,
						'use_amount' => $amount/100,
						'use_limit'=>0,
					);
					
				} else {
					//会员余额不够支付，使用会员余额 + 信用额度支付
					$member_money = $member->member_money;
					$overdraft = $amount - $member->member_money;
					$member->member_money = 0;
					$member->curr_overdraft_amount += $overdraft;
					$params_money = [ 
							':member_money' => $member->member_money,
							':member_code' => $member->member_code,
							':curr_overdraft_amount' => $member->curr_overdraft_amount 
					];
					$sql_money = ' UPDATE vcos_member SET member_money= :member_money,curr_overdraft_amount=:curr_overdraft_amount 
							WHERE member_code=:member_code';
					
					// 保存到 order_pay_log
					$params_orderlog = [ 
							':member_code' => $member->member_code,
							':order_num' => $order_num,
							':amount' => $member_money,
							':overdraft_amount' => $overdraft,
							':pay_time' => date ( "Y-m-d H:i:s", time () ) 
					];
					$sql_orderlog = ' INSERT INTO vcos_order_pay_log (member_code,order_num,amount,overdraft_amount,pay_time )
							VALUES (:member_code,:order_num,:amount,:overdraft_amount,:pay_time)';
					
					$response ['data'] = array (
							'status' => 1,
							'use_amount' => $member_money/100,
							'use_limit'=>$overdraft/100,
					);
				}
				
				//保存到 order_pay_log_detail
				foreach ($datas as $data){
					$params_logdetail = [
							':member_code'=>$member->member_code,
							':order_num'=>$order_num,
							':barcode'=>$data['barcode'],
							':price'=>$data['price']*100,
							':count'=>$data['count'],
							':status'=>1,
					];
					$sql_logdetail = 'INSERT INTO vcos_order_pay_log_detail (member_code,order_num,barcode,price,count,status)
						VALUES (:member_code,:order_num,:barcode,:price,:count,:status)';
					Yii::$app->db->createCommand ( $sql_logdetail, $params_logdetail )->execute ();
				}
				
				
				Yii::$app->db->createCommand ( $sql_money, $params_money )->execute ();
				Yii::$app->db->createCommand ( $sql_orderlog, $params_orderlog )->execute ();

				return $response;
			} else {
				// failed
				return $response ['error'] = array ('error_code' => 2,'message' => 'not enought money ');
			}
		} else {
			return $response ['error'] = array ('error_code' => 1,'message' => 'member does not exist');
		}
	}
	
	
	public static function getallmemberinfo($page,$page_size)
	{
		$response = array ();
		
		$member_array = Member::find ()->select ( [
				'cn_name',
				'member_code',
				'passport_number',
				'member_money',
				'member_credit',
				'overdraft_limit',
				'curr_overdraft_amount',
				'passport_number',
				'date_of_birth'
		] )->offset($page)->limit($page_size)->all ();
		
		$member_count = Member::find ()->select ( [
				'cn_name',
				'member_code',
				'passport_number',
				'member_money',
				'member_credit',
				'overdraft_limit',
				'curr_overdraft_amount',
				'passport_number',
				'date_of_birth'
		] )->count ();
		
		
		
		$cruise_line = CruiseLineService::getCruiseLineByCurrTime ();
			
		$temp_member_array = array ();
		$temp_count = count ( $member_array );
		for($i = 0; $i < $temp_count; $i ++) {
			$member = $member_array [$i];
			$temp_member_array [$i] ['member_code'] = $member->member_code;
			$temp_member_array [$i] ['cn_name'] = $member->cn_name;
			$temp_member_array [$i] ['member_money'] = $member->member_money / 100;
			$temp_member_array [$i] ['member_credit'] = $member->member_credit;
			$temp_member_array [$i] ['member_room'] = 5501; // CruiseLineService::getCruiseAddress($member->member_code, $cruise_line['trip_id'])['cabin_name_num'];
			$temp_member_array [$i] ['overdraft'] = ($member->overdraft_limit - $member->curr_overdraft_amount) / 100;
			$temp_member_array [$i] ['passport_number'] = $member->passport_number;
			$temp_member_array [$i] ['date_of_birth'] = date ( 'Y-m-d ', $member->date_of_birth );
		}
		
		$response['cur_page'] = $page;
		$response['total_page'] =ceil($member_count / $page_size);
		$response['total_count'] = $member_count;
		$response['page_size'] = $page_size;
		$response ['data'] = $temp_member_array;
		return $response;
	}
	
	
	
	public static function goodsreturn($code_or_passport,$order_num,$data_array)
	{
		$response = array ();
		if (self::checkMembershipCode ( $code_or_passport )) {
			$member = Member::find ()->select ( [
					'member_money',
					'member_code',
					'passport_number',
					'member_password',
					'overdraft_limit',
					'curr_overdraft_amount'
			] )->where ( [
					'member_code' => $code_or_passport
			] )->one ();
		} else {
			$member = Member::find ()->select ( [
					'member_money',
					'member_code',
					'passport_number',
					'member_password',
					'overdraft_limit',
					'curr_overdraft_amount'
			] )->where ( [
					'passport_number' => $code_or_passport
			] )->one ();
		}
		if($member){
			
			//先判断  传递过来的数量 + 己退货数量   是否大于 商品数量，如果大于直接返回
			foreach ($data_array as $data){
				
				$params_count = [
						':member_code'=>$member['member_code'],
						':order_num'=>$order_num,
						':barcode'=>$data['barcode'],
				];
				$sql_count = 'SELECT count,count_return FROM vcos_order_pay_log_detail WHERE member_code=:member_code AND  
						order_num=:order_num AND barcode=:barcode';
				$count = Yii::$app->db->createCommand($sql_count,$params_count)->queryOne();
				
				//数据库中没有此记录
				if($count === FALSE){
					return $response ['error'] = array ('error_code' => 4,'message' => 'can not find the goods ');					
				}
				
				//需要退货的数量大于可退货数量
				if($data['count_return'] + $count['count_return'] > $count['count']){
					return $response ['error'] = array ('error_code' => 3,'message' => 'count_return can not more than count ');
				}
			}
			
			$transaction = Yii::$app->db->beginTransaction();
			//事务处理
			try {
				foreach ($data_array as $data){
					$params_count = [
							':member_code'=>$member['member_code'],
							':order_num'=>$order_num,
							':barcode'=>$data['barcode'],
					];
					$sql_count = 'SELECT count,count_return,price FROM vcos_order_pay_log_detail WHERE member_code=:member_code AND
							order_num=:order_num AND barcode=:barcode';
					$count = Yii::$app->db->createCommand($sql_count,$params_count)->queryOne();
					
					
					//从vcos_order_pay_log_detail中找到订单，修改退货数量加$data['return_count'],修改状态为已退货
					$params_udetail = [
							':count_return'=>$data['count_return']+$count['count_return'] ,
							':status'=>2,
							':order_num'=>$order_num,
							':barcode'=>$data['barcode'],
							':member_code'=>$member['member_code'],
					];
					$sql_udetail = " UPDATE vcos_order_pay_log_detail SET count_return=:count_return, status=:status WHERE member_code=:member_code 
							AND order_num=:order_num AND barcode=:barcode ";
					Yii::$app->db->createCommand ( $sql_udetail, $params_udetail)->execute();	
					
					//计算需要退多少钱
					$price = $data['count_return'] * $data['price'] * 100;  //要退的总金额
// 					$price = $data['count_return'] * $count['price'] ;  //要退的总金额
					
					//判断 vcos_order_return_log 中是否存在记录，不存在就新建一条，存在则update
					$params_return = [
							':member_code'=>$member['member_code'],
							':order_num'=>$order_num,
					];
					$sql_return = "SELECT COUNT(*) count FROM vcos_order_return_log WHERE member_code=:member_code AND order_num=:order_num";
					$return_count = Yii::$app->db->createCommand($sql_return,$params_return)->queryScalar();
					
					
					$sql_order_pay_log = "SELECT amount,overdraft_amount FROM vcos_order_pay_log WHERE member_code=:member_code AND order_num=:order_num";
					$order_pay_log =  Yii::$app->db->createCommand($sql_order_pay_log,$params_return)->queryOne();
					
// 					if($order_pay_log === NULL){
// 						return $response ['error'] = array (
// 								'error_code' => 3,
// 								'message' => 'can not find the shop ',
// 						);
// 						die();
// 					}

					if($return_count == 0){
						//数据库中没有记录，insert
							
						if($price <= $order_pay_log['overdraft_amount']){
					
							//  if $price <= vcos_order_pay_log.overdraft_amount
							//	    vcos_order_return_log.overdraft_amount = $price
							//  endif
					
							$member['member_money'] = $member['member_money'];
							$member['curr_overdraft_amount'] -=  $price;
					
					
							$params_order_return_insert = [
									':member_code'=>$member['member_code'],
									':order_num'=>$order_num,
									':amount'=>0,
									':overdraft_amount'=>$price,
									':return_time'=>date('Y-m-d H:i:s',time()),
							];
							$sql_order_return_insert = " INSERT INTO vcos_order_return_log (member_code,order_num,amount,overdraft_amount,return_time)
									VALUES (:member_code,:order_num,:amount,:overdraft_amount,:return_time)";
							Yii::$app->db->createCommand ( $sql_order_return_insert, $params_order_return_insert )->execute ();
					
							$response ['data'] = array (
								'status' => 1,
								'amount' => 0,
								'overdraft_amount'=>$price/100,
							);
							
						}else if( $price > $order_pay_log['overdraft_amount'] ){
					
							//  if $price  > vcos_order_pay_log.overdraft_amount
							//      vcos_order_return_log.overdraft_amount = vcos_order_pay_log.overdraft_amount
							//      vcos_order_return_log.amount = $price - vcos_order_pay_log.overdraft_amount
							//   endif
					
							$member['member_money'] += ($price - $order_pay_log['overdraft_amount'] );
							$member['curr_overdraft_amount'] -=  $order_pay_log['overdraft_amount'];
								
							$params_order_return_insert = [
									':member_code'=>$member['member_code'],
									':order_num'=>$order_num,
									':amount'=> $price - $order_pay_log['overdraft_amount'],
									':overdraft_amount'=>$order_pay_log['overdraft_amount'],
									':return_time'=>date('Y-m-d H:i:s',time()),
							];
							$sql_order_return_insert = " INSERT INTO vcos_order_return_log (member_code,order_num,amount,overdraft_amount,return_time)
									VALUES (:member_code,:order_num,:amount,:overdraft_amount,:return_time)";
							Yii::$app->db->createCommand ( $sql_order_return_insert, $params_order_return_insert )->execute ();
							
							$response ['data'] = array (
									'status' => 1,
									'amount' => ($price - $order_pay_log['overdraft_amount'] ) / 100,
									'overdraft_amount'=>$order_pay_log['overdraft_amount']/100,
							);
							
						}	
					}else{
						//数据库中存在记录，update
							
						//查找 vcos_order_return_log 中的记录
						$params_return_log= [
								':member_code'=>$member['member_code'],
								':order_num'=>$order_num,
						];
						$sql_return_log = "SELECT amount,overdraft_amount FROM vcos_order_return_log WHERE member_code=:member_code AND order_num=:order_num";
						$order_return_log = Yii::$app->db->createCommand($sql_return_log,$params_return_log)->queryOne();
							
							
						if( $price <= $order_pay_log['overdraft_amount'] - $order_return_log['overdraft_amount']){
					
							//  if $price <= vcos_order_pay_log.overdraft_amount - vcos_order_return_log.overdraft_amount
							//	    vcos_order_return_log.overdraft_amount += $price
							//  endif
								
							$member['member_money'] = $member['member_money'];
							$member['curr_overdraft_amount'] -=  $price;
					
					
							$params_order_return_update = [
									':member_code'=>$member['member_code'],
									':order_num'=>$order_num,
									':overdraft_amount'=> $order_return_log['overdraft_amount'] + $price,
							];
							$sql_return_log_update  = " UPDATE vcos_order_return_log SET overdraft_amount=:overdraft_amount WHERE member_code=:member_code AND order_num=:order_num ";
							Yii::$app->db->createCommand ( $sql_return_log_update, $params_order_return_update )->execute ();
								
							$response ['data'] = array (
									'status' => 1,
									'amount' => 0,
									'overdraft_amount'=> $price/100,
							);
							
							
						}else if( $price > $order_pay_log['overdraft_amount'] - $order_return_log['overdraft_amount']){
					
							//  if $price  > vcos_order_pay_log.overdraft_amount - vcos_order_return_log.overdraft_amount
							//      vcos_order_return_log.overdraft_amount = vcos_order_pay_log.overdraft_amount
							//      vcos_order_return_log.amount = $price - (vcos_order_pay_log.overdraft_amount - vcos_order_return_log.overdraft_amount )
							//   endif
					
							$member['member_money'] += ( $price -($order_pay_log['overdraft_amount'] - $order_return_log['overdraft_amount'] ));
							$member['curr_overdraft_amount'] -=  ($order_pay_log['overdraft_amount'] - $order_return_log['overdraft_amount']) ;
					
							$params_order_return_update = [
									':member_code'=>$member['member_code'],
									':order_num'=>$order_num,
									':overdraft_amount'=> $order_pay_log['overdraft_amount'],
									':amount'=> $order_return_log['amount'] + $price - ($order_pay_log['overdraft_amount'] - $order_return_log['overdraft_amount']),
							];
							$sql_return_log_update  = " UPDATE vcos_order_return_log SET overdraft_amount=:overdraft_amount,amount=:amount WHERE member_code=:member_code AND order_num=:order_num ";
							Yii::$app->db->createCommand ( $sql_return_log_update, $params_order_return_update )->execute ();
							
							$response ['data'] = array (
									'status' => 1,
									'amount' => ( $price -($order_pay_log['overdraft_amount'] - $order_return_log['overdraft_amount'] )) / 100,
									'overdraft_amount'=>($order_pay_log['overdraft_amount'] - $order_return_log['overdraft_amount']) / 100,
							);
							
						}
					}
					
					
					//退钱
					$params_money = [
							':member_money' => $member['member_money'],
							':member_code' => $member['member_code'],
							':curr_overdraft_amount' => $member['curr_overdraft_amount']
					];
					$sql_money = ' UPDATE vcos_member SET member_money= :member_money,curr_overdraft_amount=:curr_overdraft_amount WHERE member_code=:member_code';
					Yii::$app->db->createCommand ( $sql_money, $params_money )->execute ();
				}
				
				$transaction->commit();
			}catch (EXception $e){
				$transaction->rollBack();
				return $response ['error'] = array ('code_code' => 2,'message' => 'wrong');
			}
			return $response;
		}else{
			return $response ['error'] = array ('error_code' => 1,'message' => 'member does not exist');
		}
	}
	
	public static function memberDishpay ( $code_or_passport, $amount,$order_num) {
		// $amount 商品价格
		//订餐系统下单
		$response = array ();
		if (self::checkMembershipCode ( $code_or_passport )) {
			$member = Member::find ()->select ([
					'member_money',
					'member_code',
					'passport_number',
					'member_password',
					'overdraft_limit',
					'curr_overdraft_amount'
			])->where ([
					'member_code' => $code_or_passport
			])->one ();
		} else {
			$member = Member::find ()->select ( [
					'member_money',
					'member_code',
					'passport_number',
					'member_password',
					'overdraft_limit',
					'curr_overdraft_amount'
			] )->where ( [
					'passport_number' => $code_or_passport
			] )->one ();
		}
	
		if ($member) {
			
			//判断  会员余额 + 信用额度 是否够支付账单
			if ($amount <= $member->overdraft_limit - $member->curr_overdraft_amount + $member->member_money && $member->member_money >= 0) {
	
				// if   member_money > amout
				//         member_money -= amount
	
				//会员余额够支付，直接支付
				if ($amount <= $member->member_money) {
					$money = $member->member_money - $amount;
					$params_money = [
							':member_money' => $money,
							':member_code' => $member->member_code
					];
					$sql_money = ' UPDATE vcos_member SET member_money= :member_money WHERE member_code=:member_code';
						
					// 保存到 order_pay_log
					$params_orderlog = [
							':member_code' => $member->member_code,
							':order_num' => $order_num,
							':amount' => $amount,
							':overdraft_amount' => 0,
							':pay_time' => date ( "Y-m-d H:i:s", time () )
					];
					$sql_orderlog = ' INSERT INTO vcos_order_dishpay_log (member_code,order_num,amount,overdraft_amount,pay_time )
							VALUES (:member_code,:order_num,:amount,:overdraft_amount,:pay_time)';
						
						
					$response ['data'] = array (
							'status' => 1,
							'use_amount' => $amount/100,
							'use_limit'=>0,
					);
						
				} else {
					//会员余额不够支付，使用会员余额+信用额度支付
					$member_money = $member->member_money;
					$overdraft = $amount - $member->member_money;
					$member->member_money = 0;
					$member->curr_overdraft_amount += $overdraft;
					$params_money = [
							':member_money' => $member->member_money,
							':member_code' => $member->member_code,
							':curr_overdraft_amount' => $member->curr_overdraft_amount
					];
					$sql_money = ' UPDATE vcos_member SET member_money= :member_money,curr_overdraft_amount=:curr_overdraft_amount
							WHERE member_code=:member_code';
						
					// 保存到 order_pay_log
					$params_orderlog = [
							':member_code' => $member->member_code,
							':order_num' => $order_num,
							':amount' => $member_money,
							':overdraft_amount' => $overdraft,
							':pay_time' => date ( "Y-m-d H:i:s", time () )
					];
					$sql_orderlog = ' INSERT INTO vcos_order_dishpay_log (member_code,order_num,amount,overdraft_amount,pay_time )
							VALUES (:member_code,:order_num,:amount,:overdraft_amount,:pay_time)';
						
					$response ['data'] = array (
							'status' => 1,
							'use_amount' => $member_money/100,
							'use_limit'=>$overdraft/100,
					);
				}
	
	
				Yii::$app->db->createCommand ( $sql_money, $params_money )->execute ();
				Yii::$app->db->createCommand ( $sql_orderlog, $params_orderlog )->execute ();
				Yii::$app->db->createCommand ()->insert ( 'vcos_pay_log', [
						'member_code' => $member->member_code,
						'passport_number' => $member->passport_number,
						'order_num' => $order_num,
						'amount' => $amount,
						'pay_time' => date ( 'Y-m-d H:i:s', time () )
				] )->execute ();
					
				return $response;
			} else {
				// failed
				return $response ['error'] = array (
						'error_code' => 2,
						'message' => 'not enought money '
				);
			}
		} else {
			return $response ['error'] = array (
					'error_code' => 1,
					'message' => 'member does not exist'
			);
		}
	}
	
	
	public static function dishreturn($code_or_passport,$order_num,$data,$status)
	{
		$response = array ();
		if (self::checkMembershipCode ( $code_or_passport )) {
			$member = Member::find ()->select ( [
					'member_money',
					'member_code',
					'passport_number',
					'member_password',
					'overdraft_limit',
					'curr_overdraft_amount'
			] )->where ( [
					'member_code' => $code_or_passport
			] )->one ();
		} else {
			$member = Member::find ()->select ( [
					'member_money',
					'member_code',
					'passport_number',
					'member_password',
					'overdraft_limit',
					'curr_overdraft_amount'
			] )->where ( [
					'passport_number' => $code_or_passport
			] )->one ();
		}
		if($member){
				
			if($status == 1){
				//$status == 1  ：单件退货
				//先判断传递过来的数量 + 己退货数量   是否大于 商品数量，如果大于直接返回
				$params_count = [
						':order_num'=>$order_num,
						':barcode'=>$data['barcode'],
				];
				$sql_count = 'SELECT count,count_return FROM vcos_order_pay_log_detail WHERE order_num=:order_num AND barcode=:barcode';
				$count = Yii::$app->db->createCommand($sql_count,$params_count)->queryOne();
	
				if($data['count_return'] + $count['count_return'] > $count['count']){
					return $response ['error'] = array (
							'error_code' => 2,
							'message' => 'count_return can not more than count ',
					);
					die();
				}else {
					//从vcos_order_pay_log_detail中找到订单，修改退货数量加$data['return_count'],修改状态为已退货
					//事务处理
					$transaction = Yii::$app->db->beginTransaction();
					try {
						$params_udetail = [
								':count_return'=>$data['count_return']+$count['count_return'] ,
								':status'=>2,
								':order_num'=>$order_num,
								':barcode'=>$data['barcode'],
						];
						$sql_udetail = " UPDATE vcos_order_pay_log_detail SET count_return=:count_return, status=:status WHERE order_num=:order_num
							AND barcode=:barcode ";
						Yii::$app->db->createCommand ( $sql_udetail, $params_udetail )->execute ();
	
						//退钱
						//根据传递过来的参数，找出要退货的商品，
						//根据商品价格，优先减少当前透支信誉额度，再添加member_money
						$price = $data['count_return'] * $data['price'] * 100;  //要退的总金额
	
						//判断当前透支金额是否大于或等于总金额，如果大于或等于， 当前透支金额  -=  总金额
						//如果当前透支金额小于总金额, member_money +=  (总金额- 当前透支金额), 当前透支金额 = 0
	
	
						// if      当前透支金额小于$price
						//           $price -= 当前透支金额 ， 当前透支金额 = 0 ，$member_money += $price
						// elseif  当前透支金额大于或等于总金额
						//           当前透支金额 -= $price,
							
						if($member->curr_overdraft_amount >= $price){
							$member->curr_overdraft_amount -= $price;
						}else {
							$member->member_money += ($price - $member->curr_overdraft_amount);
							$member->curr_overdraft_amount = 0;
						}
	
						$params_money = [
								':member_money' => $member->member_money,
								':member_code' => $member->member_code,
								':curr_overdraft_amount' => $member->curr_overdraft_amount
						];
						$sql_money = ' UPDATE vcos_member SET member_money= :member_money,curr_overdraft_amount=:curr_overdraft_amount WHERE member_code=:member_code';
						Yii::$app->db->createCommand ( $sql_money, $params_money )->execute ();
						$transaction->commit();
						return $response ['data'] = array (
								'code' => 1,
								'message' => 'success'
						);
	
					} catch (Exception $e) {
						$transaction->rollBack();
						return $response ['error'] = array (
								'code_code' => 1,
								'message' => 'wrong'
						);
					}
				}
			}else if($status == 2){
				//$status == 2 ：整单退货
				//根据 order_num找到所有订单,把status设置为己退货，把退货数量设置成商品数量
				//查找vcos_order_pay_log ，退钱，退信誉额度
	
				$params_count = [
						':order_num'=>$order_num,
				];
				$sql_count = 'SELECT count,count_return,barcode,status FROM vcos_order_pay_log_detail WHERE order_num=:order_num ';
				$counts = Yii::$app->db->createCommand($sql_count,$params_count)->queryAll();
	
				foreach ($counts as $count){
					//如果订单中有状态为2的，说明已经退货了
					if($count['status'] == 2){
						return $response ['error'] = array (
								'code_code' => 1,
								'message' => 'status have been change '
						);
						die();
					}
				}
	
				//事务处理
				$transaction = Yii::$app->db->beginTransaction();
				try {
					//改变状态，退货数量
					foreach ($counts as $count){
						$params_udetail = [
								':count_return'=>$count['count'],
								':order_num'=>$order_num,
								':barcode'=>$count['barcode'],
								':status'=>2,
						];
						$sql_udetail = 'UPDATE vcos_order_pay_log_detail SET count_return=:count_return,status=:status WHERE order_num=:order_num AND barcode=:barcode';
						Yii::$app->db->createCommand ( $sql_udetail, $params_udetail )->execute ();
					}
						
					$params_log = [
							':order_num'=>$order_num,
					];
					$sql_log = 'SELECT amount,overdraft_amount FROM vcos_order_pay_log WHERE order_num=:order_num ' ;
					$log = Yii::$app->db->createCommand($sql_log,$params_log)->queryOne();
						
	
					$member->member_money += $log['amount'];
					$member->curr_overdraft_amount -= $log['overdraft_amount'];
						
					if($member->curr_overdraft_amount < 0){
						$member->member_money -= $member->curr_overdraft_amount;
						$member->curr_overdraft_amount = 0;
					}
	
					$params_money = [
							':member_money' => $member->member_money,
							':member_code' => $member->member_code,
							':curr_overdraft_amount' => $member->curr_overdraft_amount
					];
					$sql_money = ' UPDATE vcos_member SET member_money= :member_money,curr_overdraft_amount=:curr_overdraft_amount WHERE member_code=:member_code';
					Yii::$app->db->createCommand ( $sql_money, $params_money )->execute ();
						
					$transaction->commit();
					return $response ['data'] = array (
							'code' => 1,
							'message' => 'success'
					);
				} catch (Exception $e) {
					$transaction->rollBack();
					return $response ['error'] = array (
							'code_code' => 1,
							'message' => 'wrong'
					);
				}
			}
		}else{
			return $response ['error'] = array (
					'error_code' => 1,
					'message' => 'member does not exist'
			);
		}
	}
	
	
	
	
	
	
	public static function getMemberBySearch($search) {
		$response = array ();
		if (is_numeric ( $search )) {
			if (self::checkMembershipCode ( $search )) {
				// member_code
				$member_array = Member::find ()->select ( [ 
						'cn_name',
						'member_code',
						'passport_number',
						'member_money',
						'member_credit',
						'overdraft_limit',
						'curr_overdraft_amount',
						'passport_number',
						'date_of_birth' 
				] )->where ( [ 
						'member_code' => $search 
				] )->all ();
			} else {
				// room_code
				// todo
				$member_array = '';
			}
		} else {
			if (preg_match ( "/^[A-Z][0-9]*/", $search )) {
				// member_passport
				$member_array = Member::find ()->select ( [ 
						'cn_name',
						'member_code',
						'passport_number',
						'member_money',
						'member_credit',
						'overdraft_limit',
						'curr_overdraft_amount',
						'passport_number',
						'date_of_birth' 
				] )->where ( [ 
						'passport_number' => $search 
				] )->all ();
			} else {
				// member_name
				$member_array = Member::find ()->select ( [ 
						'cn_name',
						'member_code',
						'passport_number',
						'member_money',
						'member_credit',
						'overdraft_limit',
						'curr_overdraft_amount',
						'passport_number',
						'date_of_birth' 
				] )->where ( [ 
						'cn_name' => $search 
				] )->all ();
			}
		}
		
		if ($member_array) {
			$cruise_line = CruiseLineService::getCruiseLineByCurrTime ();
			
			$temp_member_array = array ();
			$temp_count = count ( $member_array );
			for($i = 0; $i < $temp_count; $i ++) {
				$member = $member_array [$i];
				$temp_member_array [$i] ['member_code'] = $member->member_code;
				$temp_member_array [$i] ['cn_name'] = $member->cn_name;
				$temp_member_array [$i] ['member_money'] = $member->member_money / 100;
				$temp_member_array [$i] ['member_credit'] = $member->member_credit;
				$temp_member_array [$i] ['member_room'] = 5501; // CruiseLineService::getCruiseAddress($member->member_code, $cruise_line['trip_id'])['cabin_name_num'];
				$temp_member_array [$i] ['overdraft'] = ($member->overdraft_limit - $member->curr_overdraft_amount) / 100;
				$temp_member_array [$i] ['passport_number'] = $member->passport_number;
				$temp_member_array [$i] ['date_of_birth'] = date ( 'Y-m-d ', $member->date_of_birth );
			}
			
			$response ['data'] = $temp_member_array;
		} else {
			$response ['error'] = array (
					'error_code' => 1,
					'message' => 'member does not exist' 
			);
		}
		return $response;
	}
	public static function getMembershipByCodeAndPassport($code_or_passport) {
		$response = array ();
		
		if (self::checkMembershipCode ( $code_or_passport )) {
			// member_code
			$member = Member::find ()->select ( [ 
					'cn_name',
					'member_code',
					'passport_number',
					'member_money',
					'member_credit',
					'overdraft_limit',
					'curr_overdraft_amount',
					'passport_number',
					'date_of_birth' 
			] )->where ( [ 
					'member_code' => $code_or_passport 
			] )->one ();
		} elseif (16 == strlen ( $code_or_passport )) {
			// smart_card_number
			$member = Member::find ()->select ( [ 
					'cn_name',
					'member_code',
					'passport_number',
					'member_money',
					'member_credit',
					'overdraft_limit',
					'curr_overdraft_amount',
					'passport_number',
					'date_of_birth' 
			] )->where ( [ 
					'smart_card_number' => $code_or_passport 
			] )->one ();
		} else {
			// passport
			$member = Member::find ()->select ( [ 
					'cn_name',
					'member_code',
					'passport_number',
					'member_money',
					'member_credit',
					'overdraft_limit',
					'curr_overdraft_amount',
					'passport_number',
					'date_of_birth' 
			] )->where ( [ 
					'passport_number' => $code_or_passport 
			] )->one ();
		}
		
		if ($member) {
			$cruise_line = CruiseLineService::getCruiseLineByCurrTime ();
			$temp_member_array ['member_code'] = $member->member_code;
			$temp_member_array ['cn_name'] = $member->cn_name;
			$temp_member_array ['member_money'] = $member->member_money / 100;
			$temp_member_array ['member_credit'] = $member->member_credit;
			$temp_member_array ['member_room'] = 5501; // CruiseLineService::getCruiseAddress($member->member_code, $cruise_line['trip_id'])['cabin_name_num'];
			$temp_member_array ['overdraft'] = ($member->overdraft_limit - $member->curr_overdraft_amount) / 100;
			$temp_member_array ['passport_number'] = $member ['passport_number'];
			$temp_member_array ['date_of_birth'] = date ( 'Y-m-d ', $member ['date_of_birth'] );
			$response ['data'] = $temp_member_array;
		} else {
		    $response ['error'] = array (
					'error_code' => 1,
					'message' => 'member does not exist' 
			);
		}
		return $response;
	}
	public static function checkMembershipCode($membership_code) {
		$res_bool = false;
		$len = strlen ( $membership_code );
		if (12 == $len) {
			$prefix_value = substr ( $membership_code, 0, 10 );
			$suffix_value = substr ( $membership_code, 10 );
			$rsp_suffix = self::createCheckValue ( $prefix_value );
			if ($suffix_value == $rsp_suffix) {
				$res_bool = true;
			}
		}
		return $res_bool;
	}
	public static function createCheckValue($member_no) {
		$odd_value = 0; // 奇数
		$even_value = 0; // 偶数
		for($i = 0; $i < 10; $i ++) {
			// 下标0开始，所以奇数是被2整除
			if (0 == $i % 2) {
				$odd_value += $member_no [$i];
			} else {
				$even_value += $member_no [$i];
			}
		}
		
		if (9 < $odd_value) {
			$odd_value = self::getOneValue ( $odd_value );
		}
		
		if (9 < $even_value) {
			$even_value = self::getOneValue ( $even_value );
		}
		return $odd_value . '' . $even_value;
	}
	public static function getOneValue($value) {
		$len = strlen ( $value );
		$temp_value = 0;
		for($i = 0; $i < $len; $i ++) {
			$temp_value += substr ( $value, $i, 1 );
		}
		if (10 <= $temp_value) {
			return self::getOneValue ( $temp_value );
		} else {
			return $temp_value;
		}
	}
	
		
}