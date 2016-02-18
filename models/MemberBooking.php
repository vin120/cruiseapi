<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "vcos_member_booking".
 *
 * @property integer $id
 * @property string $booking_no
 * @property string $member_code
 * @property string $booking_name
 * @property string $booking_time
 * @property integer $booking_num
 * @property integer $status
 * @property string $store_id
 * @property integer $booking_type
 * @property string $create_time
 * @property integer $is_read
 * @property string $remark
 * @property string $booking_sign
 */
class MemberBooking extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vcos_member_booking';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['booking_time', 'create_time'], 'safe'],
            [['booking_num', 'status', 'booking_type', 'is_read'], 'integer'],
            [['booking_no', 'member_code', 'booking_sign'], 'string', 'max' => 32],
            [['booking_name', 'store_id', 'remark'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'booking_no' => 'Booking No',
            'member_code' => 'Member Code',
            'booking_name' => 'Booking Name',
            'booking_time' => 'Booking Time',
            'booking_num' => 'Booking Num',
            'status' => 'Status',
            'store_id' => 'Store ID',
            'booking_type' => 'Booking Type',
            'create_time' => 'Create Time',
            'is_read' => 'Is Read',
            'remark' => 'Remark',
            'booking_sign' => 'Booking Sign',
        ];
    }
}
