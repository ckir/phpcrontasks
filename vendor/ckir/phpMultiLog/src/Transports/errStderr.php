<?php

namespace phpMultiLog\Transports;

class errStderr {
	
	protected $stderr;
	
	/**
	 *
	 * @param array $transportParameters
	 *        	Transport initialization parameters
	 */
	public function __construct($transportParameters = array()) {
		$this->stderr = fopen ( 'php://stderr', 'w' );
		if (! $this->stderr) {
			error_log ( "Cannot open stderr" );
		}
	} // function __construct
	
	/**
	 */
	function __destruct() {
		fclose ( $this->stderr );
	} // function __destruct
	
	/**
	 * Writes a record to stderr
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
		$logrecord = sprintf ( "App: %s - date: %s - errno: %s - errstr: %s - errfile: %s - errline: %s", $appID, $date, $errno, $errstr, $errfile, $errline ) . PHP_EOL;
		if ($this->stderr) {
			if (! fwrite ( $this->stderr, $logrecord )) {
				error_log ( "Cannot write $logrecord to stderr" );
			}
		}
	} // function log
} // class errStderr
