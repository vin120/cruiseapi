<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "vcos_member_order".
 *
 * @property integer $order_id
 * @property string $order_serial_num
 * @property string $membership_code
 * @property integer $totale_price
 * @property integer $pay_type
 * @property string $order_check_num
 * @property string $pay_time
 * @property string $order_create_time
 * @property integer $order_status
 * @property string $order_remark
 * @property integer $is_read
 * @property integer $order_type
 * @property integer $store_id
 * @property string $store_name
 * @property integer $receiving_way
 * @property string $consignee_address
 * @property string $delivery_time
 * @property integer $is_comment
 * @property string $remark
 */
class MemberOrder extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vcos_member_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_serial_num', 'membership_code', 'order_create_time'], 'required'],
            [['totale_price', 'pay_type', 'order_status', 'is_read', 'order_type', 'store_id', 'receiving_way', 'is_comment'], 'integer'],
            [['pay_time', 'order_create_time'], 'safe'],
            [['order_serial_num', 'membership_code', 'order_check_num'], 'string', 'max' => 32],
            [['order_remark', 'store_name'], 'string', 'max' => 100],
            [['consignee_address', 'remark'], 'string', 'max' => 255],
            [['delivery_time'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'order_id' => 'Order ID',
            'order_serial_num' => 'Order Serial Num',
            'membership_code' => 'Membership Code',
            'totale_price' => 'Totale Price',
            'pay_type' => 'Pay Type',
            'order_check_num' => 'Order Check Num',
            'pay_time' => 'Pay Time',
            'order_create_time' => 'Order Create Time',
            'order_status' => 'Order Status',
            'order_remark' => 'Order Remark',
            'is_read' => 'Is Read',
            'order_type' => 'Order Type',
            'store_id' => 'Store ID',
            'store_name' => 'Store Name',
            'receiving_way' => 'Receiving Way',
            'consignee_address' => 'Consignee Address',
            'delivery_time' => 'Delivery Time',
            'is_comment' => 'Is Comment',
            'remark' => 'Remark',
        ];
    }
}
