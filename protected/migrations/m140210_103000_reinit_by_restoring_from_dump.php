<?php
/*
 * restore databases from phpMyAdmin sql dump
 * used to restore the state of the database on a schedule 
 * created to demonstrate the application
 * in sql dump DELIMITER // mast be use after other. 
 * (use similar in migration and ruin command stack)
 * cron 
 * ./yiic migrate --interactive=0 mark 000000_000000
 * ./yiic migrate --interactive=0
 * or run in unite cron.php
 * <?php
 * `php /public_html/cheferee/protected/yiic migrate --interactive=0 mark 000000_000000`;
 * `php /public_html/cheferee/protected/yiic migrate --interactive=0`;
 * 
 */

class m140210_103000_reinit_by_restoring_from_dump extends CDbMigration
{
	public function up()
	{
        $sql = file_get_contents(dirname(__FILE__).'/../data/schema.mysql.sql');
        $this->execute($sql);
		//fix for windows os
        Yii::app()->db->setActive(false);
        Yii::app()->db->setActive(true);		
	}

	public function down()
	{
		echo "m140210_103000_reinit_by_restoring_from_dump does not support migration down.\n";
		return false;
	}

	/*
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
	}

	public function safeDown()
	{
	}
	*/
}