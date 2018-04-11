<?php
/**
 *  Application CRON CLI for SCALE MANAGER APPLICATION
 *
 *  Copyright (C) 2017
 *
 *
 *  @who	   	PAJ
 *  @info   	paj@gaiterjones.com
 *  @license    blog.gaiterjones.com
 * 	
 */
 
namespace PAJ\Application\Docker\Scale\Manager\Cron;
 
class Cli extends CronController
{

	public function __construct() {
	
		parent::__construct();
		
		if (php_sapi_name() === 'cli') {
			$this->doCronCli();
		}

	}
	
	// -- command line / cron cli tasks
	//		
	private function doCronCli()
	{
	
		// commandline - cron
		//
		
			$_silent=false;
			$_forceUpdate=false;
		
			// cli cron jobs
			foreach($_SERVER['argv'] as $_value)
			{
				if ($_value==="silent") {$_silent=true;}
				if ($_value==="update") {$_forceUpdate=true;}
				
				if ($_silent) { error_reporting(0); }
				
				$_cmd=explode('=',$_value);
				  
				if (isset($_cmd[1])) // check for command=value
				{
					$_value=$_cmd[0];
					
				} 
				

				// scale manager CRON
				// monitor containers and update nginx dynamic upstream hosts configuration || varnish vcl
				//
				// usage:
				//      php cron.php silent scalemanager - silent, use in cron
				//      php cron.php scalemanager - run with debug info
				//      php cron.php update scalemanager -  force an update
				//
				// for varnish
				//      php cron.php getvarnishvcl - vcl config from varnish server
				//      php cron.php getvarnishbackend - backend list 
				//      php cron.php getvarnishbanner
				//      php cron.php getvarnishparam
				//
				//
				if ($_value==="scalemanager") {
				
					$_proxyType='NONE';
					
					try
					{					
						$_proxyType=getenv('PROXY_TYPE');
						
						if ($_proxyType==='nginx')
						{
							// NGINX
							//
							$_managerOutput=\PAJ\Application\Docker\Scale\Manager\Nginx::updateNginx($_silent,$_forceUpdate);
						}
						
						if ($_proxyType==='varnish')
						{

							// VARNISH
							//
							$_managerOutput=\PAJ\Application\Docker\Scale\Manager\Varnish::updateVarnish($_silent,$_forceUpdate);

						}						
						
							
					}
					
					catch (\Exception $e)
					{
						// catch bad guys
						$_managerOutput['clierror']='ERROR - '. $e;
					}
					
					$_managerOutput['proxytype']=$_proxyType;
					$_managerOutput['heartbeat']['timestamp']= (new \DateTime())->getTimestamp();
			
					// cache manager output
					//
					$_key='docker-scalemanager-output';
					\PAJ\Library\Cache\Helper::setCachedString(array('manageroutput' => $_managerOutput),$_key,3600);									
					
					if (!$_silent) {
						
						//print_r(array('manageroutput' => $_managerOutput));
						
						if (!$_silent) {
							if (isset($_managerOutput['error']))
							{
								echo $_proxyType. ' '.$_managerOutput['error']. "\n";
							} else {
								echo 'no errors'. "\n";
							}
							echo 'done.'. "\n";
						}
					}
					
						
					exit;				
				
				}
				
				if ($_value==="getvarnishvcl") {
					
					$_varnish=new \PAJ\Library\Docker\Scale\Varnish\VCL('varnish');
					$_output=$_varnish->getVCL();
					
					print_r($_output);
					exit;
				}
				
				if ($_value==="getvarnishparam") {
					
					$_varnish=new \PAJ\Library\Docker\Scale\Varnish\VCL('varnish');
					$_output=$_varnish->getParam();
					
					print_r($_output);
					exit;
				}	

				if ($_value==="getvarnishbanner") {
					
					$_varnish=new \PAJ\Library\Docker\Scale\Varnish\VCL('varnish');
					$_output=$_varnish->getBanner();
					
					print_r($_output);
					exit;
				}

				if ($_value==="getvarnishbackend") {
					
					$_varnish=new \PAJ\Library\Docker\Scale\Varnish\VCL('varnish');
					$_output=$_varnish->getBackendList();
					
					print_r($_output);
					exit;
				}				

				if ($_value==="help") {
					
				echo "\n\n".'###'."\n".'scale manager CRON' ."\n".
						'monitor containers and update nginx dynamic upstream hosts configuration || varnish vcl' ."\n".
						'usage:' ."\n".
						'php cron.php silent scalemanager - silent, use in cron' ."\n".
						'php cron.php scalemanager - run with debug info' ."\n".
						'php cron.php update scalemanager -  force an update' ."\n".
						'for varnish' ."\n".
						'php cron.php getvarnishvcl - vcl config from varnish server' ."\n".
						'php cron.php getvarnishbackend - backend list ' ."\n".
						'php cron.php getvarnishbanner' ."\n".
						'php cron.php getvarnishparam' ."\n".
						'###'."\n\n";

					exit;
				}				
			}
				
		
			exit; // cron finished
		
	}

}