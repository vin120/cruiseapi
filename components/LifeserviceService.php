<?php

namespace app\components;
use Yii;

/**
 * Description of LifeserviceService
 *
 * @author Rock.Lei
 */
class LifeserviceService {
	
	public static function getLifeserviceById($lifeservice_id,$my_lang='zh_cn')
	{
		$sql_value = 'SELECT a.ls_id as lifeservice_id, b.ls_title as lifeservice_title, b.ls_desc as lifeservice_desc, a.ls_tel as telphone, (a.ls_price/100) as price,
					b.ls_address as address,b.ls_opening_time as opening_time, \'\' as img_items,b.ls_info as lifeservice_info
				FROM vcos_lifeservice a, vcos_lifeservice_language b
				WHERE a.ls_id = b.ls_id AND a.ls_id = \''.$lifeservice_id.'\' AND b.iso = \''.$my_lang.'\'';
			
		$lifeservice = Yii::$app->db->createCommand($sql_value)->queryOne();
		
		return $lifeservice;
	}
	
	
	public static function getLifeserviceImageById($lifeservice_id,$my_lang='zh_cn')
	{
		$sql_img = 'SELECT img_url FROM vcos_lifeservice_img WHERE lifeservice_id='.$lifeservice_id.' AND iso=\''.$my_lang.'\' AND state=1';
		$img_array = Yii::$app->db->createCommand($sql_img)->queryAll();
	
		return $img_array;
	}
	
	
}