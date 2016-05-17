<?php

class ServiceController_back extends Controller
{
        public $wifi_server_conf_array;
        
        public function init()
        {
            parent::init();

            $this->wifi_server_conf_array = parse_ini_file("wifi_conf.ini",true);
        }
        
        /**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
        public function actionIndex()
        {
            $netCategoryArray = NetCommunicationCategory::model()->findAll(array(
                'condition' => 'state=1'
            ));
            
            $this->render('net_category_item', array('netCategoryArray'=>$netCategoryArray));
        }
        public function actionIndex_back(){
            $membership = MembershipService::getMembership($this->membership_id);
            $this->render('wifi_index', array('membership'=>$membership));
        }
	
	public function actionWifilist()
	{
            $wifiConnectModelArray = MywifiService::getWifiConnectLog($this->membership_id);
            if(isset($wifiConnectModelArray[0])){
                    $curr_connect= $wifiConnectModelArray[0];
                    $curr_wifi_time = ($curr_connect->wifi_time)/60;
                    if (2 <= $curr_connect->exit_type){
                        $online_time = round(($curr_connect->wifi_logout_time-$curr_connect->wifi_login_time)/60, 2);
                    }else{
                        $online_time = round((time()-$curr_connect->wifi_login_time)/60, 2);
                    }
                    if($online_time >= $curr_wifi_time){
                        $online_time = $curr_wifi_time;
                    }
                    if($online_time < $curr_wifi_time && 1 != $curr_connect->exit_type){
                         $this->redirect(yii::app()->createUrl('service/WifiConnect'));
                    }
            }
            $membership = MembershipService::getMembership($this->membership_id);
            $free_bool = MywifiService::getFreeWifiLog($this->membership_id);
            
            $wifi_compensation_log = MywifiService::getWifiCompensationLog($this->membership_id);
            
            $wifi_names_and_times = (isset($this->wifi_server_conf_array['wifi_names_and_times']) ? $this->wifi_server_conf_array['wifi_names_and_times'] : array());
            $wifi_names_and_price = (isset($this->wifi_server_conf_array['wifi_names_and_price']) ? $this->wifi_server_conf_array['wifi_names_and_price'] : array());
            $wifi_free_names_and_times = (isset($this->wifi_server_conf_array['wifi_free_names_and_times']) ? $this->wifi_server_conf_array['wifi_free_names_and_times'] : array());

            $this->render('service_wifi_view',array('wifi_names_and_times'=>$wifi_names_and_times, 'wifi_names_and_price'=>$wifi_names_and_price,
                'wifi_free_names_and_times'=>$wifi_free_names_and_times, 'free_bool'=>$free_bool, 'wifi_compensation_log'=>$wifi_compensation_log,'membership'=>$membership));
      
           }
           
           
        public function actionBookingwifi()
        {
            
            $wifi_names_and_price = (isset($this->wifi_server_conf_array['wifi_names_and_price']) ? $this->wifi_server_conf_array['wifi_names_and_price'] : array());
            $wifi_free_names_and_times = (isset($this->wifi_server_conf_array['wifi_free_names_and_times']) ? $this->wifi_server_conf_array['wifi_free_names_and_times'] : array());
     
            //wifi 补偿 设置为 false，不跳转到补偿页面
            $wifi_compensation_log = '';
//            if(isset($_POST['wifi_name'])){
//                $wifi_compensation_log = MywifiService::getWifiCompensationLogById($_POST['wifi_name']);                
//            }
            
            if (false || !empty($wifi_compensation_log)){
                //$this->render('service_wifi_compensation_view',array('wifi_compensation_log'=>$wifi_compensation_log));
                
            }else{
                if(isset($_POST['wifi_name']) && (isset($wifi_names_and_price[$_POST['wifi_name']]) || isset($wifi_free_names_and_times[$_POST['wifi_name']]))){
                    $curr_wifi_price = 0;
                    if(!isset($wifi_free_names_and_times[$_POST['wifi_name']])){
                        $curr_wifi_price = $wifi_names_and_price[$_POST['wifi_name']];
                    }
                    $membership = MembershipService::getMembership($this->membership_id);
                    $this->render('service_booking_wifi_view',array('booking_wifi_names'=>$_POST['wifi_name'], 'curr_wifi_price'=>$curr_wifi_price,'membership'=>$membership));
                }else{
                    $message_title='提示';
                    $message_info = 'Wifi套餐不存在!';
                    $message_url = yii::app()->createUrl('Service/Wifilist');
                    $this->render('my_message_view',array('message_title'=>$message_title,'message_info'=>$message_info,'message_url'=>$message_url));
                }
            }
        }
        public function actionWifipay()
        {
            //需要判断时长是否存在，如果存在，使用时长，无需购买
            $wifi_names_and_price = (isset($this->wifi_server_conf_array['wifi_names_and_price']) ? $this->wifi_server_conf_array['wifi_names_and_price'] : array());
            $wifi_names_and_times = (isset($this->wifi_server_conf_array['wifi_names_and_times']) ? $this->wifi_server_conf_array['wifi_names_and_times'] : array());
            $wifi_free_names_and_times = (isset($this->wifi_server_conf_array['wifi_free_names_and_times']) ? $this->wifi_server_conf_array['wifi_free_names_and_times'] : array());
            
            $booking_wifi_name = isset($_POST['booking_wifi_name']) ? $_POST['booking_wifi_name'] : '';
            $message_title = yii::t('basic', 'wifi充值');
            $message_info = '';
            $message_url = '';
            
            if(empty($wifi_names_and_times) || empty($booking_wifi_name))
            {
                $message_info = '该页面已失效。';
                $message_url = yii::app()->createUrl('Service/Wifilist');
                $this->render('my_message_view',array('message_title'=>$message_title,'message_info'=>$message_info,'message_url'=>$message_url));
                    
            }
            if(!empty($booking_wifi_name) && (isset($wifi_names_and_price[$booking_wifi_name]) || isset($wifi_free_names_and_times[$booking_wifi_name]))){
                $curr_wifi_price = 0;
                $wifi_session_time = 0;
                if(!isset($wifi_free_names_and_times[$booking_wifi_name])){
                    $curr_wifi_price = $wifi_names_and_price[$booking_wifi_name];
                }else{
                    //免费时间所需要的价格
                }
                $membership = MembershipService::getMembership($this->membership_id);
                $membership_amount = $membership->member_money;
                      
                if(($curr_wifi_price*100) > $membership_amount)
                {
                    $message_info = '你的余额不足请去柜台先充值!';
                    $message_url = yii::app()->createUrl('Service/Wifilist');
                    $this->render('my_message_view',array('message_title'=>$message_title,'message_info'=>$message_info,'message_url'=>$message_url));
                }else{
                    $state = MyWifi::topup($membership, $curr_wifi_price);

                    if($state){
                        $membership->member_money = $membership_amount - ($curr_wifi_price*100);
                        $membership->save();
                        
                        $logMemberTransferAmount = new LogMemberTransferAmount();
                        $logMemberTransferAmount->member_id = $this->membership_id;
                        $logMemberTransferAmount->transfer_type = 2;//余额支付
                        $logMemberTransferAmount->old_amount = $membership_amount/100;
                        $logMemberTransferAmount->transfer_amount = $curr_wifi_price;
                        $logMemberTransferAmount->total_amount = ($membership_amount/100 - $curr_wifi_price);
                        $logMemberTransferAmount->create_time = time();
                        $logMemberTransferAmount->create_by = 'wifi充值';
                        $logMemberTransferAmount->remark = 'wifi成功充值扣费：'.$curr_wifi_price;
                        $logMemberTransferAmount->save();
                        
                        $message_info = 'wifi充值成功!';
                        $message_url = yii::app()->createUrl('Service/Wificonnect');
                        
                        $this->render('my_message_view',array('message_title'=>$message_title,'message_info'=>$message_info,'message_url'=>$message_url));
                        
                    }else{
                        $message_info = 'wifi充值失败!';
                        $message_url = yii::app()->createUrl('Service/Wifilist');
                        
                        $this->render('my_message_view',array('message_title'=>$message_title,'message_info'=>$message_info,'message_url'=>$message_url));
                    }
                    
                }            
            }else{
                $message_info = 'Wifi套餐不存在!';
                $message_url = yii::app()->createUrl('Service/Wifilist');
                        
                $this->render('my_message_view',array('message_title'=>$message_title,'message_info'=>$message_info,'message_url'=>$message_url));
            }
//            $this->redirect(array('site/message','message_info'=>'你的余额不足请充值！','menu_type'=>'service'));
            
        }
        public function actionWificonnect(){
            $membership = MembershipService::getMembership($this->membership_id);
            MyCurl::vlogin(Yii::app()->params['wifi_url'],'status=manage&opt=login&admin='.Yii::app()->params['wifi_login_name'].'&pwd='.Yii::app()->params['wifi_login_password']);
            $check_out_param = 'status=manage&opt=dbcs&subopt=checkout&dbName=usermanage_umb&admin='.Yii::app()->params['wifi_login_name'].'&account='.$membership->member_code;
            
            $check_out_json = MyCurl::vpost(Yii::app()->params['wifi_url'],$check_out_param);
            $check_out_array = json_decode(iconv('GB2312', 'UTF-8', $check_out_json),true);
            $wifi_membership = '';
            if($check_out_array['success']){
                $wifi_membership = $check_out_array['data'];

                $this->render('wifi_connt_view',array('wifi_membership'=>$wifi_membership,'membership'=>$membership));
            }else{
                $message_title = yii::t('basic','上网提示');
                $message_info = '请先充值';
                $message_url = yii::app()->createUrl('Service/Wifilist');
       
                $this->render('my_message_view',array('message_title'=>$message_title,'message_info'=>$message_info,'message_url'=>$message_url));
            }      
            
        }
        
        public function actionOnline(){
            $message_title = yii::t('basic','用户上线');
            $message_info = '上线失败';
            
            $membership = MembershipService::getMembership($this->membership_id);
            $passport_number = $membership->passport_number;
            $online_param = 'status=login&opt=login&IsAjaxClient=1&account='.$membership->member_code.'&pwd='.substr($passport_number,-6);
            $online_json = MyCurl::vpost(Yii::app()->params['wifi_url'],$online_param);
            $online_array = json_decode(iconv('GB2312', 'UTF-8', $online_json),true);
            if(!empty($online_array)){
                $message_info = '上线成功';
            }
            $message_url = yii::app()->createUrl('Service/Wificonnect');
                        
            $this->render('my_message_view',array('message_title'=>$message_title,'message_info'=>$message_info,'message_url'=>$message_url));
        }
        public function actionDisconnect()
        {
            $idRec = isset($_POST['userid']) ? $_POST['userid'] :'';

            $message_title = yii::t('basic','用户下线');
            $message_info = '下线失败';
            if(isset($_POST['userid'])){
                $disc_param = 'status=manage&opt=dbcs&subopt=disc&dbName=usermanage_umb&idRec='.$idRec;
                $disc_json = MyCurl::vpost(Yii::app()->params['wifi_url'],$disc_param);
                $disc_array = json_decode(iconv('GB2312', 'UTF-8', $disc_json),true);            
                if($disc_array['success']){
                    $message_info = '下线成功';
                }  
            }
            $message_url = yii::app()->createUrl('Service/Wificonnect');
                        
            $this->render('my_message_view',array('message_title'=>$message_title,'message_info'=>$message_info,'message_url'=>$message_url));
            
        }
        public function actionTelephone(){
            $membership = MembershipService::getMembership($this->membership_id);
            $this->render('service_telephone_view', array('membership'=>$membership));
        }
        	
}