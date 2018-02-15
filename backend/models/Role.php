<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "role".
 *
 * @property integer $id
 * @property string $name
 * @property string $property
 *
 * @property EpmsAccount[] $epmsAccounts
 */
class Role extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'role';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name', 'property'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '角色',
            'property' => 'Property',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEpmsAccounts()
    {
        return $this->hasMany(EpmsAccount::className(), ['role' => 'id']);
    }
}
