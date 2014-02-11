<?php
/*
 * restore databases from phpMyAdmin sql dump
 * (without create database - 
 * db creating automatically inside migrate (or migrateall) command)
 * used to restore the state of the database on a schedule 
 * created to demonstrate the application
 * in sql dump DELIMITER // mast be use after other. 
 * (use similar in migration and ruin command stack)
 * 
 * run through protected/cron.php like this
 * php /home/u123456789/public_html/cheferee/protected/cron.php migrate --interactive=0 mark 000000_000000
 * php /home/u123456789/public_html/cheferee/protected/cron.php migrate --interactive=0
 * 
 * Tell PHP to execute a certain file.
 * $ php my_script.php
 * $ php -f my_script.php
 * Both ways (whether using the -f switch or not) execute the file my_script.php. 
 * Note that there is no restriction on which files can be executed; 
 * in particular, the filename is not required have a .php extension.
 * PHP Note:
 * If arguments need to be passed to the script when using -f , the first argument must be --.
 * 
 * if cron wizard in your hosting plan set hard prefix like "php -f /home/u123456789/"
 * and/or disabled prefix or digit on command or disabled any arguments
 * you need to find another way - for example create new command, included --argument
 * (CConsoleCommandRunner expects the first argument to the command)
 * or include cron.php in another php-file 
 * ()
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