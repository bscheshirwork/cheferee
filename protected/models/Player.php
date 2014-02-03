<?php

/**
 * This is the model class for table "player".
 *
 * The followings are the available columns in table 'player':
 * @property integer $id
 * @property string $name
 * @property string $nickname
 * @property string $birthyear
 * @property integer $elo
 * @property string $logo
 * @property integer $leave
 */
class Player extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return Yii::app()->params['tablename'.__CLASS__];
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, nickname, birthyear, elo', 'required'),
			array('elo', 'numerical', 'integerOnly'=>true),
			array('name, nickname', 'length', 'max'=>256),
			array('birthyear', 'length', 'max'=>4),
			array('leave', 'length', 'max'=>1),
			array('logo', 'length', 'max'=>1024),
			// The following rule is used by search().
			array('id, name, nickname, birthyear, elo, logo, leave', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name' => 'ФИО участника',
			'nickname' => 'Ник участника',
			'birthyear' => 'Год рождения',
			'elo' => 'ELO',
			'logo' => 'Логотип',
			'leave' => 'Игрок покинул соревнование',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('nickname',$this->nickname,true);
		$criteria->compare('birthyear',$this->birthyear,true);
		$criteria->compare('elo',$this->elo);
		$criteria->compare('logo',$this->logo,true);
		$criteria->compare('leave',$this->leave,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Player the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
