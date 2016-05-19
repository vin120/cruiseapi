<?php
	namespace app\modules\wifiservice\components;

	use Yii;
	/*
	 * To change this license header, choose License Headers in Project Properties.
	 * To change this template file, choose Tools | Templates
	 * and open the template in the editor.
	 */
	
	/**
	 * Description of MyString
	 *
	 * @author Rock.Lei
	 */
	class MyWifi {
	
		public static function topup($membership, $money)
		{
			//login
			MyCurl::vcurl(Yii::$app->params['wifi_url'],'status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);
	
			$select_name_param = 'status=manage&opt=dbcs&subopt=recordByName&dbName=usermanage_umb&&account='.$membership->member_code;
			$member_json = MyCurl::vpost(Yii::$app->params['wifi_url'],$select_name_param);
			$member_array = json_decode(iconv('GB2312', 'UTF-8', $member_json),true);
			$date_state = $member_array['success'];
			$wifi_membership = '';
			$return_value = '';
			if($date_state)
			{
				$wifi_membership = $member_array['data'];
			}else{
				$curr_date = date('y年m月d日');
				$passport_number = $membership->passport_number;
				$member_code = $membership->member_code;
				$phone = $membership->mobile_number;
				$reg_wifi_member_param = 'account='.$member_code.'&pwd='.substr($passport_number,-6).'&idUgb=1&IsStartAcc=on&limitData='.iconv('UTF-8', 'GB2312', $curr_date).'&paperType=6&paperNum='.$passport_number.'&phone='.$phone.'&status=manage&opt=dbcs&dbName=usermanage_umb&subopt=add';
	
				$member_reg_json = MyCurl::vpost(Yii::$app->params['wifi_url'],$reg_wifi_member_param);
				$member_reg_array = json_decode(iconv('GB2312', 'UTF-8', $member_reg_json),true);
				if($member_reg_array['success'])
				{
					$wifi_membership = $member_reg_array['data'];
				}
			}
			if(!empty($wifi_membership)){
				$idRec =$wifi_membership['idRec'];
				$paymoney = 'status=manage&opt=dbcs&subopt=paymoney&dbName=usermanage_umb&admin='.Yii::$app->params['wifi_login_name'].'&idRec='.$idRec.'&money='.$money;
	
				$member_pay_json = MyCurl::vpost(Yii::$app->params['wifi_url'],$paymoney);
				$member_pay_array = json_decode(iconv('GB2312', 'UTF-8', $member_pay_json),true);
	
				$return_value = $member_pay_array['success'];
			}
	
			return $return_value;
		}
	}
	