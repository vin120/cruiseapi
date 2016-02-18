<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "vcos_im_friend".
 *
 * @property integer $id
 * @property string $member_id
 * @property string $friend_id
 * @property string $friend_name
 * @property string $add_time
 */
class ImFriend extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vcos_im_friend';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['add_time'], 'safe'],
            [['member_id', 'friend_id'], 'string', 'max' => 32],
            [['friend_name'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'member_id' => 'Member ID',
            'friend_id' => 'Friend ID',
            'friend_name' => 'Friend Name',
            'add_time' => 'Add Time',
        ];
    }
}
