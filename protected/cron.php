<?php
/*
 * run console application on shedule
 * (for example use in migration)
 * /public_html/cheferee/protected/cron.php migrate --interactive=0 mark 000000_000000
 * /public_html/cheferee/protected/cron.php migrate --interactive=0
 * 
 */

// change the following paths if necessary
$yiic=dirname(__FILE__).'/../../../yiiframework/yiic.php';
$config=dirname(__FILE__).'/config/cron.php';

require_once($yiic);
