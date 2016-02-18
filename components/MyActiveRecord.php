<?php

namespace app\components;
use Yii;

class MyActiveRecord extends \yii\db\ActiveRecord
{

	public static  function getDb() {
		return Yii::$app->pdb;
	}
	
}

