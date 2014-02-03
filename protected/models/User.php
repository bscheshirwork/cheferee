<?php

/**
 * This is the model class for table "user".
 *
 * The followings are the available columns in table 'user':
 * @property integer $id
 * @property string $username
 * @property string $password
 * @property integer $type
 * @property string $timestamp
 */
class User extends CActiveRecord
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
			array('username, password', 'required'),
			array('type', 'numerical', 'integerOnly'=>true),
			array('username', 'length', 'max'=>255),
			array('password', 'length', 'max'=>64),
			// The following rule is used by search().
			array('id, username, type, timestamp', 'safe', 'on'=>'search'),
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
			'username' => 'Имя пользователя',
			'password' => 'Пароль',
			'type' => 'Тип',
			'timestamp' => 'Время',
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
		$criteria->compare('username',$this->username,true);
		$criteria->compare('type',$this->type);
		$criteria->compare('timestamp',$this->timestamp,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return User the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	/**
	 * Returns the password validation result
	 * @param string $password The password to verify. If password is empty or not a string, method will return false.
	 * @return bool True if the password matches the hash in user data.
	 */
    public function validatePassword($password)
    {
        return CPasswordHelper::verifyPassword($password,$this->password);
    }
 
	/**
	 * Generate a secure hash from a password and a random salt. 
	 * @param string $password The password to be hashed
	 * @return string	The password hash string, ASCII and not longer than 64 characters.
	 */
    public function hashPassword($password)
    {
        return CPasswordHelper::hashPassword($password);
    }
	
	/**
	 * This method is invoked before saving a record (after validation, if any).
	 * Use {@link isNewRecord} to determine whether the saving is
	 * for inserting or updating record.
	 * @return boolean whether the saving should be executed. Defaults to true.
	 */
	protected function beforeSave()
	{
		//$this->password = $this->hashPassword($this->password);
		$this->timestamp = new CDbExpression('UNIX_TIMESTAMP(NOW())');
		return parent::beforeSave();
	}     	
}
