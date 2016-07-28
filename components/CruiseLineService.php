<?php

namespace app\components;
use Yii;
use yii\helpers\Url;
use app\modules\wifiservice\components\MyCurl;
/**
 * Description of BaggageService
 *
 * @author Rock.Lei
 */
class CruiseLineService {
	/**
	 * 根据当前时间判断所在航线
	 * @return type
	 * 
	 */
	public static function getCruiseLineByCurrTime()
	{
		$curr_time = time();
		$sql_value = 'SELECT trip_id,trip_name,trip_no,trip_start_time,trip_end_time 
				FROM vcos_cruise_trip WHERE trip_state =0 AND trip_start_time< '.$curr_time.' AND trip_end_time > '.$curr_time.' LIMIT 1';
		$cruise_trip = Yii::$app->mdb->createCommand($sql_value)->queryOne();
		
		return $cruise_trip;
	}
	
	public static function getCruiseAddress($member_code,$cruise_trip){
		$sql_value = 'SELECT t2.cabin_name_num,t2.floor FROM vcos_boarding_ticket t1,vcos_cruise_cabin t2
		WHERE t1.cabin_id=t2.cabin_id AND t1.trip_id=t2.trip_id AND t1.trip_id=\''.$cruise_trip.'\' AND t1.member_or_crew_code=\''.$member_code.'\' LIMIT 1';
		$cabin = Yii::$app->mdb->createCommand($sql_value)->queryOne();
		
		return $cabin;
	}
	
	//初始化用户流量
	public static function getRunInitStatus($type,$passport)
	{
		$sql = '';
		if($type == 1){
			//会员
			$cruise_trip = CruiseLineService::getCruiseLineByCurrTime();
			$trip_id = $cruise_trip['trip_id'];
	
			if(!empty($trip_id)){
				
				$sql = "SELECT * FROM vcos_wifi_login_log WHERE login_type=$type AND trip_id='$trip_id' AND passport='$passport'";
				$sql_result = Yii::$app->db->createCommand($sql)->queryOne();
	
				if (empty($sql_result)) {
					
					$my_date = date('Y-m-d');
					$my_time = date('H:i:s');
					
					$sql = "INSERT INTO `vcos_wifi_login_log` (`login_date`,`login_time`,`login_type`,`trip_id`,`passport`)
					VALUES ('$my_date','$my_time','$type','$trip_id','$passport')";
					Yii::$app->db->createCommand($sql)->execute();
					
					MyCurl::InitAccount($passport);
				}
				
			}
	
		}else{
			//船员
			$temp_date = date('Y-m-');
			$like_value = $temp_date.'%';
			$sql = "SELECT * FROM vcos_wifi_login_log WHERE login_type=$type AND login_date LIKE '$like_value' AND passport='$passport'";
			$sql_result = Yii::$app->db->createCommand($sql)->queryOne();
			if (empty($sql_result)) {
				$my_date = date('Y-m-d');
				$my_time = date('H:i:s');
	
				$sql = "INSERT INTO `vcos_wifi_login_log` (`login_date`,`login_time`,`login_type`,`passport`)
				VALUES ('$my_date','$my_time','$type','$passport')";
	
				Yii::$app->db->createCommand($sql)->execute();
				MyCurl::InitAccount($passport);
				self::wifiOfficeCrewInit($passport);//配合上一条初始化一起开启，完成初始化后的流量分配
	
			}
		}
	
	}

	//船员流量分配处理（完成分配流量的充值）
	public static function wifiOfficeCrewInit($passport)
	{
		//查询上网船员流量分配执行记录表是否有当月的记录，即是否已做数据迁移
		$temp_date = date('Y-m-');
		$like_value = $temp_date.'%';
		$migration_sql = "SELECT * FROM vcos_wifi_office_crew_migration WHERE migration_type = 1 AND migration_datetime LIKE '$like_value'";
		$migration_sql_result = Yii::$app->db->createCommand($migration_sql)->queryOne();

// var_dump($migration_sql_result);
		//当无当月的迁移记录时，做一次数据迁移
		if (empty($migration_sql_result)) {
			//获取表——渤海办公总流量模板(此处默认表中只有一条记录，只去单条)
			$total_template_sql = "SELECT * FROM vcos_wifi_total_office_flow_template ORDER BY id DESC";
			$total_template_sql_result = Yii::$app->db->createCommand($total_template_sql)->queryOne();

// var_dump($total_template_sql_result);
			//将模板数据迁移到vcos_wifi_total_office_flow表
			$insert_data_time = date('Y-m-d H:i:s');
			$insert_data_total = Yii::$app->db->createCommand()->insert('vcos_wifi_total_office_flow', [
					'total_office_flow'	=> $total_template_sql_result['total_office_flow'],
					'flow_date' => $insert_data_time,
					])->execute();

			//获取表——办公船员名单表 * 联 * 上网船员办公流量分配模板
			// $crew_rule_template_sql = "SELECT * FROM vcos_wifi_crew_flow_rule_template";
			// $crew_rule_template_sql_result = Yii::$app->db->createCommand($crew_rule_template_sql)->queryAll();
			$crew_rule_template_sql = "SELECT a.passport,total_flow FROM vcos_wifi_office_crew a
							            LEFT JOIN vcos_wifi_crew_flow_rule_template b
							            ON a.passport = b.passport
							            WHERE a.state = '1'";
            $crew_rule_template_sql_result = Yii::$app->db->createCommand($crew_rule_template_sql)->queryAll();

// var_dump($crew_rule_template_sql_result);
			//将模板数据迁移到vcos_wifi_crew_flow_rule表
			$use_office_flow = 0;//记录总分配流量
			$last_flow_rule_sql = "SELECT * FROM vcos_wifi_total_office_flow ORDER BY id DESC";
			$last_flow_rule_sql_result = Yii::$app->db->createCommand($last_flow_rule_sql)->queryOne();
			foreach ($crew_rule_template_sql_result as $key => $value) {
				$insert_data_crew = Yii::$app->db->createCommand()->insert('vcos_wifi_crew_flow_rule', [
					'passport'	=> $value['passport'],
					'total_flow' => $value['total_flow'],
					'rule_date' => $insert_data_time,
					])->execute();
				$use_office_flow = $use_office_flow + $value['total_flow'];
			}
			//更新每月总流量表的已使用流量数
			Yii::$app->db->createCommand()->update(
							'vcos_wifi_total_office_flow', 
							['use_office_flow' => $use_office_flow],
							['id' => $last_flow_rule_sql_result['id']]
							)->execute();

			//增加一条流量分配执行记录，对应表：vcos_wifi_office_crew_migration
			$insert_data_total = Yii::$app->db->createCommand()->insert('vcos_wifi_office_crew_migration', [
					'migration_type' => '1',
					'migration_datetime' => $insert_data_time,
					])->execute();

		}

		//判断激活与否并完成船员充值
		$check_activate_sql = "SELECT * FROM vcos_wifi_crew_flow_rule WHERE passport = '$passport' AND activate_datetime LIKE '$like_value'";
		$check_activate_sql_result = Yii::$app->db->createCommand($check_activate_sql)->queryOne();
	
		//如果未激活，则进行激活并充值
		if (empty($check_activate_sql_result)) {
			//充值
			$find_crew_flow_sql = "SELECT * FROM vcos_wifi_crew_flow_rule WHERE passport = '$passport' ORDER BY id DESC";
			$find_crew_flow_sql_result = Yii::$app->db->createCommand($find_crew_flow_sql)->queryOne();

// var_dump($find_crew_flow_sql_result['total_flow']);
			$recharge_wifi = MyCurl::RechargeWifi($passport,$find_crew_flow_sql_result['total_flow']);
			$recharge_wifi = json_decode($recharge_wifi,true);

			//充值：成功，进行激活；失败，弹出报错
			if (empty($recharge_wifi['data'])) {
				return Yii::$app->getResponse()->redirect(Url::toRoute(['/wifiservice/site/login',
						'active'=> 0,
						'response'=>'初始化流量操作失败，请重试',]));
			}
			//充值失败后，重定向到报错页面，下面代码将不再执行，只有成功才继续进行激活操作
			$activate_datetime = date('Y-m-d H:i:s');
			$sql = "UPDATE `vcos_wifi_crew_flow_rule` SET `activate_datetime`='$activate_datetime' WHERE `passport`='$passport' AND `rule_date` LIKE '$like_value'";
 			Yii::$app->db->createCommand($sql)->execute();
// var_dump($recharge_wifi);die();		
		}

	}
	
	
}