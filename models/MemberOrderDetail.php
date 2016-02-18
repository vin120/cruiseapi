<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "vcos_member_order_detail".
 *
 * @property integer $order_detail_id
 * @property string $order_serial_num
 * @property integer $goods_id
 * @property string $goods_name
 * @property string $goods_img_url
 * @property integer $goods_price
 * @property integer $buy_num
 * @property integer $sub_goods_state
 * @property string $sub_goods_remark
 * @property string $last_change_time
 * @property integer $standard_price
 */
class MemberOrderDetail extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vcos_member_order_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_serial_num', 'goods_id', 'goods_name'], 'required'],
            [['goods_id', 'goods_price', 'buy_num', 'sub_goods_state', 'standard_price'], 'integer'],
            [['last_change_time'], 'safe'],
            [['order_serial_num'], 'string', 'max' => 32],
            [['goods_name', 'goods_img_url'], 'string', 'max' => 255],
            [['sub_goods_remark'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'order_detail_id' => 'Order Detail ID',
            'order_serial_num' => 'Order Serial Num',
            'goods_id' => 'Goods ID',
            'goods_name' => 'Goods Name',
            'goods_img_url' => 'Goods Img Url',
            'goods_price' => 'Goods Price',
            'buy_num' => 'Buy Num',
            'sub_goods_state' => 'Sub Goods State',
            'sub_goods_remark' => 'Sub Goods Remark',
            'last_change_time' => 'Last Change Time',
            'standard_price' => 'Standard Price',
        ];
    }
}
