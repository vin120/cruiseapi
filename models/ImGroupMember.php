<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "vcos_im_group_member".
 *
 * @property integer $id
 * @property string $member_id
 * @property integer $group_id
 * @property string $member_group_name
 */
class ImGroupMember extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vcos_im_group_member';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['group_id'], 'integer'],
            [['member_id'], 'string', 'max' => 32],
            [['member_group_name'], 'string', 'max' => 100],
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
            'group_id' => 'Group ID',
            'member_group_name' => 'Member Group Name',
        ];
    }
}
