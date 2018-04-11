<?php
/**
 *  paj@gaiterjones.com
 *  MEMCACHE helper class - for all round cache (helper) fun.
 *
 *	version AUGUST 2014
 * 
 *
 */

namespace PAJ\Library\Cache;
 
// memcache helper class
//
class Helper {


	// get a string or array from cache
	// -- returns cached data or false
	//
	public static function getCachedString($_key,$_isArray=true)
	{
		$_cache=false;
		$_obj=new Memcache();
		$_cacheConnected=$_obj->get('memcacheconnected');
		
		if ($_cacheConnected)
		{
			if ($_isArray) {
				$_cache=unserialize($_obj->cacheGet($_key));
			} else {
				$_cache=$_obj->cacheGet($_key);
			}
		}
		
		unset($_obj);			
		return $_cache;		
	}
	
	// get a string or array from cache
	// -- returns cached data or false
	//
	public static function getCachedStringWithNameSpace($_key,$_nameSpace='global',$_isArray=true)
	{
		$_cache=false;
		$_obj=new Memcache();
		$_cacheConnected=$_obj->get('memcacheconnected');
		
		if ($_cacheConnected)
		{
			if ($_isArray) {
				$_cache=unserialize($_obj->cacheGetWithNameSpace($_key,$_nameSpace));
			} else {
				$_cache=$_obj->cacheGetWithNameSpace($_key,$_nameSpace);
			}
		}
		
		unset($_obj);			
		return $_cache;		
	}	
	
	// set a string or array in cache
	//
	public static function setCachedString($_string,$_key,$_ttl=7200)
	{
		$_obj=new Memcache();
		$_cacheConnected=$_obj->get('memcacheconnected');
		
		if ($_cacheConnected) // if connected, use cache otherwise return original string
		{
			if (is_array($_string))
			{
				$_obj->cacheSet($_key,serialize($_string),$_ttl);
			} else {
				$_obj->cacheSet($_key,$_string,$_ttl);
			}
		} 
		
		unset($_obj);
		return ($_string);
	}
	
	// set a string or array in cache with namespace
	//
	public static function setCachedStringWithNameSpace($_string,$_key,$_nameSpace='global',$_ttl=7200)
	{
		$_obj=new Memcache();
		$_cacheConnected=$_obj->get('memcacheconnected');
		
		if ($_cacheConnected) // if connected, use cache otherwise return original string
		{
			if (is_array($_string))
			{
				$_obj->cacheSetWithNameSpace($_key,serialize($_string),$_nameSpace,$_ttl);
			} else {
				$_obj->cacheSetWithNameSpace($_key,$_string,$_nameSpace,$_ttl);
			}
		}
		
		unset($_obj);
		return ($_string);
	}
	
	/**
	 * incLogCounter function.
	 * @what increments a counter in memcache
	 * @access public
	 * @return INTEGER COUNTER
	 */		
	public static function incLogCounter($_cacheNameSpace)
	{
		$this->__cache->increment($_cacheNameSpace);
		return ($this->getLogCounter($_cacheNameSpace));
	}
	
	/**
	 * getLogCounter function.
	 * @what gets a memcache counter used to numerate logs
	 * @access protected
	 * @return INTEGER COUNTER
	 */	
	public static function getLogCounter($_cacheNameSpace)
	{
	
		$_counter = $this->__cache->cacheGet($_cacheNameSpace); // get version from cache
        
        if ($_counter === false) { // if namespace not in cache reset to 1
            $_counter = 1;
            $this->__cache->cacheSet($_cacheNameSpace, $_counter,7200); // save to cache note ttl in seconds
        }
        
        return $_counter;
        
	}		
	
}