<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');
Yii::setPathOfAlias('bootstrap', dirname(__FILE__).'/../extensions/bootstrap');
Yii::setPathOfAlias('bscheshir', dirname(__FILE__).'/../extensions/bscheshir');
//CHtml::$closeSingleTags=true;

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'Cheferee',
	'language'=>'ru',
	'theme'=>'bootstrap',
    
	// preloading 'log' component & bootstrap
	'preload'=>array('log','bootstrap',),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
		'application.components.behaviors.*',
	),

	'modules'=>array(
		// uncomment the following to enable the Gii tool
		/*
		'gii'=>array(
			'class' => 'system.gii.GiiModule',
			'password'=>'giipassword',//example
			// If removed, Gii defaults to localhost only. Edit carefully to taste.
			'ipFilters' => array('127.0.0.1', '::1', '192.168.0.101'),
			'generatorPaths'=>array(
				'bootstrap.gii',
			),
		),
		 */	    
	),

	// application components
	'components'=>array(
		'bootstrap'=>array(
			'class'=>'bootstrap.components.Bootstrap',
		),	    
		'user'=>array(
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
		),
		// uncomment the following to enable URLs in path-format
		'urlManager'=>array(
			'urlFormat'=>'path',
			'showScriptName'=>false,
			'urlSuffix' => '',
			'rules'=>array(
				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
		),
		/*
		'db'=>array(
			'connectionString' => 'sqlite:'.dirname(__FILE__).'/../data/testdrive.db',
		),
		*/
		// uncomment the following to use a MySQL database
		'db'=>array(
			'connectionString' => 'mysql:host=localhost;dbname=cheferee',
			'emulatePrepare' => true,
			'username' => 'cheferee',
			'password' => 'chefereeemysqlpassword',//example
			'charset' => 'utf8',
			
			'enableProfiling' => true,
			'enableParamLogging' => true,
		),
		/*
		*/
		'errorHandler'=>array(
			// use 'site/error' action to display errors
			'errorAction'=>'site/error',
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
//				array(
//					'class'=>'CFileLogRoute',
//					'levels'=>'trace',
//				),
				//profile
//				array(
//				   'class'=>'CProfileLogRoute',
//				),
				// uncomment the following to show log messages on web pages
				/*
				array(
					'class'=>'CWebLogRoute',
				),
				 * uncomment the following to send email log messages
                array(
                    'class'=>'CEmailLogRoute',
                    'levels'=>'error, warning',
                    'emails'=>'admin@example.com',
                ),				 
				*/
			),
		),
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		// this is used in contact page
		'accuracyCount'=>3,	//The number of first places reliably distributed
		'scoreWining'=>10,	//1.0x10 - integer format of score. Cost of wictory
		'scoreDeadHeat'=>5,	//0.5x10 - integer format of score. Cost of dead heat
		'scoreLosing'=>0,		//0 - integer format of score. Cost of lose
		'minSemicolorCheck'=>2,	//Number of the same color in a row when the rule applies
		'maxSemicolorPass'=>3,	//The absolute maximum for games with one color in a row. 
		//You may need to put unreasonably large values to undergo distribution rules in the last game.
		'adminEmail'=>'bscheshir.work@gmail.com',
		'tablenameUser'=>'user',// table name referee
		'tablenamePlayer'=>'player',// table name player
		'tablenameRival'=>'rival',// table name player and rival log
		'tablenameGrid'=>'grid',// table nsme grid and result
	),
);