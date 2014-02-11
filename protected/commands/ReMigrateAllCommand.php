<?php
/**
 * ReMigrateAllCommand class file.
 * @author BSCheshir <BSCheshir@gmail.com>
 * @copyright Copyright &copy; BSCheshir 2014
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package system.cli.commands
 */

Yii::import('system.cli.commands.MigrateCommand');

/*
 * This command siliency run 
 * migrate --interactive=0 mark 000000_000000
 * and
 * migrate --interactive=0
 * use for (re)init database on hosting from sql dump
 * db created automatically from protected\config\cron.php settings
 */

class ReMigrateAllCommand extends MigrateCommand
{
	/**
	 * @var boolean whether to execute the migration in an interactive mode. Defaults to false.
	 * Set this to false when performing migration in a cron job or background process.
	 */
	public $interactive=false;
	
	public function actionUp($args)
	{
		parent::actionMark(Array('000000_000000'));
		parent::actionUp(Array());
	}
}