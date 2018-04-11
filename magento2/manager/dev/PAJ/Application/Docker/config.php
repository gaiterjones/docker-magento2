<?php
/*

	Edit configuration settings here

*/

// 
//
//

namespace PAJ\Application\Docker;

class config
{
	// configure memcache
	//
	const useMemcache=true;
	const memcacheServer='memcached';
	const memcacheServerPort='11211';
	const memcacheTTL='3600';
	const cacheKey='docker-scaledemo';	
	

	// my constants here
	
	
	public $_serverURL;
	public $_serverPath;
	
	
	public function __construct()
	{
		date_default_timezone_set('Europe/London');
		
		if (php_sapi_name() != 'cli') {
			$this->_serverURL=$this->serverURL();
			$this->_serverPath=$this->serverPath();
		}
	}
	
	
    public function get($constant) {
	
	    $constant = 'self::'. $constant;
	
	    if(defined($constant)) {
	        return constant($constant);
	    }
	    else {
	        return false;
	    }

	}

	/**
	 * serverURL function.
	 * 
	 * @access public
	 * @return string
	 */
	public function serverURL() {
	 $_serverURL = 'http';
	 if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$_serverURL .= "s";}
	 $_serverURL .= "://";
	 if ($_SERVER["SERVER_PORT"] != "80") {
	  $_serverURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
	 } else {
	  $_serverURL .= $_SERVER["SERVER_NAME"];
	 }
	 return $_serverURL;
	}
	
	private function serverPath() {
	 $_serverPath=$_SERVER["REQUEST_URI"];
	 //$_serverPath=explode('?',$_serverPath);
	 //$_serverPath=$_serverPath[0];
	 
	 return $_serverPath;
	}	
	
}




?>