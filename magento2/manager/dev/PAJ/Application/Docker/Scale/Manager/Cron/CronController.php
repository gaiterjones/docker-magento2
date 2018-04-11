<?php
/**
 *  Application CRON
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
 
namespace PAJ\Application\Docker\Scale\Manager\Cron;

class CronController
{

	protected $__;
	protected $__config;
	protected $__cache;

	public function __construct() {
	

	}
	
	public function escape($string) {
		$return = '';
		for($i = 0; $i < strlen($string); ++$i) {
			$char = $string[$i];
			$ord = ord($char);
			if($char !== "'" && $char !== "\"" && $char !== '\\' && $ord >= 32 && $ord <= 126)
				$return .= $char;
			else
				$return .= '\\x' . dechex($ord);
		}
		return $return;
	}
	
	public function curlThis($_url){
	 
		if (!function_exists('curl_init')){
			die('ERROR : cURL is not installed!');
		}
		
		$_lang=array('en-US');
		//$_userAgent=Application_SEM_Data::random_uagent($_lang);
		
		$_ch = curl_init();
		curl_setopt($_ch, CURLOPT_URL, $_url);
		curl_setopt($_ch, CURLOPT_HEADER, 0);
		curl_setopt($_ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($_ch, CURLOPT_TIMEOUT, 10);
		$_output = curl_exec($_ch);
		curl_close($_ch);
	 
		return $_output;
	}	
	
	public function set($key,$value)
	{
		$this->__[$key] = $value;
	}
		
  	public function get($variable)
	{
		return $this->__[$variable];
	}	

}