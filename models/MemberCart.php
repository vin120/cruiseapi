<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "vcos_member_cart".
 *
 * @property integer $id
 * @property string $membership_code
 * @property integer $shop_id
 * @property integer $goods_id
 * @property integer $num
 * @property string $add_time
 * @property integer $cart_type
 */
class MemberCart extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vcos_member_cart';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_id', 'goods_id', 'num', 'cart_type'], 'integer'],
            [['add_time'], 'safe'],
            [['membership_code'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'membership_code' => 'Membership Code',
            'shop_id' => 'Shop ID',
            'goods_id' => 'Goods ID',
            'num' => 'Num',
            'add_time' => 'Add Time',
            'cart_type' => 'Cart Type',
        ];
    }
}
