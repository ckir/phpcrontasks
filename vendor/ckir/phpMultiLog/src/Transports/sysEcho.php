<?php

namespace phpMultiLog\Transports;

/**
 *
 * @author user
 *        
 */
class sysEcho {
	
	/**
	 *
	 * @param array $transportParameters
	 *        	Transport initialization parameters
	 */
	public function __construct($transportParameters = array()) {
		if (php_sapi_name () !== 'cli') {
			echo "<pre>";
		}
	} // function __construct
	
	/**
	 */
	function __destruct() {
		if (php_sapi_name () !== 'cli') {
			echo "</pre>";
		}
	} // function __destruct
	
	/**
	 * Writes a record to a file
	 *
	 * @param string $appID        	
	 * @param string $date        	
	 * @param string $logType        	
	 * @param integer $logLevel        	
	 * @param unknown $message        	
	 * @param array $context        	
	 */
	public function log($appID, $date, $logType, $logLevel, $message, $context) {
		$logrecord = sprintf ( "%s - %s - %s", $date, $logType, $message ) . PHP_EOL;
		echo $logrecord;
	} // function log
} // class sysEcho

?>