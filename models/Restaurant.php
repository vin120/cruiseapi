<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "vcos_restaurant".
 *
 * @property integer $restaurant_id
 * @property integer $restaurant_type
 * @property string $restaurant_tel
 * @property string $restaurant_img_url
 * @property string $restaurant_img_url2
 * @property integer $restaurant_state
 * @property integer $restaurant_sequence
 * @property string $bg_color
 * @property integer $can_delivery
 * @property integer $can_book
 * @property string $food_setting
 */
class Restaurant extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vcos_restaurant';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['restaurant_type', 'restaurant_tel', 'restaurant_img_url', 'restaurant_img_url2', 'restaurant_state', 'restaurant_sequence'], 'required'],
            [['restaurant_type', 'restaurant_state', 'restaurant_sequence', 'can_delivery', 'can_book'], 'integer'],
            [['restaurant_tel', 'restaurant_img_url', 'restaurant_img_url2', 'bg_color'], 'string', 'max' => 255],
            [['food_setting'], 'string', 'max' => 1000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'restaurant_id' => 'Restaurant ID',
            'restaurant_type' => 'Restaurant Type',
            'restaurant_tel' => 'Restaurant Tel',
            'restaurant_img_url' => 'Restaurant Img Url',
            'restaurant_img_url2' => 'Restaurant Img Url2',
            'restaurant_state' => 'Restaurant State',
            'restaurant_sequence' => 'Restaurant Sequence',
            'bg_color' => 'Bg Color',
            'can_delivery' => 'Can Delivery',
            'can_book' => 'Can Book',
            'food_setting' => 'Food Setting',
        ];
    }
}
