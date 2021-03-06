<?php
	namespace app\modules\wifiservice\components;
	
	use Yii;
	
class MyCurl {
	
	//curl 发送请求
    public static function vcurl($url, $post = '', $cookie = '', $cookiejar = '', $referer = '')
    {   
		$tmpInfo = '';
	    $curl = curl_init();   
	    curl_setopt($curl, CURLOPT_URL, $url);   
	    curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);   
	    
	    if($referer) {   
	   		curl_setopt($curl, CURLOPT_REFERER, $referer);   
	    } else {
	    	curl_setopt($curl, CURLOPT_AUTOREFERER, 1);    
	    }
	    if($post) {
	    	curl_setopt($curl, CURLOPT_POST, 1);    
	    	curl_setopt($curl, CURLOPT_POSTFIELDS, $post);   
	    }
	    if($cookie) {
	    	curl_setopt($curl, CURLOPT_COOKIE, $cookie);
	    }
	    curl_setopt($curl, CURLOPT_TIMEOUT, 15);
	    curl_setopt($curl, CURLOPT_HEADER, 0);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	    $tmpInfo = curl_exec($curl);
	    if (curl_errno($curl)) {
	    	echo '<pre><b>错误:</b><br />'.curl_error($curl);
	    }
	    curl_close($curl);
	    return $tmpInfo;
	}

     // 删除Cookie函数
	public static function delcookie($cookie_file)
	{ 
		@unlink($cookie_file); // 执行删除
	}

	
	//查询流量
	public static function CheckFlow($passport)
	{
		MyCurl::vcurl(Yii::$app->params['wifi_url'].'comstserver.awm?','status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);
		$check_out_params = "status=manage&opt=dbcs&subopt=checkout&dbName=usermanage_umb&admin=bisheng&account=$passport";
		$check_out_json = MyCurl::vcurl(Yii::$app->params['wifi_url']."comstserver.awm?",$check_out_params);
		$check_out_json = iconv('GB2312', 'UTF-8', $check_out_json);
		
		//TEST TODO
		//self::testFunction($passport,$check_out_params,$check_out_json);
		
		return $check_out_json;
	}
	
	
	//查询流量并解析,显示在前端页面
	public static function CheckFlowAndParse($passport)
	{
		$flow_info = array();
		$flow = self::CheckFlow($passport);
		$flow_array = json_decode($flow,true);
		
		//剩余流量
		if($flow_array['success']){
			$arr = explode("<br>", $flow_array['data']['feeInfo']);	
			$price = explode(": ",$arr[2])[1];	//wifi单价 
			$total_used_flow = str_replace('MB','',explode(": ",$arr[7])[1]);	//用户使用了的总流量
			$used_money = str_replace('元','',explode(": ",$arr[8])[1]);		//用户已经使用了的钱
			$money = str_replace('元','',explode(": ",$arr[10])[1]);		//用户的当前余额
			$total_flow = ($money+$used_money) / $price;	//根据余额和单价得出的总流量
			$left_flow = number_format($total_flow - $total_used_flow,2);	//根椐总流量和使用的总流量算出的的剩余流量,格式为 172.00
			//分割流量，分割成整数部分和小数部分
			$flow_info = explode('.',$left_flow );
		}else{
			//第一次用户不存在时
			$flow_info = array('0','00');
		}
		return $flow_info;
	}
	
	//网络链接
    public static function Connect($passport,$password)
    {
    	$userip = self::getIp();
    	MyCurl::vcurl(Yii::$app->params['wifi_url'].'comstserver.awm?','status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);
    	$online_param = "status=login&opt=login&IsAjaxClient=1&account=$passport&pwd=$password&wlanuserip=$userip";
    	$online_json = MyCurl::vcurl(Yii::$app->params['wifi_url']."comstserver.awm?",$online_param);
    	$online_json = iconv('GB2312', 'UTF-8', $online_json);
    	
    	//TEST TODO
    	self::testFunction($passport, $online_param, $online_json);
    	
    	return $online_json;
    }
    
    //查找comst中$passport对应的idRec
    public static function FindidRec($passport)
    {
    	MyCurl::vcurl(Yii::$app->params['wifi_url'].'comstserver.awm?','status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);
    	$url = Yii::$app->params['wifi_url']."fee_checkout/comstserver.awm?";
    	$find_params = "status=manage&subopt=checkout&opt=dbcs&dbName=usermanage_umb&admin=".Yii::$app->params['wifi_login_name']."&account=$passport";
    	$find_json = MyCurl::vcurl($url,$find_params);
    	$find_json = iconv('GB2312', 'UTF-8', $find_json);
    	$res = json_decode($find_json,true);
    	$idRec = $res['data']['userId'];
    	
    	//TEST TODO
    	//self::testFunction($passport, $find_params, $find_json);
    	
    	return $idRec;
    }
    
    
    //断开网络
    public static function DisConnect($idRec)
    {
    	MyCurl::vcurl(Yii::$app->params['wifi_url'].'comstserver.awm?','status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);
    	$disc_param = 'status=manage&opt=dbcs&subopt=disc&dbName=usermanage_umb&idRec='.$idRec;
    	$disc_json = MyCurl::vcurl(Yii::$app->params['wifi_url']."comstserver.awm?",$disc_param);
    	$disc_json = iconv('GB2312', 'UTF-8', $disc_json);
    	
    	//TEST TODO
    	self::testFunction($idRec, $disc_param, $disc_json);
    	
    	return $disc_json;
    }
    
    //创建用户
    public static function CreateUser($member,$comst_password,$type)
    {
    	//模拟登录
    	MyCurl::vcurl(Yii::$app->params['wifi_url'].'comstserver.awm?','status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);
    	
    	$create_url = Yii::$app->params['wifi_url']."um_add/comstserver.awm?";
    	
    	//UTF-8 转换为 GB2312
    	$date = iconv('UTF-8','GB2312//IGNORE', date('Y年m月d日',time()));
    	$LinkName = iconv('UTF-8','GB2312//IGNORE', $member['cn_name']);
    	$create_user_param = "status=manage&opt=dbcs&dbName=usermanage_umb&subopt=add&Account=".$member['passport_number']."&pwd=".$comst_password."&idUgb=".$type."&isStartAcc=1&LinkName=".$LinkName."&paperType=6&paperNum=".$member['passport_number']."&phone=".$member['mobile_number']."&email=".$member['member_email']."&limitData=".$date;
    	$create_json = MyCurl::vcurl($create_url,$create_user_param);
    	$create_json = iconv('GB2312', 'UTF-8', $create_json);
    	
    	//TEST TODO
    	//self::testFunction($member['passport_number'],$create_user_param,$create_json);
    	
    	return $create_json;
    }
    
    
    //查找comst里的user
    public static function FindUser($username)
    {
    	//模拟登录
    	MyCurl::vcurl(Yii::$app->params['wifi_url'].'comstserver.awm?','status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);
    	$find_url = Yii::$app->params['wifi_url']."um_query/comstserver.awm?";
    	//TODO
    	$find_params = "status=manage&opt=dbcs&subopt=recordByName&dbName=usermanage_umb&account=$username";
//     	$find_params = "status=manage&opt=dbcs&dbName=usermanage_umb&subopt=query&account=$username&IsAccount=1&direct=1";
    	$find_json = MyCurl::vcurl($find_url,$find_params);
    	$find_json = iconv('GB2312', 'UTF-8', $find_json);
    	
    	//TEST TODO
//     	self::testFunction($username,$find_params,$find_json);
    	
    	return $find_json;
    }
    
    
    //充钱到账户
    public static function RechargeWifi($passport,$price)
    {
    	//模拟登录
    	MyCurl::vcurl(Yii::$app->params['wifi_url'].'comstserver.awm?','status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);
    	
    	//查找comst中$passport对应的idRec
    	$url = Yii::$app->params['wifi_url']."fee_checkout/comstserver.awm?";
    	$find_params = "status=manage&subopt=checkout&opt=dbcs&dbName=usermanage_umb&admin=".Yii::$app->params['wifi_login_name']."&account=$passport";
    	$find_json = MyCurl::vcurl($url,$find_params);
    	$find_json = iconv('GB2312', 'UTF-8', $find_json);
    	$res = json_decode($find_json,true);
    	
    	if(isset($res['data']['userId'])){
    		$idRec = $res['data']['userId'];
    		//在comst系统中充钱
    		$pay_params = "admin=".Yii::$app->params['wifi_login_name']."&opt=dbcs&status=manage&subopt=paymoney&dbName=usermanage_umb&idRec=".$idRec."&money=".$price;
    		$pay_json = MyCurl::vcurl($url,$pay_params);
    		$pay_json = iconv('GB2312', 'UTF-8', $pay_json);
    	}else {
    		$pay_json = '';
    	}
    	//TEST TODO
    	//self::testFunction($passport, $pay_params, $pay_json);
    	
    	return $pay_json;
    }
    
    
    //初始化账户
    public static function InitAccount($passport)
    {
    	//模拟登录
    	MyCurl::vcurl(Yii::$app->params['wifi_url'].'comstserver.awm?','status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);
    	
    	//查找comst中$passport对应的idRec
    	$url = Yii::$app->params['wifi_url']."fee_checkout/comstserver.awm?";
    	$find_params = "status=manage&subopt=checkout&opt=dbcs&dbName=usermanage_umb&admin=".Yii::$app->params['wifi_login_name']."&account=$passport";
    	$find_json = MyCurl::vcurl($url,$find_params);
    	$find_json = iconv('GB2312', 'UTF-8', $find_json);
    	$res = json_decode($find_json,true);
    	
    	if(isset($res['data']['userId'])){
    		$idRec = $res['data']['userId'];
    		
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
    		
            //写入剩余流量记录
            self::WriteLeftFlowLogToDB($passport);
             		
    		//初始化
	    	$init_params = "status=manage&opt=dbcs&subopt=initAccount&dbName=usermanage_umb&idRec=".$idRec."&admin=".Yii::$app->params['wifi_login_name'];
	    	$init_json = MyCurl::vcurl($url,$init_params);
	    	$init_json = iconv('GB2312', 'UTF-8', $init_json);
    	}else {
    		$init_json = '';
    	}
    	return $init_json;
    }
    

    //记录剩余流量
    public static function WriteLeftFlowLogToDB($passport)
    {
        //查询剩余流量
        $left_flow = self::CheckFlowAndParse($passport);

        $ip_address = MyCurl::getIp();
        $init_time = date("Y-m-d H:i:s",time());

        //写入剩余流量到表
        $log = Yii::$app->db->createCommand()->insert('vcos_wifi_left_flow_log', [
                    'passport_number' =>$passport,
                    'left_flow' => $left_flow[0],
                    'ip_address'=>$ip_address,
                    'init_time'=>$init_time,
            ])->execute();

        return $log;
    }

	
	//初始化账户组
	public static function InitAccountGroup($group_name)
	{
		//模拟登录
		MyCurl::vcurl(Yii::$app->params['wifi_url'].'comstserver.awm?','status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);

		$url = Yii::$app->params['wifi_url']."fee_InitBatch/comstserver.awm?";
		$find_params = "status=manage&subopt=query&opt=dbcs&dbName=usergroup_ugb&admin=".Yii::$app->params['wifi_login_name'];
		$find_json = MyCurl::vcurl($url,$find_params);
		$find_json = iconv('GB2312', 'UTF-8', $find_json);
		$res = MyCurl::ext_json_decode($find_json,true);

		$init_json = '初始化失败';

		if(isset($res['total']))
		{
			$usergroup_array = isset($res['records']) ? $res['records'] : array();

			foreach ($usergroup_array as $usergroup){

				if( $group_name == $usergroup['name']){
					$idUgb= $usergroup['idRec'];
					$init_params = "status=manage&opt=dbcs&subopt=initBatch&dbName=usermanage_umb&ugName=".$group_name."&idUgb=".$idUgb."&admin=".Yii::$app->params['wifi_login_name'];
					$init_json = MyCurl::vcurl($url,$init_params);
					$init_json = iconv('GB2312', 'UTF-8', $init_json);
					break;
				}
			}
		}
		return $init_json;
	}

	/** 兼容key没有双引括起来的JSON字符串解析
	 * @param String $str JSON字符串
	 * @param boolean $mod true:Array,false:Object
	 * @return Array/Object
	 */
	public static function ext_json_decode($str, $mode=false){
		if(preg_match('/\w:/', $str)){
			$str = preg_replace('/(\w+):/is', '"$1":', $str);
		}
		return json_decode($str, $mode);
	}
	
    //获取用户ip
    public static function getIp() {
    	if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) $ip = getenv("HTTP_CLIENT_IP");
    	else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) $ip = getenv("HTTP_X_FORWARDED_FOR");
    	else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) $ip = getenv("REMOTE_ADDR");
    	else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) $ip = $_SERVER['REMOTE_ADDR'];
    	else $ip = "unknown";
    	return ($ip);
    }
    
    //过滤字符
    public static function deletehtml($str)
    {
    	$str = trim($str);
    	$str=strip_tags($str,"");
    	$str=preg_replace("{\t}","",$str);
    	$str=preg_replace("{\r\n}","",$str);
    	$str=preg_replace("{\r}","",$str);
    	$str=preg_replace("{\n}","",$str);
    	$str=preg_replace("{ }","",$str);
    	return $str;
    }
    
    public static function testFunction($passport,$params,$response)
    {
    	$time = date('Y-m-d H:i:s',time());
    	$sql = "INSERT INTO `vcos_wifi_test_tab` (`passport`,`params`,`response`,`time`) VALUES ('$passport','$params','$response','$time')";
    	Yii::$app->db->createCommand($sql)->execute();
    }
    
}
