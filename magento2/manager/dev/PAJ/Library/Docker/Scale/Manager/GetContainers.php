<?php
/**
 *
 *  Copyright (C) 2017 paj@gaiterjones.com
 */

namespace PAJ\Library\Docker\Scale\Manager;

//
// class to parse docker ps output
//
class GetContainers
{

	public function __construct() {

		$_containers=$this->getContainers();

		if ($_containers) {

			$_now = new \DateTime(null, new \DateTimeZone('Europe/London'));

			$_headerData=array('project','name','state','status');

			$this->set('success',true);
			$this->set('output',array(
				'docker' => array(
					'containers' => $_containers['containerdata'],
					'timestamp' => $_containers['timestamp']
				)
			));

		} else {

			$this->set('errormessage','No items found.');
		}
	}

	private function getContainers()
	{

		// CLI get live data from curl
		//
		$_execCmd="curl -sS --unix-socket /var/run/docker.sock http://v1.24/containers/json";
		$_exec = exec($_execCmd,$_output, $_status);		

		if (0 === $_status) {

			// ok - do something
			$_psJsonData=(json_decode($_output[0],true));
			$_psJsonDataLastModified=time();

		} else {
			// error
			throw new \Exception('Error accessing Docker PS JSON data');
		}


		$_containers=array();

		foreach ($_psJsonData as $_key => $_value)
		{
			array_push($_containers,array(
				'project' => $_value['Labels']['com.docker.compose.project'],
				'name' => $_value['Names'][0],
				'networksettings' => $_value['NetworkSettings'],
				'state' => $_value['State'],
				'status' => $_value['Status'],
				'upseconds' => $this->UpTime($_value['Status']),
				'up' => $_value['Status'],
			));

		}

		$_sortCriteria = array(
				'project' => array(SORT_ASC, SORT_STRING)
		 );

		$_containers=$this->MultiSort($_containers, $_sortCriteria, true);

		$_dt = new \DateTime(null, new \DateTimeZone('Europe/London'));
		$_dt->setTimestamp($_psJsonDataLastModified);

		return array(
			'containerdata' => $_containers,
			'timestamp' => $_dt->format('F d Y H:i:s')
		);
	}

	private function UpTime($_upText)
	{
		$_upTime=preg_replace("/[^0-9]/","",$_upText);

		if (strpos($_upText, 'seconds') !== false) {return ($_upTime);}
		if (strpos($_upText, 'minutes') !== false) {return ($_upTime*60);}
		if (strpos($_upText, 'hours') !== false) {return ($_upTime*3600);}
		if (strpos($_upText, 'days') !== false) {return ($_upTime*86400);}

		return ($_upText);
	}

	private function MultiSort($data, $sortCriteria, $caseInSensitive = true)
	{
	  if( !is_array($data) || !is_array($sortCriteria))
		return false;
	  $args = array();
	  $i = 0;
	  foreach($sortCriteria as $sortColumn => $sortAttributes)
	  {
		$colList = array();
		foreach ($data as $key => $row)
		{
		  $convertToLower = $caseInSensitive && (in_array(SORT_STRING, $sortAttributes) || in_array(SORT_REGULAR, $sortAttributes));
		  $rowData = $convertToLower ? strtolower($row[$sortColumn]) : $row[$sortColumn];
		  $colLists[$sortColumn][$key] = $rowData;
		}
		$args[] = &$colLists[$sortColumn];

		foreach($sortAttributes as $sortAttribute)
		{
		  $tmp[$i] = $sortAttribute;
		  $args[] = &$tmp[$i];
		  $i++;
		 }
	  }
	  $args[] = &$data;
	  call_user_func_array('array_multisort', $args);
	  return end($args);
	}

	protected function minify($_html,$_dev=false,$_sanitize=true)
	{
		if (!$_dev) {
			// minify live code
			return ($_sanitize ? $this->sanitize(\PAJ\Library\Minify\HTML::minify($_html,array('jsCleanComments' => true))) : \PAJ\Library\Minify\HTML::minify($_html,array('jsCleanComments' => true)));

		} else {

			return $_html;
		}
	}

	protected function sanitize($_html) {

		return (preg_replace('#\R+#', ' ', $_html));
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
