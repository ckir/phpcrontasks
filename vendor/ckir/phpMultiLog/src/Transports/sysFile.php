<?php

namespace phpMultiLog\Transports;

/**
 *
 * @author user
 *        
 */
class sysFile {
	
	protected $logfile;
	
	/**
	 *
	 * @param array $transportParameters
	 *        	Transport initialization parameters
	 */
	public function __construct($transportParameters = array()) {
		$this->logfile = $transportParameters ["filename"];
	} // function __construct
	
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
		$logrecord = sprintf ( "App: %s - date: %s - type: %s - level: %s - message: %s - context: %s", $appID, $date, $logType, $logLevel, $message, $context );
		if (! file_put_contents ( $this->logfile, $logrecord . PHP_EOL, FILE_APPEND )) {
			error_log ( "Cannot write $logrecord to " . $this->logfile . PHP_EOL );
		}
	} // function log
	
} // class sysFile

?>