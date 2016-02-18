<?php

namespace app\components;
use Yii;


class SurveyService {
	
	public static function setSurveyRecode($survey_id,$member_code,$star_value)
	{
		$create_time = date('Y-m-d H:i:s');
		$sql_value = 'INSERT INTO vcos_survey_recode(survey_id,membership_code,create_time,star_value) 
				VALUES(\''.$survey_id.'\',\''.$member_code.'\',\''.$create_time.'\',\''.$star_value.'\') ';
		Yii::$app->db->createCommand($sql_value)->execute();
	}
	
	public static function setComment($comment_content,$member_code,$comment_type_id)
	{
		$comment_time = date('Y-m-d H:i:s');
		$sql_value = 'INSERT INTO vcos_comment (comment_content,comment_time,comment_type_id,membership_code) 
				VALUES(\''.$comment_content.'\',\''.$comment_time.'\',\''.$comment_type_id.'\',\''.$member_code.'\' )';
		Yii::$app->db->createCommand($sql_value)->execute();
	}

}