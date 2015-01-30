<?php

namespace phpMultiLog\Transports;

/**
 *
 * @author user
 *        
 */
class errFile {
	
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
	 * @param integer $errno        	
	 * @param string $errstr        	
	 * @param string $errfile        	
	 * @param string $errline        	
	 * @param string $errcontext        	
	 */
	public function log($appID, $date, $errno, $errstr, $errfile, $errline, $errcontext) {
		$logrecord = sprintf ( "App: %s - date: %s - errno: %s - errstr: %s - errfile: %s - errline: %s", $appID, $date, $errno, $errstr, $errfile, $errline );
		if (! file_put_contents ( $this->logfile, $logrecord . PHP_EOL, FILE_APPEND )) {
			error_log ( "Cannot write $logrecord to " . $this->logfile . PHP_EOL );
		}
	} // function log
} // class errFile

?>