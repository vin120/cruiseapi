<?php

namespace app\components;
use Yii;

class MymemberActiveRecord extends \yii\db\ActiveRecord
{

	public static  function getDb() {
		return Yii::$app->mdb;
	}
	
}

