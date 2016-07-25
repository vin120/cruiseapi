<?php
	namespace app\modules\wificard\components;
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
	    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
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
    public static function Connect($username,$password)
    {
    	$userip = self::getIp();
    	MyCurl::vcurl(Yii::$app->params['wifi_url'].'comstserver.awm?','status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);
    	$online_param = "status=login&opt=login&IsAjaxClient=1&account=$username&pwd=$password&wlanuserip=$userip";
    	$online_json = MyCurl::vcurl(Yii::$app->params['wifi_url']."comstserver.awm?",$online_param);
    	$online_json = iconv('GB2312', 'UTF-8', $online_json);
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
    	return $idRec;
    }
    
    
    //断开网络
    public static function DisConnect($idRec)
    {
    	MyCurl::vcurl(Yii::$app->params['wifi_url'].'comstserver.awm?','status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);
    	$disc_param = 'status=manage&opt=dbcs&subopt=disc&dbName=usermanage_umb&idRec='.$idRec;
    	$disc_json = MyCurl::vcurl(Yii::$app->params['wifi_url']."comstserver.awm?",$disc_param);
    	$disc_json = iconv('GB2312', 'UTF-8', $disc_json);
    	return $disc_json;
    }
    
    //创建用户
    public static function CreateUser($member)
    {
    	//模拟登录
    	MyCurl::vcurl(Yii::$app->params['wifi_url'].'comstserver.awm?','status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);
    	
    	$create_url = Yii::$app->params['wifi_url']."um_add/comstserver.awm?";
    	
    	//UTF-8 转换为 GB2312
    	$date = iconv('UTF-8','GB2312', date('Y年m月d日',time()));
    	$LinkName = iconv('UTF-8','GB2312', $member['cn_name']);
    	$create_user_param = "status=manage&opt=dbcs&dbName=usermanage_umb&subopt=add&Account=".$member['passport_number']."&pwd=".$member['passport_number']."&idUgb=1&isStartAcc=1&LinkName=".$LinkName."&paperType=6&paperNum=".$member['passport_number']."&phone=".$member['mobile_number']."&email=".$member['member_email']."&limitData=".$date;
    	$create_json = MyCurl::vcurl($create_url,$create_user_param);
    	$create_json = iconv('GB2312', 'UTF-8', $create_json);
    	return $create_json;
    }
    
    
    //查找comst里的user
    public static function FindUser($username)
    {
    	//模拟登录
    	MyCurl::vcurl(Yii::$app->params['wifi_url'].'comstserver.awm?','status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);
    	$find_url = Yii::$app->params['wifi_url']."um_query/comstserver.awm?";
    	$find_params = "status=manage&opt=dbcs&dbName=usermanage_umb&subopt=query&account=$username&IsAccount=1";
    	$find_json = MyCurl::vcurl($find_url,$find_params);
    	$find_json = iconv('GB2312', 'UTF-8', $find_json);
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
    	$idRec = $res['data']['userId'];
    	
    	//在comst系统中充钱
    	$pay_params = "admin=".Yii::$app->params['wifi_login_name']."&opt=dbcs&status=manage&subopt=paymoney&dbName=usermanage_umb&idRec=".$idRec."&money=".$price;
    	$pay_json = MyCurl::vcurl($url,$pay_params);
    	$pay_json = iconv('GB2312', 'UTF-8', $pay_json);
    	return $pay_json;
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


    //验证卡是否已售 (取消使用，改用CheckSellAndActive联表查询)
    public static function CheckSell($card)
    {
        $sql = "SELECT * FROM vcos_sell_card_records WHERE card_number='$card'";
        $sql_result = Yii::$app->db->createCommand($sql)->queryOne();
        return $sql_result;
    }

    // 查看是否存在该卡
    // public static function FindCard($card)
    // {
    //     $sql = "SELECT * FROM vcos_card WHERE card_number='$card'";
    //     $sql_result = Yii::$app->db->createCommand($sql)->queryOne();
    //     return $sql_result;
    // }

    //验证该卡是否已激活 (取消使用，改用CheckSellAndActive联表查询)
    // public static function FindCardActiveLog($card)
    // {
    //     $sql = "SELECT * FROM vcos_card_active_log WHERE card_number='$card'";
    //     $sql_result = Yii::$app->db->createCommand($sql)->queryOne();
    //     return $sql_result;
    // }

    //write card active log to db
    public static function WriteCardActiveLogToDB($card_number, $ip_address)
    {   
        $active_time = date("Y-m-d H:i:s",time());
        //记录激活的卡号和时间，ip等字段
        $sql = "INSERT INTO vcos_card_active_log (`card_number`,`active_time`,`ip_address`) VALUES ('$card_number','$active_time','$ip_address')";
        $sql_result = Yii::$app->db->createCommand($sql)->execute();

        return $sql_result;
    }

    //查询卡销售记录及是否激活
    public static function CheckSellAndActive($card_number)
    {
        $sql = "SELECT sell_date,active_time,is_cancel FROM vcos_sell_card_records a 
            LEFT JOIN vcos_card_active_log b ON a.card_number = b.card_number
            WHERE a.card_number='$card_number' ORDER BY a.id DESC";
        $sql_result = Yii::$app->db->createCommand($sql)->queryOne();

        return $sql_result;
    }

    //查看卡套餐类型名称及流量数（以price来计算）
    public static function FindCardTypeAndPrice($card)
    {
        $sql = "SELECT type_name,price FROM vcos_card a
            LEFT JOIN vcos_card_batch b ON a.batch_id = b.batch_id
            LEFT JOIN vcos_card_type c ON b.card_type_id = c.card_type_id
            WHERE card_number='$card'";
        $sql_result = Yii::$app->db->createCommand($sql)->queryOne();
        return $sql_result;
    }

    //创建卡用户
    public static function CreateCardUser($member)
    {
        //模拟登录
        MyCurl::vcurl(Yii::$app->params['wifi_url'].'comstserver.awm?','status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);
        
        $create_url = Yii::$app->params['wifi_url']."um_add/comstserver.awm?";
        
        //UTF-8 转换为 GB2312
        $date = iconv('UTF-8','GB2312', date('Y年m月d日',time()));
        $ugName = iconv('UTF-8', 'GB2312', $member['ugName']);
        $LinkName = iconv('UTF-8','GB2312', $member['cn_name']);
        $create_user_param = "status=manage&opt=dbcs&dbName=usermanage_umb&subopt=add&Account=".$member['passport_number']."&pwd=".$member['pwd']."&ugName=".$ugName."&idUgb=1&isStartAcc=1&LinkName=".$LinkName."&paperType=6&paperNum=".$member['passport_number']."&phone=".$member['mobile_number']."&email=".$member['member_email']."&limitData=".$date;
        $create_json = MyCurl::vcurl($create_url,$create_user_param);
        $create_json = iconv('GB2312', 'UTF-8', $create_json);
        return $create_json;
    }

}
