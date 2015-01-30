<?php

namespace phpMultiLog\Transports;

/**
 *
 * @author user
 *        
 */
class sysStdout {
	
	protected $stdout;
	
	/**
	 *
	 * @param array $transportParameters
	 *        	Transport initialization parameters
	 */
	function __construct($transportParameters = array()) {
		$this->stdout = fopen ( 'php://stdout', 'w' );
		if (! $this->stdout) {
			error_log ( "Cannot open stdout" );
		}
	} // function __construct
	
	/**
	 */
	function __destruct() {
		fclose ( $this->stdout );
	} // function __destruct
	
	/**
	 *
	 * @param string $appID        	
	 * @param string $date        	
	 * @param string $logType        	
	 * @param integer $logLevel        	
	 * @param unknown $message        	
	 * @param array $context        	
	 */
	public function log($appID, $date, $logType, $logLevel, $message, $context) {
		$logrecord = sprintf ( "App: %s - date: %s - type: %s - level: %s - message: %s - context: %s", $appID, $date, $logType, $logLevel, $message, $context ) . PHP_EOL;
		if ($this->stdout) {
			if (! fwrite ( $this->stdout, $logrecord )) {
				error_log ( "Cannot write [$logrecord] to stdout" );
			}
		}
	} // function log()
} // class sysStdout

?>