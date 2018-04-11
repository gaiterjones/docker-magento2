<?php
/**
 *  
 *  Copyright (C) 2017 paj@gaiterjones.com
 *  
 *   https://github.com/yzprofile/ngx_http_dyups_module
 */

namespace PAJ\Library\Docker\Scale\Nginx;

//
// class update/list nginx upstream host configuration
//
class DynamicUpstream
{

	public function __construct() {
		

	}

	public function getUpstream($_upstream,$_host,$_useCache=false)
	{
		$_cached=false;
		$_key='getUpstream-'. $_upstream. '-'. $_host;
		if ($_useCache)
		{
			$_output=\PAJ\Library\Cache\Helper::getCachedStringWithNameSpace($_key,$_upstream,true);
			
			if (!$_output)
			{
				$_output=explode("\n", trim($this->curlGet($_host. ':8081/upstream/'. $_upstream)));
				\PAJ\Library\Cache\Helper::setCachedStringWithNameSpace($_output,$_key,$_upstream,3600);
					
			} else { // got cached data
				
				$_cached=true;
			}
			
		} else {
			
			$_output=explode("\n", trim($this->curlGet($_host. ':8081/upstream/'. $_upstream)));
		}
		
		return array(
			'output' => $_output,
			'cached' => ($_cached ? 'true':'false')
		);
	}
	
	public function getAllUpstreams($_host,$_useCache=false)
	{
		$_output=$this->curlGet($_host. ':8081/detail');
		
		return array(
			'output' => explode("\n", trim($_output))
		);
	}
	
	public function listAllUpstreams($_host,$_useCache=false)
	{
		$_output=$this->curlGet($_host. ':8081/list');
		
		return array(
			'output' => explode("\n", trim($_output))
		);
	}
	
	public function updateUpstream($_upstream,$_host,$_config)
	{
		$_output=$this->curlPost($_host. ':8081/upstream/'. $_upstream,$_config. ';');
		
		return array(
			'output' => explode("\n", trim($_output))
		);
	}		

	protected function curlGet($_url){
	 
		if (!function_exists('curl_init')){
			die('ERROR : cURL is not installed!');
		}
		
		$_ch = curl_init();
		curl_setopt($_ch, CURLOPT_URL, $_url);
		curl_setopt($_ch, CURLOPT_HEADER, 0);
		curl_setopt($_ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($_ch, CURLOPT_TIMEOUT, 10);
		$_output = curl_exec($_ch);
		curl_close($_ch);
	 
		return $_output;
	}
	
	protected function curlPost($_url,$_post){
	 
		if (!function_exists('curl_init')){
			die('ERROR : cURL is not installed!');
		}
		
		$_ch = curl_init();
		curl_setopt($_ch, CURLOPT_URL, $_url);
		curl_setopt($_ch, CURLOPT_POST, 1);
		curl_setopt($_ch, CURLOPT_POSTFIELDS,$_post);		
		curl_setopt($_ch, CURLOPT_HEADER, 0);
		curl_setopt($_ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($_ch, CURLOPT_TIMEOUT, 10);
		$_output = curl_exec($_ch);
		curl_close($_ch);
	 
		return $_output;
	}	

}
