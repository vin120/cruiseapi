<?php
namespace app\models;

use Yii;
use app\components\MyActiveRecord;

class ProductComment extends MyActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'vcos_product_comment';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
				
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
				
		];
	}
}