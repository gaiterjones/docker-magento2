<?php
/**
 *  
 *  Copyright (C) 2017
 *
 *
 *  @who	   	PAJ
 *  @info   	paj@gaiterjones.com
 *  @license    blog.gaiterjones.com
 * 	
 *
 */
namespace PAJ\Application;
 
if (php_sapi_name() != 'cli') { exit;} // command line only

chdir(dirname(__FILE__));

include '../../../../autoload.php';
define ('ANS','Docker'); // Application Name Space

	
// App
$_PAJApp = new Docker\Scale\Manager\Cron\Cli();
	unset($_PAJApp);
		exit;		