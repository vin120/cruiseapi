<?php

namespace app\components;

use Yii;
use app\models\MemberBooking;
use app\models\MemberOrder;
use app\models\MemberOrderDetail;
use app\models\MemberCart;

/**
 * Description of OrderService
 *
 * @author Rock.Lei
 */
class OrderService {
	
	public static function createOrderno()
	{
    	$my_code = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
    	
    	$order_sn = $my_code[intval(date('m'))].(intval(date('d')) < 10 ? intval(date('d')) : $my_code[(intval(date('d'))-10)]).date('Y')
    	.substr(time(),-5).substr(microtime(),2,5)
    	.sprintf('%02d', rand(0, 99));
    	
    	return $order_sn;
	}
	public static function getBookingno(){
		$booking_no = OrderService::createOrderno();
		$booking_count = MemberBooking::find()->where(['booking_no' => $booking_no])->count();
		
		if($booking_count>0){
			return OrderService::getBookingno();
		}else{
			return $booking_no;
		}
	}
	
	public static function getBookingsign($booking_sign){
		$booking_no = MemberBooking::find()->select('booking_no')->where(['booking_sign' => $booking_sign])->scalar();
	
		return $booking_no;
		
	}
		
	public static function getMemberOrderNO()
	{
		$order_serial_num = OrderService::createOrderno();
		$order_count = MemberOrder::find()->where(['order_serial_num' => $order_serial_num])->count();
		
		if($order_count>0){
			return OrderService::getMemberOrderNO();
		}else{
			return $order_serial_num;
		}
	}
	
// 	public static function getWifiOrderNO()
// 	{
		
// 		$wifi_order_number = OrderService::createOrderno();
// 		$sql = 'SELECT * FROM vcos_wifi_service_order WHERE wifi_order_number=\''.$wifi_order_number.'\'';
// 		$order = Yii::$app->db->createCommand($sql)->queryAll();
// 		$order_count = count($order);
		
// 		if($order_count>0){
// 			return OrderService::getWifiOrderNO();
// 		}else{
// 			return $wifi_order_number;
// 		}
// 	}
	
	public static function getOrderCheckNum($order_check_num){
		$member_order = MemberOrder::find()->where(['order_check_num' => $order_check_num])->one();
	
		return $member_order;
	
	}
	
	public static function createProductOrder($member,$shop,$goods_items,$order_check_num,$receiving_way,$pay_type,$address)
	{	
		$curr_time = date('Y-m-d H:i:s');
		
		$goods_id_array = array();
		foreach ($goods_items as $goods){
			$goods_id_array[] = $goods['goods_id'];
		}
		
		$goods_array = MallService::getProductByIdArray($goods_id_array);
		$total_price = 0;
		for($i=0;$i<count($goods_items);$i++)
		{
			foreach ($goods_array as $goods)
			{
				if($goods_items[$i]['goods_id'] == $goods['product_id']){
					$goods_items[$i]['unit_price'] = $goods['sale_price']*100;
					$goods_items[$i]['total_price'] = ($goods['sale_price']*$goods_items[$i]['buy_num'])*100;
					$goods_items[$i]['goods_name'] = $goods['product_name'];
					$goods_items[$i]['goods_img_url'] = $goods['product_img'];
					
					$total_price += ($goods['sale_price']*$goods_items[$i]['buy_num'])*100;
						
				}
			}
		}

		$order_num = OrderService::getMemberOrderNO();//获得订单NO.
		$memberOrder = new MemberOrder();
		$memberOrder->order_serial_num = $order_num;
		$memberOrder->membership_code = $member->member_code;
		$memberOrder->totale_price = $total_price;
		$memberOrder->pay_type = $pay_type;
		$memberOrder->order_create_time = $curr_time;
		$memberOrder->order_type = 2;
		$memberOrder->order_check_num = $order_check_num;
		$memberOrder->store_id = $shop[0]['shop_id'];
		$memberOrder->store_name = $shop[0]['shop_title'];
		$memberOrder->receiving_way = $receiving_way;
		$memberOrder->consignee_address = $address;
		$memberOrder->save();
		
		foreach ($goods_items as $goods_item)
		{
			$memberOrderDetail = new MemberOrderDetail();
			$memberOrderDetail->order_serial_num = $order_num;
			$memberOrderDetail->goods_id = $goods_item['goods_id'];
			$memberOrderDetail->goods_name = $goods_item['goods_name'];
			$memberOrderDetail->goods_img_url = $goods_item['goods_img_url'];
			$memberOrderDetail->goods_price = $goods_item['unit_price'];
			$memberOrderDetail->standard_price = $goods_item['standard_price']*100;
			$memberOrderDetail->buy_num = $goods_item['buy_num'];
			$memberOrderDetail->last_change_time =  $curr_time;
				
			$memberOrderDetail->save();
			
		}
		
		$response['order_no']=$order_num;
		$response['totale_price']=$total_price;
		$response['shop']=$shop;
		
		return $response;
		
	}
	
	public static function getMemberCartByid($member_code,$shop_id,$product_id){
		$memberCart = MemberCart::find()->where(['membership_code' => $member_code,'shop_id' => $shop_id,'goods_id' => $product_id])->one();
		
		return $memberCart;
	}
	
	public static function getMemberCart($member_code)
	{
		$memberCartArray = MemberCart::find()->where(['membership_code' => $member_code])->all();
		
		return $memberCartArray;
	}
	
	
	public static function getMemberBooking($member_code){

		$sql = ' SELECT a.booking_no , a.member_code, a.store_id, a.booking_name, a.booking_time,
				a.booking_num,a.status,a.booking_type,a.create_time as create_time,a.remark,b.restaurant_img_url  FROM vcos_member_booking a,vcos_restaurant b 
				WHERE a.member_code = \''.$member_code.'\' AND a.booking_type=1 AND a.store_id = b.restaurant_id  
				union ALL
				 SELECT a.booking_no , a.member_code, a.store_id, a.booking_name, a.booking_time,
				a.booking_num,a.status,a.booking_type,a.create_time as create_time,a.remark,c.ls_img_url  FROM vcos_member_booking a,vcos_lifeservice c
				WHERE a.member_code = \''.$member_code.'\' AND a.booking_type=2 AND a.store_id = c.ls_id ORDER BY create_time DESC ';
		
		
		$memberBookingArray = Yii::$app->db->createCommand($sql)->queryAll();

		$tmpArray = array();
		
		foreach ($memberBookingArray as $memberBooking ){
			if(strtotime($memberBooking['booking_time']) < time() && ($memberBooking['status']==1 || $memberBooking['status']==2)){
				$sql_update = " UPDATE vcos_member_booking SET status=3 WHERE booking_no ='{$memberBooking['booking_no']}'";
				Yii::$app->db->createCommand($sql_update)->execute();
				$memberBooking['status'] = 3;
				$tmpArray[] = $memberBooking;
			}else{
				$tmpArray[] = $memberBooking;
			}
		}

// 		$memberBookingArray = MemberBooking::find()
// 		->select('booking_no,member_code,store_id,booking_name,booking_time,
// 				booking_num,status,booking_type,create_time,remark')
// 		->where(['member_code' => $member_code])->orderBy('create_time DESC')->all();

// 		$restaurantArray = Yii::$app->db->createCommand($sql)->queryAll();
				
		return $tmpArray;
	}
	

	public static function DelShopcartAfterOrder($data_array,$member)
	{
		foreach($data_array as $data){
			$store_id = $data['store_id'];
			$goods_items = $data['goods_items'];
			foreach($goods_items as $item){
				$goods_id = $item['goods_id'];
				$params = [':member_code'=>$member->member_code,':store_id'=>$store_id,':goods_id'=>$goods_id];
				$sql = " DELETE FROM vcos_member_cart WHERE membership_code =:member_code AND shop_id=:store_id AND goods_id=:goods_id ";
				Yii::$app->db->createCommand($sql,$params)->execute();
			}
		}
	}
	
	
	public static function multipleOrderPayment($member,$order_array,$pay_type,$iscart,$data_array){
		$total_price = 0;
		$order_no_array = array();
		foreach ($order_array as $order){
			$total_price += $order['totale_price'];
			$order_no_array[] = $order['order_no'];
		}
		
		$order_no_str_sql_in = '\''.join('\',\'', $order_no_array).'\'';
		
		$curr_time= '\''.date('Y-m-d H:i:s').'\'';
		//$order_status = 1;
		
		$response = array();
		if($pay_type != 3){
			if($pay_type == 1){
				//卡支付
				if($total_price <= $member->overdraft_limit - $member->curr_overdraft_amount + $member->member_money && $member->member_money >= 0){
					//先判断    会员余额 + 信用额度 是否够支付账单
					if($total_price <= $member->member_money){
						// 会员余额够支付，直接支付
						$update_sql = 'UPDATE vcos_member SET member_money='.($member->member_money-$total_price).' WHERE member_code=\''.$member->member_code.'\'';
					}else{
						//会员余额不够支付，使用会员余额+信用额度支付
						$draft = $total_price - $member->member_money;
						$curr_overdraft_amount = $member->curr_overdraft_amount + $draft;
						$update_sql = 'UPDATE vcos_member SET member_money=0 , curr_overdraft_amount='.$curr_overdraft_amount.'  WHERE member_code=\''.$member->member_code.'\'';
					}
					$bool_value = Yii::$app->db->createCommand($update_sql)->execute();
					if(!$bool_value){
						$response['error'] = array('error_code'=>2,'message'=>'Membership amount update fail');
					}else{
						//更新订单信息
						$update_order_sql = 'UPDATE vcos_member_order SET pay_type='.$pay_type.',pay_time='.$curr_time.',order_status=1 WHERE order_serial_num IN ('.$order_no_str_sql_in.') AND membership_code=\''.$member->member_code.'\'';
						Yii::$app->db->createCommand($update_order_sql)->execute();
					
						if($iscart == 1){
							//从购物车下单，下完单后删除购物车内容
							OrderService::DelShopcartAfterOrder($data_array,$member);
						}
						$response['data'] = array('code'=>1,'message'=>'Pay for success');
					}
				}else{
					$response['error'] = array('error_code'=>1,'message'=>'Membership insufficient balance');
				}
			}else if ($pay_type == 2){
				//挂帐
				if(($total_price <= $member->overdraft_limit - $member->curr_overdraft_amount) ){
					$draft = $member->curr_overdraft_amount + $total_price;
					$update_sql = 'UPDATE vcos_member SET curr_overdraft_amount='.$draft.' WHERE member_code=\''.$member->member_code.'\'';
					$bool_value = Yii::$app->db->createCommand($update_sql)->execute();
					if(!$bool_value){
						$response['error'] = array('error_code'=>2,'message'=>'Membership amount update fail');
					}else{
						//更新订单信息
						$update_order_sql = 'UPDATE vcos_member_order SET pay_type='.$pay_type.',pay_time='.$curr_time.',order_status=1 WHERE order_serial_num IN ('.$order_no_str_sql_in.') AND membership_code=\''.$member->member_code.'\'';
						Yii::$app->db->createCommand($update_order_sql)->execute();
						if($iscart == 1){
							//从购物车下单，下完单后删除购物车内容
							OrderService::DelShopcartAfterOrder($data_array,$member);
						}
						$response['data'] = array('code'=>1,'message'=>'Pay for success');
					}
				}else{
					$response['error'] = array('error_code'=>3,'message'=>'overdraft limit was less than');
				}
			}
		}else{
			//到付
			//更新订单信息
			$update_order_sql = 'UPDATE vcos_member_order SET pay_type='.$pay_type.',pay_time='.$curr_time.',order_status=0 WHERE order_serial_num IN ('.$order_no_str_sql_in.') AND membership_code=\''.$member->member_code.'\'';
			Yii::$app->db->createCommand($update_order_sql)->execute();
			if($iscart == 1){
				//从购物车下单，下完单后删除购物车内容
				OrderService::DelShopcartAfterOrder($data_array,$member);
			}
			$response['data'] = array('code'=>1,'message'=>'Pay for success');
		}
		return $response;
	}
	
	
	public static function getMemberOrder($member_code,$order_status,$bool_count=true,$page=1,$page_size=20)
	{
		//返回的结果不包含状态为7的条件
		$sql_where_value = 'FROM vcos_member_order WHERE membership_code = \''.$member_code.'\' AND order_status != 7 ';
		//order_state : 0等待付款,1卖家发货中,2等待收货中,3完成，4取消订单，5申请退款,6.交易失败,7.删除订单
		switch ($order_status)
		{
			//todo
			case 0 :
// 				$sql_where_value .= ' AND order_status = 0 ';
				break;
			case 1 :
// 				$sql_where_value .= ' AND order_status = 1 ';
				break;
			case 2 : 
// 				$sql_where_value .= ' AND order_status = 2 ';
				break;
			case 3 : 
// 				$sql_where_value .= ' AND order_status = 3 ';
				break;
			case 4 :
// 				$sql_where_value .= ' AND order_status = 4 ';
				break;
			case 5 :
// 				$sql_where_value .= ' AND order_status = 5 ';
				break;
			case 6 : 
// 				$sql_where_value .= ' AND order_status = 6 ';
				break;
			case 7 :
// 				$sql_where_value .= ' AND order_status = 7 ';
				break;
		}
		
		if($bool_count){
			$sql_value = 'SELECT COUNT(*)' .$sql_where_value;
			$count = Yii::$app->db->createCommand($sql_value)->queryScalar();
			
			return $count;
		}else{
			$sql_value = 'SELECT * '.$sql_where_value. 'ORDER BY order_create_time DESC LIMIT '.(($page-1)*$page_size).' ,'.$page_size;
			$order_array = Yii::$app->db->createCommand($sql_value)->queryAll();
				
			return $order_array;
		}
	}
	
	public static function getOrderByNo($order_serial_num){
		$sql_value = 'SELECT * FROM vcos_member_order WHERE order_serial_num=\''.$order_serial_num.'\'';
		$order = Yii::$app->db->createCommand($sql_value)->queryOne();
		
		if( 1 == $order['order_type']){
			//1餐厅,2免税店,3网络服务，4电话服务
			$param_tel = [':restaurant_id'=>$order['store_id']];
			$sql_tel = ' SELECT restaurant_tel FROM vcos_restaurant WHERE restaurant_id = :restaurant_id';
			$tel = Yii::$app->db->createCommand($sql_tel,$param_tel)->queryOne()['restaurant_tel'];
		}
		else if(2 == $order['order_type']){
			//1餐厅,2免税店,3网络服务，4电话服务
			$param_tel = [':shop_id'=>$order['store_id']];
			$sql_tel = ' SELECT shop_tel FROM vcos_shop WHERE shop_id = :shop_id ';
			$tel = Yii::$app->pdb->createCommand($sql_tel,$param_tel)->queryOne()['shop_tel'];
		}
		else {
			$tel = '';
		}
		
		$order['tel'] = $tel;
		return $order;
	}
	
	public static function getMemberOrderDetail($order_no_array){
		$order_detail_array = array();
		if (!empty($order_no_array))
		{
			$sql_in_value = '\''.join('\',\'', $order_no_array).'\'';
			
			$sql_value = 'SELECT order_serial_num,goods_id,goods_name,goods_img_url,(goods_price/100) as goods_price,(standard_price/100) as standard_price,buy_num,sub_goods_state 
					FROM vcos_member_order_detail WHERE order_serial_num IN ('.$sql_in_value.')';
			$order_detail_array = Yii::$app->db->createCommand($sql_value)->queryAll();
		}
		
		return $order_detail_array;
	}
	
	
	public static function delMembercartByid($id)
	{
		if($id)
		{
			$sql = 'DELETE FROM `vcos_member_cart` WHERE id = '.$id.' LIMIT 1 ';
			Yii::$app->db->createCommand($sql)->execute();
		}
	}
	
	
	public static function setShopcart($member,$shop_id,$product_id,$number,$type)
	{
		$memberCart = OrderService::getMemberCartByid($member->member_code, $shop_id, $product_id);
		$count = 0;
		if(!empty($memberCart))
		{
			//if $type = 1 means add shopcart ,2 means delete shopcart
			if(1 == $type)
			{							
				$memberCart->num += isset($number) ? $number : 1 ;
			}
			elseif(2 == $type)
			{
				$memberCart->num -= 1;
			}
			$count = $memberCart->save();
			if(0 == $memberCart->num)
			{
				//delete data
				OrderService::delMembercartByid($memberCart->id);
			}
		}
		else
		{
			if(1 == $type)
			{
				$memberCart = new MemberCart();
				$memberCart->membership_code = $member->member_code;
				$memberCart->shop_id = $shop_id;
				$memberCart->goods_id = $product_id;
				$memberCart->num =$number;
				$memberCart->add_time = date('Y-m-d H:i:s');
				$memberCart->cart_type=2;
				$count = $memberCart->save();
			}
			else
			{
				return $response['error'] = array('error_code'=>1,'message'=>'data can not be empty');
			}
			
		}
		if($count)
		{
			$response['data'] = array('code'=>1,'message'=>'success');
			return $response['data'];
			
		}
	}
	
}