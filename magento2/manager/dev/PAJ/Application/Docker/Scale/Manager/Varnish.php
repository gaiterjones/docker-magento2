<?php
/**
 *  SCALE MANAGER VARNISH
 *
 *  Copyright (C) 2020
 *
 *
 *  @who	   	PAJ
 *  @info   	paj@gaiterjones.com
 *  @license    blog.gaiterjones.com
 *
 */

namespace PAJ\Application\Docker\Scale\Manager;

class Varnish
{

	public static function updateVarnish($_silent,$_forceUpdate)
	{
		$_upstreamScaleContainers=array();
		$_managerOutput=array();

		$_projectName=getenv('SCALE_MANAGER_PROJECTNAME');
		if (!$_projectName){ $_projectName='magento2';}

		$_projectDomainName=getenv('APPDOMAIN');
		if (!$_projectDomainName){ $_projectDomainName='dev.com';}

		$_magentoHostName=getenv('MAGENTO_HOSTNAME');
		if (!$_magentoHostName){ $_magentoHostName='magento2';}

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

			// init upstream containers array
			//
			$_managerOutput['upstreamcontainers']=$_upstreamScaleContainers;

			// extract upstream configuration data from containers
			//
			foreach($_upstreamScaleContainers as $_server)
			{
				$_managerOutput['containerdata']=$_server;

				$_containerIP=false;

				if (isset($_server['networksettings']['Networks'][$_server['project'].'_wwwserver']['IPAddress']))
				{
					$_containerIP=$_server['networksettings']['Networks'][$_server['project'].'_wwwserver']['IPAddress'];
					$_containerUpstreamServerConfig[]=array(
						'name'=>str_replace('/','',$_server['name']),
						'ip' => $_containerIP
					);
				}
				if (!$_silent) {echo $_server['name']. ' : '. $_containerIP. ' - '. $_server['up']. "\n";}
			}

			$_managerOutput['containerupstreamconfig']=$_containerUpstreamServerConfig;
			$_managerOutput['scalechange']['detected']='false';

			if (count($_containerUpstreamServerConfig) > 0)
			{

				// generate new vcl config
				//
				$_varnishVCLUpdateConfig = "";

				//$_probe_file = "/health_check.php";
				$_probe_file = "/pub/health_check.php";
				$_timeout="60";
				$_interval="120";
				$_window="10";
				$_threshold="5";

				foreach ($_containerUpstreamServerConfig as $_config) {

					$ip=$_config['ip'];
					$name=str_replace('-','_',$_config['name']);
					$_varnishVCLUpdateConfig .= "backend $name {\n\t.host = \"$ip\";\n\t.port = \"80\";\n\t.first_byte_timeout = 600s;\n\t.probe = {.request =  \"GET ". $_probe_file. " HTTP/1.1\" \"Host: $_magentoHostName.$_projectDomainName\" \"Connection: close\" \"Accept: text/html\";.timeout = ".$_timeout."s;.interval = ".$_interval."s;.window = ".$_window.";.threshold = ".$_threshold.";}\n}\n";
				}

				$_varnishVCLUpdateConfig .= "sub vcl_init {\n\tnew cluster1 = directors.round_robin();\n";

				foreach ($_containerUpstreamServerConfig as $_config) {

					$ip=$_config['ip'];
					$name=str_replace('-','_',$_config['name']);
					$_varnishVCLUpdateConfig .= "\tcluster1.add_backend($name);\n";
				}

				$_varnishVCLUpdateConfig .= "}\n";
				$_varnishVCLUpdateConfig .= "sub vcl_recv {set req.backend_hint = cluster1.backend();}\n";

				$_managerOutput['varnishvclupdateconfig']= explode("\n",$_varnishVCLUpdateConfig);

				// get cached vcl update config
				//
				$_key='docker-scalemanager-varnishvclupdate';
				$_varnishVCLConfig=\PAJ\Library\Cache\Helper::getCachedStringWithNameSpace($_key,'VARNISHVCLUPDATE',false);

				// detect change by comparing cached vcl config to generated config || if update forced
				//
				if ($_varnishVCLConfig !== $_varnishVCLUpdateConfig || $_forceUpdate)
				{
					// change!!
					//
					try
					{
						$_varnish=new \PAJ\Library\Docker\Scale\Varnish\VCL('varnish');
						$_varnishVCLUpdate=$_varnish->updateVCL($_varnishVCLUpdateConfig);
						$_managerOutput['varnishvclupdate']=explode("\n",$_varnishVCLUpdate);


						if (!$_silent) {echo 'change detected, VARNISH VCL was updated.'. "\n";}

						$_managerOutput['scalechange']['detected']='true';
						$_managerOutput['scalechange']['timestamp']=(new \DateTime())->getTimestamp();

						// increment namespace for VARNISHVCLUPDATE
						//
						$_cache=new \PAJ\Library\Cache\Memcache();
							$_cache->incVersion('VARNISHVCLUPDATE');
								unset($_cache);

						\PAJ\Library\Cache\Helper::setCachedStringWithNameSpace($_varnishVCLUpdateConfig,$_key,'VARNISHVCLUPDATE',0);
						if (!$_silent) {echo 'varnish vcl configuration was updated '.  (new \DateTime())->format('d-m-Y H:i:s'). "\n";}
						$_managerOutput['updatestream']='varnish vcl configuration was updated '. (new \DateTime())->format('d-m-Y H:i:s');
					}

					catch (\Exception $e)
					{
						// increment namespace for VARNISHVCLUPDATE
						//
						$_cache=new \PAJ\Library\Cache\Memcache();
							$_cache->incVersion('VARNISHVCLUPDATE');
								unset($_cache);

						// Error during update
						$_managerOutput['error']=$e->getMessage();
						if (!$_silent) {echo 'ERROR - '.$e->getMessage(). "\n";}
					}

				} else {

					// no change
					//
					if (!$_silent) {echo 'no upstream host configuration changes detected.'. "\n";}

				}


			} else {

				$_managerOutput['updatestream']='ERROR - No upstream containers available!';
				if (!$_silent) {echo 'ERROR - No upstream containers available!'. "\n";}
			}
		}

		$_managerOutput['projectname']=$_projectName;
		$_managerOutput['servicename']=$_scaleContainerServiceName;
		$_managerOutput['scalecontainername']=$_scaleContainerName;
		$_managerOutput['scalecontainercount']=count($_containerUpstreamServerConfig);

		return $_managerOutput;

	}
}
