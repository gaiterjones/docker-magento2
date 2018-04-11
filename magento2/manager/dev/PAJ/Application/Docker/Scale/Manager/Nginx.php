<?php
/**
 *  SCALE MANAGER NGINX
 *
 *  Copyright (C) 2017
 *
 *
 *  @who	   	PAJ
 *  @info   	paj@gaiterjones.com
 *  @license    blog.gaiterjones.com
 * 	
 */
 
namespace PAJ\Application\Docker\Scale\Manager;
 
class Nginx
{
	
	public static function updateNginx($_silent,$_forceUpdate)
	{
		$_upstreamScaleContainers=array();
		$_managerOutput=array();
		
		$_projectName=getenv('SCALE_MANAGER_PROJECTNAME');
		if (!$_projectName){ $_projectName='scaledemo';}

		$_scaleContainerServiceName=getenv('SCALE_CONTAINER_NAME');
		if (!$_scaleContainerServiceName){ $_scaleContainerServiceName='php-apache';}

		$_proxyNetworkName=getenv('PROXY_NETWORK_NAME');					
		if (!$_proxyNetworkName){ $_proxyNetworkName='proxy';}
		
		$_scaleContainerName=$_projectName.'_'.$_scaleContainerServiceName;
		$_success=false;
		
		// get docker container data
		//
		$_obj=new \PAJ\Library\Docker\Scale\Manager\GetContainers();
		
			$_output=$_obj->get('output');
			$_success=$_obj->get('success');
			$_managerOutput['getcontainers']=$_output;
			unset($_obj);
			
		if($_success) // got container data
		{

			foreach ($_output['docker']['containers'] as $_container)
			{

				// parse containers for scaled upstream container names
				//
				if (strpos($_container['name'],$_scaleContainerName)!== false)
				{
					// add to array
					//
					$_upstreamScaleContainers[]=$_container;
				}
			}
			
				if (!$_silent) {echo count($_upstreamScaleContainers). ' upstream docker containers found.'. "\n";}
				$_managerOutput['upstreamcontainers']=$_upstreamScaleContainers;
			
			// extract upstream configuration data from containers
			//
			foreach($_upstreamScaleContainers as $_server)
			{
				$_managerOutput['containerdata']=$_server;
				if (isset($_server['networksettings']['Networks'][$_server['project'].'_proxy']['IPAddress']))
				{
					$_containerIP=$_server['networksettings']['Networks'][$_server['project'].'_proxy']['IPAddress'];
					$_containerUpstreamServerConfig[]='server '. $_containerIP.':80';
					if (!$_silent) {echo $_server['name']. ' : '. $_containerIP. ' - '. $_server['up']. "\n";}
					
				} else {
					//throw new \Exception('Could not obtain IP address for '. $_server['name']);
				}
				
			}
				
			$_managerOutput['containerupstreamconfig']=$_containerUpstreamServerConfig;
		
			if (count($_containerUpstreamServerConfig) > 0)
			{
				
				// get current nginx upstream config
				//
				$_dynamicUpstream=new \PAJ\Library\Docker\Scale\Nginx\DynamicUpstream();
					$_nginxUpstreamServerConfig=$_dynamicUpstream->getUpstream($_projectName,'nginx',true);
					$_managerOutput['nginxupstreamserverconfig']=$_nginxUpstreamServerConfig;								
					if (!$_silent) {echo count($_nginxUpstreamServerConfig['output']).' upstream servers in nginx config.'. "\n". implode(';',$_nginxUpstreamServerConfig['output'])."\n";}	
						
				$_managerOutput['updatestream']='nothing to do';
			
				// compare nginx config with docker scale containers available
				//								
				
				if ($_nginxUpstreamServerConfig['output'] !== $_containerUpstreamServerConfig || $_forceUpdate)
				{
					
					// change
					//
					if (!$_silent) {echo 'change detected!'. "\n";}
					//$_managerOutput['changedetected']='true';
					$_managerOutput['changedetected']['timestamp']=(new \DateTime())->getTimestamp();
					
					// update nxinx upstream host config
					//
					$_updateUpstream=$_dynamicUpstream->updateUpstream($_projectName,'nginx',implode(';',$_containerUpstreamServerConfig));
					
					if (isset($_updateUpstream['output'][0]) && $_updateUpstream['output'][0] === 'add server failed')
					{
						if (!$_silent) {echo 'ERROR nginx upstream host configuration not updated : '. $_updateUpstream['output'][0]. "\n";}
						$_managerOutput['updatestream']='ERROR nginx upstream host configuration not updated : '. $_updateUpstream['output'][0];
						
					} else {
						
						if (!$_silent) {echo 'nginx upstream host configuration was updated '.  (new \DateTime())->format('d-m-Y H:i:s'). "\n";}
						$_managerOutput['updatestream']='nginx upstream host configuration was updated '. (new \DateTime())->format('d-m-Y H:i:s');
					}
					
					// increment namespace for cache
					//
					$_cache=new \PAJ\Library\Cache\Memcache();
						$_cache->incVersion($_projectName);
							unset($_cache);
					
				} else {
					
					// no change
					//
					if (!$_silent) {echo 'no upstream host configuration changes detected.'. "\n";}
					$_managerOutput['changedetected']='false';
					

							
				}
				
				unset($_dynamicUpstream);
				
			} else {
				
				$_managerOutput['updatestream']='ERROR - No upstream containers available!';
			}
		}	

		$_managerOutput['projectname']=$_projectName;
		$_managerOutput['servicename']=$_scaleContainerServiceName;
		$_managerOutput['scalecontainername']=$_scaleContainerName;
		$_managerOutput['scalecontainercount']=count($_containerUpstreamServerConfig);
					
		return $_managerOutput;
		
	}
}