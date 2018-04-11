<?php
/**
 *  paj@gaiterjones.com
 *  MEMCACHE wrapper class - for all round cache fun.
 *
 *	version JUNE 23 2014
 * 
 *
 *
 *  @category   PAJ
 *  @package    
 *  @license    http://www.gnu.org/licenses/ GNU General Public License
 * 	
 
	NOTE class is using PHP memcache, not memcached.
	installation on ubuntu:
	apt-get install memcached
	apt-get install php5-memcache
 
	Flush cache from command line:
   (sleep 2; echo flush_all; sleep 2; echo quit; ) | telnet 127.0.0.1 11212
 
	Manually flush cache
	telnet localhost 11211
	flush_all
	quit

	CacheDump - use ?dumpcache&filtercache=keytofilter
 *
 */

namespace PAJ\Library\Cache;
 
// memcache wrapper class
//
class Memcache {

	protected $con = null;
	protected $__config;
	protected $__cache;
	protected $__;
	
	public function __construct($_con=null,$_cacheNameSpace=false,$_DBName=false,$_server=false) {
	
		if (!$_cacheNameSpace) { $_cacheNameSpace='global'; } else { $this->set('namespace',$_cacheNameSpace); }
		
		$this->con=$_con;
		
		$this->loadConfig($_server);
		
		if (!$_DBName) {
			$this->set('dbname',$this->__config->get('DBNAME'));
		} else {
			$this->set('dbname',$_DBName);
		}
		
		$this->cacheConnect();
	}	
	
	// connect to the memcache server
	//
	private function cacheConnect()
	{
		$_memcacheConnected=false; // connected bool
		
		if (class_exists('Memcache')) {

            $this->__cache = new \Memcache();
			
			$_server=$this->get('memcacheserver');
			$_port=$this->get('memcacheserverport');
			
				if (@$this->__cache->connect($_server, $_port))  {
					$_memcacheConnected=true;
					$this->set('memcacheversion',$_memcacheVersion=memcache_get_version($this->__cache));
				}
				
        } else {
		
			//throw new \Exception ('No memcache');
		}
		
		$this->set('memcacheconnected',$_memcacheConnected);
    }
	
	// returns data from the cache
	//
	public function cacheGet($_key)
	{
		return $this->__cache->get($_key);

	}
	
	// increment cache key data
	//
	public function increment($_key)
	{
		return $this->__cache->increment($_key);

	}	
	
	// stores data in the cache
	//
	public function cacheSet($_key,$_data,$_ttl=false)
	{
		
		if (!$_ttl) {$_ttl=$this->get('memcachettl');} // use default ttl if not specified
		
		//Use MEMCACHE_COMPRESSED to store the item compressed (uses zlib).
		
			$this->__cache->set($_key,$_data, MEMCACHE_COMPRESSED, $_ttl);
		
	}
	
	// stores data in the cache with versioned namespace
	//
	public function cacheSetWithNameSpace($_key,$_data,$_cacheNameSpace='global',$_ttl=false)
	{
		$this->set('namespace',$_cacheNameSpace);
		$this->loadVersion();
		// get cache version to use in key, incrementing cache version invalidates key
		$_version=$this->get('namespacekeyversion');
		
		// build memcache key
		$_key=$_key.'-'.$_cacheNameSpace.'-v'. $_version.'-'. $this->get('appcachekey');
		$this->set('namespacecachekey',$_key);
		
		// add rows to cache
		$this->cacheSet($_key, $_data, $_ttl);
	}
	
	// retrieves data from the cache with versioned namespace
	//
	public function cacheGetWithNameSpace($_key,$_cacheNameSpace='global')
	{
		$this->set('namespace',$_cacheNameSpace);
		$this->loadVersion();
		// get cache version to use in key, incrementing cache version invalidates key
		$_version=$this->get('namespacekeyversion');
		
		// build memcache key
		$_key=$_key.'-'.$_cacheNameSpace.'-v'. $_version.'-'. $this->get('appcachekey');
		$this->set('namespacecachekey',$_key);
		
		// get data from cache
		return $this->cacheGet($_key);
	}	

	// Cached SQL query function. Returns either cached query or DB query as array
	//
	//
	private function cacheQuery($_query)
	{
		$_cacheNameSpace = $this->get('namespace');
		// get cache version to use in key, incrementing cache version invalidates key
		$_version=$this->get('namespacekeyversion');
		
		// build memcache key
		$_key=$_cacheNameSpace.'-v'. $_version.'-'. $this->get('appcachekey'). '-'. md5($_query);
		$this->set('namespacecachekey',$_key);
		
		// attempt to get query results from cache
		$_cachedRows=$this->cacheGet($_key);
		
		
		if (!$_cachedRows) // no data in cache, retrieve from database
		{
			mysql_select_db($this->get('dbname'), $this->con);

			
			$_result=mysql_query($_query); // perfrom query
			
			if (!$_result) throw new \Exception(get_class($this). " query failed: " . mysql_error());
			
			
			$_allRows = array();
			// dump rows into array
			while ($_rows = mysql_fetch_array($_result,MYSQL_ASSOC)) {
			    $_allRows[] = $_rows;
			}
			
			// add rows to cache
			$this->cacheSet($_key, serialize($_allRows));
			
			$this->set('datacached',false);

			return $_allRows;
		}
		
		// return cached data
		$this->set('datacached',true);
		return unserialize($_cachedRows);
	}

    // increments key version variable
	// held in memcache to invalidate
	// stale records
	public function incVersion($_cacheNameSpace='global') {
		
		$this->set('namespace',$_cacheNameSpace); // set namespace
		$this->increment($_cacheNameSpace); // increment version key
    }	
	
	// returns memcaceh stats
	//
    public function cacheStats()  
    {  
        return $this->__cache->getStats();
    } 
    
    // returns app cache key
	//
	public function cacheAppKey()  
    {  
        return $this->get('appkey');
    }  
	
	// loads config
	//
	private function loadConfig($_server)
	{
		if (!defined('ANS')) { throw new \Exception ('No configuration class specified. (CACHE)'); }
		
		$_class = '\\PAJ\\Application\\'. ANS. '\\config';
		
		$this->__config= new $_class();
		
		$this->set('datacached',false);

		// set server address
		//
		if ($_server === false)
		{
			$this->set('memcacheserver',$this->__config->get('memcacheServer'));
		} else {
			$this->set('memcacheserver',$_server);
		}
		$this->set('memcacheserverport',$this->__config->get('memcacheServerPort'));	
		
		$this->set('appcachekey',md5($this->__config->get('cacheKey')));
		$this->set('cacheenabled',$this->__config->get('cacheEnabled'));		
		$this->set('memcachettl',$this->__config->get('memcacheTTL'));	
	
	}
	
	// loads namespace version used for key control
	//
	private function loadVersion()
	{
        $_cacheNameSpace = $this->get('namespace'); // get namespace
        $_version = $this->cacheGet($_cacheNameSpace); // get version from cache
        
        if ($_version === false) { // if namespace not in cache reset to 1
            $_version = 1;
            $this->cacheSet($_cacheNameSpace, $_version,2592000); // save to cache note ttl!
        }
        
        $this->set('namespacekeyversion', $_version); // set version
        
	}
 
	public function set($key,$value)
	{
		$this->__[$key] = $value;
	}
		
  	public function get($variable)
	{
		return $this->__[$variable];
	}
	
  	public function query($_query,$_cacheNameSpace='global',$_cacheTTL=false)
	{
		$this->set('namespace',$_cacheNameSpace); // namespace
		
		if ($_cacheTTL) { $this->set('memcachettl',$_cacheTTL); } // TTL if specified
		
		$this->loadVersion(); // get namespace version
		
		return $this->cacheQuery($_query); // return query
	}
	
		
	public function __destruct()
	{
		
		unset($this->__config);
		unset($this->__);
		unset($this->__cache);
		

	}

	public function countKeys($_filter=false)
	{
		$_list = array();
		$_count=0;
		
			$allSlabs = $this->__cache->getExtendedStats('slabs');
			$items = $this->__cache->getExtendedStats('items');
			foreach($allSlabs as $server => $slabs) {
				foreach($slabs AS $slabId => $slabMeta) {
					$cdump = $this->__cache->getExtendedStats('cachedump',(int)$slabId);
					foreach($cdump AS $server => $entries) {
						if($entries) {
							foreach($entries AS $eName => $eData) {
							
								if ($_filter)
								{
									if (strrpos($eName,$_filter) === false) {continue;}
								}
								$_count++;
							}
						}
					}
				}
			}
			
			return $_count;
	}
	
	public function dumpCache($_filter=false)
	{
		$_list = array();
			$allSlabs = $this->__cache->getExtendedStats('slabs');
			$items = $this->__cache->getExtendedStats('items');
			foreach($allSlabs as $server => $slabs) {
				foreach($slabs AS $slabId => $slabMeta) {
					$cdump = $this->__cache->getExtendedStats('cachedump',(int)$slabId);
					foreach($cdump AS $server => $entries) {
						if($entries) {
							foreach($entries AS $eName => $eData) {
							
								if ($_filter)
								{
									if (strrpos($eName,$_filter) === false) {continue;}
								}
								$_list[$eName] = array(
									 'key' => $eName,
									 'server' => $server,
									 'slabId' => $slabId,
									 'detail' => $eData,
									 'age' => $items[$server]['items'][$slabId]['age'],
									 );
							}
						}
					}
				}
			}
			
			ksort($_list);
			$_dumpCacheHTML="<h1>v.". $this->get('memcacheversion'). " ". $this->get('memcacheserver'). ":". $this->get('memcacheserverport'). " TTL:". $this->get('memcachettl'). " Memcache Dump</h1><table cellspacing=\"0\" border=\"2\">\n". $this->show_array($_list, 1, 0). "</table>\n";
			
			return $_dumpCacheHTML;
	}
	
	private function do_offset($level){
		$offset = "";						 // offset for subarry 
		for ($i=1; $i<$level;$i++){
		$offset = $offset . "<td></td>";
		}
		return $offset;
	}

	private function show_array($array, $level, $sub){
		$_html=null;
		if (is_array($array) == 1){          // check if input is an array
		   foreach($array as $key_val => $value) {
			   $offset = "";
			   if (is_array($value) == 1){   // array is multidimensional
			   $_html=$_html. "<tr>";
			   $offset = $this->do_offset($level);
			   $_html=$_html. $offset . "<td>" . $key_val . "</td>";
			   $_html=$_html. $this->show_array($value, $level+1, 1);
			   }
			   else{                        // (sub)array is not multidim
			   if ($sub != 1){          	// first entry for subarray
				   $_html=$_html. "<tr nosub>";
				   $offset = $this->do_offset($level);
			   }
			   $sub = 0;
			   $_html=$_html. $offset . "<td main ".$sub." width=\"120\">" . $key_val . 
				   "</td><td width=\"120\">" . $value . "</td>"; 
			   $_html=$_html. "</tr>\n";
			   }
		   } //foreach $array
		}  
		else{ // argument $array is not an array
			return $_html;
		}
		
		return $_html;
	}
}



