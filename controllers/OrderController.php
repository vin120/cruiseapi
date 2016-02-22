<?php

namespace app\controllers;

use Yii;
use app\components\MemberService;
use app\components\RestaurantService;
use app\components\LifeserviceService;
use app\components\OrderService;
use app\models\MemberBooking;
use app\models\MemberOrder;
use app\models\MemberOrderDetail;
use app\models\MemberCart;
use yii\base\Object;
use yii\rest\UpdateAction;
use app\components\MallService;


class OrderController extends MyActiveController
{
	public function actionCreatebooking() {
		$sign = isset($_POST['sign']) ? $_POST['sign'] : '';
		$booking_time = isset($_POST['booking_time']) ? $_POST['booking_time'] : '';
		$booking_name = isset($_POST['booking_name']) ? $_POST['booking_name'] : '';
		$booking_num = isset($_POST['booking_num']) ? $_POST['booking_num'] : '';
		$booking_type = isset($_POST['booking_type']) ? $_POST['booking_type'] : '';
		$store_id = isset($_POST['store_id']) ? $_POST['store_id'] : '';
		$my_lang = isset($_POST['my_lang']) ? $_POST['my_lang'] : 'zh_cn';
		
		$booking_sign = md5($sign.$booking_time.$booking_num.$booking_type.$store_id);
		
		$response = array();
		if(!empty($sign) && !empty($booking_time) && !empty($booking_num) && !empty($booking_type) && !empty($store_id)){
			$booking_no = OrderService::getBookingsign($booking_sign);
			if(empty($booking_no)){
				$booking_no = OrderService::getBookingno();
				$member = MemberService::getMemberbysign($sign);
				if(!empty($member)){
// 					直接传预定名不需要再去数据库调用，就不用再执行以下代码
					$store_item = '';
					switch ($booking_type)
					{
						case 1:
							$store_item = RestaurantService::getRestaurantById($store_id,$my_lang);
							break;
						default:
							$store_item = LifeserviceService::getLifeserviceById($store_id,$my_lang);
							break;
					}
					if(!empty($store_item)){
							$memberBooking = new MemberBooking();
							$memberBooking->booking_no = $booking_no;
							$memberBooking->member_code = $member->member_code;
							$memberBooking->booking_name = $booking_name;
							$memberBooking->booking_time = $booking_time;
							$memberBooking->booking_num = $booking_num;
							$memberBooking->booking_type = $booking_type;
							$memberBooking->store_id = $store_id;
							$memberBooking->create_time = date('Y-m-d H:i:s');
							$bool_value = $memberBooking->save();

							if($bool_value){
								$response['data'] = 1;
							}else{
								$response['data'] = -1;
							}
					}else{
						$response['error'] = array('error_code'=>1,'message'=>'store does not exist,store_id and booking_type error.');
					}
				
				}else{
					$response['error'] = array('error_code'=>1,'message'=>'Member does not exist');
				}
			}else {
				$response['error'] = array('error_code'=>2,'message'=>'Please do not submit a duplicate');
			}
		}else {
			$response['error'] = array('error_code'=>1,'message'=>'sign、booking_time、booking_num、booking_type、store_id can not be empty');
		}
		
		return $response;
	}
	
	public function actionCreateorder()
	{
// 		$disc_json = '{"sign":"123456","order_type":"1","store_id":"123456","store_name":"vip西餐厅","order_time":"201405011234121","address":"地址","totle_price":"2000","remark":"备注","goods_items":[{"goods_id":"25","goods_name":"青岛大虾","goods_price":"3800","buy_num":"2","unit":"份"},{"goods_id":"27","goods_name":"啤酒","goods_price":"40000","buy_num":"2","unit":"份"}]}';
//     	$disc_array = json_decode(iconv('GB2312', 'UTF-8', $disc_json),true);
		
		$data = $_POST['data'];
		$my_lang = isset($_POST['my_lang']) ? $_POST['my_lang'] : 'zh_cn';

		
		$data_array = json_decode($data,true);
		$response = array();
		if(!empty($data_array)){
			$total_price = 0;
			
			$sign=$data_array['sign'];
			$order_type=isset($data_array['order_type']) ? $data_array['order_type'] : '1';//1餐厅,2免税店,3网络服务，4电话服务
			$store_id=isset($data_array['store_id']) ? $data_array['store_id'] : '';
			$delivery_time = isset($data_array['order_time']) ? $data_array['order_time'] : '';
			$pay_type = isset($data_array['pay_type']) ? $data_array['pay_type'] : '2';	//1余额，2挂账，3到付
			$additional_cost = isset($data_array['additional_cost']) ? $data_array['additional_cost']*100 : '0';
			$additional_cost_info = isset($data_array['additional_cost_info']) ? $data_array['additional_cost_info'] : '';
			$curr_time = date('Y-m-d H:i:s');
			$order_time = time();
			//获得所有商品ID
			$goods_items=$data_array['goods_items'];
			$goods_id_array = array();
			foreach ($goods_items as $goods){
				$goods_id_array[] = $goods['goods_id'];
			}
			
			//检查是否重复提交
			$order_check_num = md5($sign.$order_type.$store_id.join(',', $goods_id_array).$order_time);
			$myMemberOrder = OrderService::getOrderCheckNum($order_check_num);
			
			if(empty($myMemberOrder)){
				$goods_array = array();
				$member = MemberService::getMemberbysign($sign);
				if(!empty($member)){
					//直接传店铺名不需要再去数据库调用，就不用再执行以下代码
					$store_item = '';
				
					switch ($order_type)
					{
						case 1: //1余额，2挂账，3到付
							//$store_item = RestaurantService::getRestaurantById($store_id,$my_lang);
							$goods_array = RestaurantService::getFoodById($goods_id_array,$my_lang);
				
							for($i=0;$i<count($goods_items);$i++)
							{
								foreach ($goods_array as $goods)
								{
									if($goods_items[$i]['goods_id'] == $goods['food_id']){
										$goods_items[$i]['unit_price'] = $goods['food_price'];
										$goods_items[$i]['total_price'] = $goods['food_price']*$goods_items[$i]['buy_num'];
										$goods_items[$i]['goods_name'] = $goods['food_title'];
										$goods_items[$i]['goods_img_url'] = $goods['food_img_url'];
										$total_price += $goods['food_price']*$goods_items[$i]['buy_num'];
									}
								}
							}
							break;
						default:
							break;
					}
				
					$order_num = OrderService::getMemberOrderNO();//获得订单NO.
				
					$memberOrder = new MemberOrder();
					$memberOrder->order_serial_num = $order_num;
					$memberOrder->membership_code = $member->member_code;
					$memberOrder->totale_price = $total_price + $additional_cost;
					$memberOrder->pay_type = $pay_type;
					$memberOrder->order_create_time = $curr_time;
					$memberOrder->order_type = $order_type;
					$memberOrder->order_check_num = $order_check_num;
					$memberOrder->store_id = isset($data_array['store_id']) ? $data_array['store_id'] : '';
					$memberOrder->store_name = isset($data_array['store_name']) ? $data_array['store_name'] : '';
					$memberOrder->consignee_address = isset($data_array['address']) ? $data_array['address'] : '';
					$memberOrder->additional_cost = $additional_cost;
					$memberOrder->delivery_time = $delivery_time;
					$memberOrder->additional_cost_info = $additional_cost_info;
					$memberOrder->save();
				
					foreach ($goods_items as $goods_item)
					{
						$memberOrderDetail = new MemberOrderDetail();
						$memberOrderDetail->order_serial_num = $order_num;
						$memberOrderDetail->goods_id = $goods_item['goods_id'];
						$memberOrderDetail->goods_name = $goods_item['goods_name'];
						$memberOrderDetail->goods_img_url = $goods_item['goods_img_url'];
						$memberOrderDetail->goods_price = $goods_item['unit_price'];
						$memberOrderDetail->buy_num = $goods_item['buy_num'];
						$memberOrderDetail->last_change_time =  $curr_time;
							
						$memberOrderDetail->save();
					}
				
					$response['data']['order_no']=$order_num;
					$response['data']['store_name']=$memberOrder->store_name;
					$response['data']['totale_price']=$total_price/100;
					$response['data']['member_amount']=$member->member_money;
				}else {
					$response['error'] = array('error_code'=>1,'message'=>'Member does not exist');
				}
			}else{
				$response['error'] = array('error_code'=>2,'message'=>'Please do not submit a duplicate');
			}
			
		}else {
			$response['error'] = array('error_code'=>1,'message'=>'data can not be empty');
		}
		
		return $response;
	}
	
	public function actionOrderpayment()
	{
		$sign = isset($_POST['sign']) ? $_POST['sign'] : '12';
		$pay_type = isset($_POST['pay_type']) ? $_POST['pay_type'] : '2';
		$order_serial_num = isset($_POST['order_serial_num']) ? $_POST['order_serial_num'] : 'KR2015163355724125';
		
		if(!empty($sign) && !empty($pay_type) && !empty($order_serial_num)){
			$member = MemberService::getMemberbysign($sign);

			$member_order = MemberOrder::find()->where(['membership_code'=>$member->member_code,'order_serial_num' => $order_serial_num])->one();
			if(!empty($member_order)){
				if(0 == $member_order->order_status){

					if(strtotime("$member_order->order_create_time +30 min") >= time()){
						$member_order->pay_type = $pay_type;
						$member_order->pay_time = date('Y-m-d H:i:s');
						$member_order->order_status = 1;	//0等待付款,1卖家发货,2等待收获,3完成，4取消订单，5，申请退款,6.交易失败,7.删除订单
						$member_order->user_operation = 1;  //'0提交订单,1支付3评价4取消订单，5申请退款,7.删除订单',
						if($pay_type != 3){
							if($pay_type == 1){
								//卡支付
								if($member_order->totale_price <= $member->overdraft_limit - $member->curr_overdraft_amount + $member->member_money && $member->member_money >= 0){
									//先判断    会员余额 + 信用额度 是否够支付账单
									if($member_order->totale_price <= $member->member_money){
										// 会员余额够支付，直接支付
										$update_sql = 'UPDATE vcos_member SET member_money='.($member->member_money-$member_order->totale_price).' WHERE member_code=\''.$member->member_code.'\'';

									}else{
										//会员余额不够支付，使用会员余额+信用额度支付
										$draft = $member_order->totale_price - $member->member_money;
										$curr_overdraft_amount = $member->curr_overdraft_amount + $draft;
										$update_sql = 'UPDATE vcos_member SET member_money=0 , curr_overdraft_amount='.$curr_overdraft_amount.'  WHERE member_code=\''.$member->member_code.'\'';					
									}
									$bool_value = Yii::$app->db->createCommand($update_sql)->execute();
									if(!$bool_value){
										$response['error'] = array('error_code'=>2,'message'=>'Membership amount update fail');
									}else{
										$member_order->save();
										$response['data'] = array('code'=>1,'message'=>'Pay for success');
									}
								}else{
									$response['error'] = array('error_code'=>1,'message'=>'not enought money');
								}							
							}else{
								//挂帐
								if(($member->overdraft_limit - $member->curr_overdraft_amount) < $member_order->totale_price){
									$response['error'] = array('error_code'=>3,'message'=>'overdraft limit was less than');
								}else{
									$draft = $member->curr_overdraft_amount + $member_order->totale_price;
									$update_sql = 'UPDATE vcos_member SET curr_overdraft_amount='.$draft.' WHERE member_code=\''.$member->member_code.'\'';
									$bool_value = Yii::$app->db->createCommand($update_sql)->execute();
									if(!$bool_value){
										$response['error'] = array('error_code'=>2,'message'=>'Membership amount update fail');
									}else{
										$member_order->save();
										$response['data'] = array('code'=>1,'message'=>'Pay for success');
									}
								}
							}
						}else{
							//货到付款
							$member_order->order_status = 0;
							$member_order->user_operation = 0;
							$member_order->save();
							$response['data'] = array('code'=>1,'message'=>'Pay for success');
						}
						
					}else{
						$response['error'] = array('error_code'=>4,'message'=>'Membership order expired');
					}
				}else{
					$response['error'] = array('error_code'=>5,'message'=>'Membership order Payment has been');
				}
			}else {
				$response['error'] = array('error_code'=>6,'message'=>'Membership order does not exist');
			}
		}
		
		return $response;
	}
	
	public function actionCreateproductorder()
	{
		$data = isset($_POST['data']) ? $_POST['data'] : '';
// 		$data = '{"sign":"12","order_type":"2","pay_type":"1","is_pay":"1","order_time":"201405011234121","receiving_way":"2",
// 						"address":"地址","totle_price":"2000","remark":"备注",
// 						"order_items":[
// 							{"store_id":"25","store_name":"vip西餐厅",
// 								"goods_items":[{"goods_id":"115","buy_num":"1","standard_price":"100"},{"goods_id":"53","buy_num":"2","standard_price":"100"}]
// 							},
// 							{"store_id":"17","store_name":"vip",
// 								"goods_items":[{"goods_id":"71","buy_num":"3","standard_price":"100"},{"goods_id":"73","buy_num":"4","standard_price":"100"}]
// 							}
// 						]
// 				}';
		$data_array = json_decode($data,true);
		
		$order_array = array();
		$response = array();
		if(!empty($data_array)){
			$total_price = 0;
				
			$sign=$data_array['sign'];
			$order_type=isset($data_array['order_type']) ? $data_array['order_type'] : '2';//1餐厅,2免税店,3网络服务，4电话服务
			$pay_type=isset($data_array['pay_type']) ? $data_array['pay_type'] : '2'; //1余额，2挂账，3到付
			$sign_order_time = isset($data_array['order_time']) ? $data_array['order_time'] : '';
 			$receiving_way =  isset($data_array['receiving_way']) ? $data_array['receiving_way'] : '0';//0没有值,1自提，2送货到房间
 			$address = isset($data_array['address']) ? $data_array['address'] : '';
 			$iscart = isset($data_array['iscart']) ? $data_array['iscart'] : 0;  //0为直接下单，1为从购物车下单
			$curr_time = date('Y-m-d H:i:s');

			//检查是否重复提交
			$order_check_num = md5($data);
			$myMemberOrder = OrderService::getOrderCheckNum($order_check_num);
			if(empty($myMemberOrder)){
				$member = MemberService::getMemberbysign($sign);
				if(!empty($member)){
					if (isset($data_array['order_items'])){
						$order_items = $data_array['order_items'];
						foreach ($order_items as $my_order){
							$store_id = $my_order['store_id'];
							$goods_items = $my_order['goods_items'];
							$shop = MallService::getShopByActivityId(array($store_id));
							$order_array[] = OrderService::createProductOrder($member, $shop, $goods_items, $order_check_num,$receiving_way,$pay_type,$address);
						}
						$response = OrderService::multipleOrderPayment($member, $order_array, $pay_type,$iscart,$data_array['order_items']);
	
					}else{
						$response['error'] = array('error_code'=>1,'message'=>'order items does not exist');
					}
					
				}else{
					$response['error'] = array('error_code'=>1,'message'=>'Member does not exist');
				}
			}else{
				$response['error'] = array('error_code'=>2,'message'=>'Please do not submit a duplicate');
			}
		}else {
			$response['error'] = array('error_code'=>1,'message'=>'data can not be empty');
		}
		return $response;
	}
	
	public function actionAddshopcart()
	{
		$sign = isset($_POST['sign']) ? $_POST['sign'] : '12';
		$shop_id = isset($_POST['shop_id']) ? $_POST['shop_id'] : '21';
		$product_id = isset($_POST['product_id']) ? $_POST['product_id'] : '19';
		$number = isset($_POST['number']) ? $_POST['number'] : '1';
		$type = 1; 			//$type : 1 means add shopcart ,2 means delete shopcart 
		$response = array();
		if(!empty($sign) && !empty($shop_id) && !empty($product_id)){
			$member = MemberService::getMemberbysign($sign);
			if(!empty($member)){
				$response = OrderService::setShopcart($member,$shop_id,$product_id,$number,$type);
			}
			else{
				$response['error'] = array('error_code'=>1,'message'=>'Member does not exist');
			}
		}
		else{
			$response['error'] = array('error_code'=>1,'message'=>'data can not be empty');
		}
		
		return $response;
		
	}
	
	
	public function actionDelshopcart()
	{
		$sign = isset($_POST['sign']) ? $_POST['sign'] : '12';
		$shop_id = isset($_POST['shop_id']) ? $_POST['shop_id'] : '21';
		$product_id = isset($_POST['product_id']) ? $_POST['product_id'] : '19';
		$number = isset($_POST['number']) ? $_POST['number'] : '1';
		$type = 2; 			//$type : 1 means add shopcart ,2 means delete shopcart 
		$response = array();
		if(!empty($sign) && !empty($shop_id) && !empty($product_id)){
			$member = MemberService::getMemberbysign($sign);
			if(!empty($member)){
				$response = OrderService::setShopcart($member,$shop_id,$product_id,$number,$type);
			}
			else{
				$response['error'] = array('error_code'=>1,'message'=>'Member does not exist');
			}
		}
		else{
			$response['error'] = array('error_code'=>1,'message'=>'data can not be empty');
		}
		
		return $response;
		
	}
	
	
	public function actionDelallshopcart()
	{
		$tmp_data = $_POST['data'];
		$data = json_decode($tmp_data,true);
		if($data){
			$goods_id = '';
			$shop_id = '';
			$sign = $data['sign'];
				
			$response = array();
			if(!empty($sign)){
				$member = MemberService::getMemberbysign($sign);
				if(!empty($member)){
					foreach($data['data'] as $key=>$row){
						$goods_id = $row['goods_id'];
						$shop_id = $row['shop_id'];
						$memberCart = OrderService::getMemberCartByid($member->member_code,$shop_id, $goods_id);
						OrderService::delMembercartByid($memberCart->id);
					}
					$response['data'] = array('code'=>1,'message'=>'delete shopcart success');
				}
				else{
					$response['error'] = array('error_code'=>1,'message'=>'Member does not exist');
				}
			}
			else{
				$response['error'] = array('error_code'=>1,'message'=>'data can not be empty');
			}
		}
		else{
			$response['error'] = array('error_code'=>1,'message'=>'data wrong ');
		}
	
		return $response;
	}
	
	
	
	public function actionMyshopcart()
	{
		$sign = isset($_POST['sign']) ? $_POST['sign'] : '12';
		$response = array();
		if(!empty($sign)){
			$member = MemberService::getMemberbysign($sign);
			if(!empty($member)){
				$memberCartArray = OrderService::getMemberCart($member->member_code);
				$shop_id_array =  array();
				$product_id_array = array();
				foreach ($memberCartArray as $memberCart){
					$shop_id_array[$memberCart->shop_id] = 0;
					$product_id_array[] = $memberCart->goods_id;
				}
				$shop_id_array = array_keys($shop_id_array);
				
				$shop_array = MallService::getShopByActivityId($shop_id_array);
				
				
				$product_array = MallService::getProductByIdArray($product_id_array);
				
				$temp_product_count = count($product_array);
				for($p=0;$p<$temp_product_count;$p++){
					$proudct_id = $product_array[$p]['product_id'];
					foreach ($memberCartArray as $memberCart){
						if($proudct_id == $memberCart->goods_id){
							$product_array[$p]['num'] = $memberCart->num;
							$product_array[$p]['add_time'] = $memberCart->add_time;
							$product_array[$p]['cart_type'] = $memberCart->cart_type;
						}
					}
				}
				
				$shop_count = count($shop_array);
				for($i=0;$i<$shop_count;$i++){
					$shop_id = $shop_array[$i]['shop_id'];
					foreach ($product_array as $product){	
						if($shop_id == $product['shop_id']){
							unset($product['shop_id']);
							$shop_array[$i]['cart_items'][]=$product;
						}
					}
				}				
				$response = $shop_array;
				
			}else{
				$response['error'] = array('error_code'=>1,'message'=>'Member does not exist');
			}
		}else{
			$response['error'] = array('error_code'=>1,'message'=>'data can not be empty');
		}
		return $response;
	}
	
	public static function actionCancelorder()
	{
		$sign = isset($_POST['sign']) ? $_POST['sign'] : '';
		$order_serial_num = isset($_POST['order_serial_num']) ? $_POST['order_serial_num'] : '';
		$remark = isset($_POST['remark']) ? $_POST['remark'] : '';
		
		$response = array();
		if(!empty($sign) && !empty($order_serial_num)){
			$member = MemberService::getMemberbysign($sign);
			if(!empty($member)){
				$order = MemberOrder::find()->where(['order_serial_num' => $order_serial_num,'membership_code'=>$member->member_code,'order_status'=>0])->one();
				if(!empty($order)){
					$order->order_status = 4;
					$order->user_operation = 4;
					$order->remark =$remark;
					$count = $order->save();
					if($count){
						$response['data'] = array('code'=>1,'message'=>'cancel order success');
					}else{
						$response['error'] = array('error_code'=>3,'message'=>'order no cancel fail');
					}
					
				}else{
					$response['error'] = array('error_code'=>2,'message'=>'order no does not exist');
				}
			}else{
				$response['error'] = array('error_code'=>1,'message'=>'Member does not exist');
			}
		}else{
			$response['error'] = array('error_code'=>0,'message'=>'order serial num can not be empty');
		}
		
		return $response;
	}
	
	public static function actionDelorder()
	{
		$sign = isset($_POST['sign']) ? $_POST['sign'] : '';
		$order_serial_num = isset($_POST['order_serial_num']) ? $_POST['order_serial_num'] : '';
		
		$response = array();
		if(!empty($sign) && !empty($order_serial_num)){
			$member = MemberService::getMemberbysign($sign);
			if(!empty($member)){
				$order = MemberOrder::find()->where(['order_serial_num' => $order_serial_num,'membership_code'=>$member->member_code,'order_status'=>[3,4,6]])->one();
				if(!empty($order)){
					$order->order_status = 7;
					$order->user_operation = 7;
					$count = $order->save();
					if($count){
						$response['data'] = array('code'=>1,'message'=>'Delete order success');
					}else{
						$response['error'] = array('error_code'=>3,'message'=>'order no delete fail');
					}	
				}else{
					$response['error'] = array('error_code'=>2,'message'=>'order no does not exist');
				}
			}else{
				$response['error'] = array('error_code'=>1,'message'=>'Member does not exist');
			}
		}else{
			$response['error'] = array('error_code'=>0,'message'=>'sign or order serial num can not be empty');
		}
		
		return $response;
	}
	
	public static function actionRefund()
	{
		$sign = isset($_POST['sign']) ? $_POST['sign'] : '';
		$order_serial_num = isset($_POST['order_serial_num']) ? $_POST['order_serial_num'] : '';
		
		$response = array();
		if(!empty($sign) && !empty($order_serial_num)){
			$member = MemberService::getMemberbysign($sign);
			if(!empty($member)){
				$order = MemberOrder::find()->where(['order_serial_num' => $order_serial_num,'membership_code'=>$member->member_code,'order_status'=>[1,2]])->one();
				if(!empty($order)){
					$order->order_status = 5;
					$order->user_operation = 5;
					$count = $order->save();
					if($count){
						$response['data'] = array('code'=>1,'message'=>'Refund order success');
					}else{
						$response['error'] = array('error_code'=>3,'message'=>'order no refund fail');
					}
				}else{
					$response['error'] = array('error_code'=>2,'message'=>'order no does not exist');
				}
			}else{
				$response['error'] = array('error_code'=>1,'message'=>'Member does not exist');
			}
		}else{
			$response['error'] = array('error_code'=>0,'message'=>'sign or order serial num can not be empty');
		}
		
		return $response;
	}
	
	
}