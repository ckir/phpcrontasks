<?php

namespace phpMultiLog;

require_once dirname ( __FILE__ ) . '/phpMultiLogException.php';
require_once dirname ( __FILE__ ) . '/phpMultiLogShutdownProcess.php';

/**
 *
 * @author user
 *        
 */
class phpMultiLog {
	/**
	 * System is unusable
	 */
	const EMERG = 0; // Emergency: system is unusable
	
	/**
	 * Action must be taken immediately
	 *
	 * Example: Entire website down, database unavailable, etc. This should
	 * trigger the SMS alerts and wake you up.
	 */
	const ALERT = 1; // Alert: action must be taken immediately
	
	/**
	 * Critical conditions
	 *
	 * Example: Application component unavailable, unexpected exception.
	 */
	const CRIT = 2; // Critical: critical conditions
	
	/**
	 * Runtime errors that do not require immediate action but should typically
	 * be logged and monitored.
	 */
	const ERR = 3; // Error: error conditions
	
	/**
	 * Exceptional occurrences that are not errors.
	 *
	 * Example: Use of deprecated APIs, poor use of an API, undesirable things
	 * that are not necessarily wrong.
	 */
	const WARN = 4; // Warning: warning conditions
	
	/**
	 * Normal but significant events.
	 */
	const NOTICE = 5; // Notice: normal but significant condition
	
	/**
	 * Interesting events.
	 *
	 * Example: User logs in, SQL logs.
	 */
	const INFO = 6; // Informational: informational messages
	
	/**
	 * Detailed debug information.
	 */
	const DEBUG = 7; // Debug: debug messages
	
	/**
	 * Application identifier
	 *
	 * @var string $appID
	 */
	static $appID = "";
	
	/**
	 * Folder location for custom transports
	 *
	 * @var string $customTransportsDir
	 */
	protected $customTransportsDir = "";
	
	/**
	 * All available transports
	 *
	 * @var array $allTransports
	 */
	static $allTransports = array ();
	
	/**
	 * Transports for logging
	 *
	 * @var array $logTransports
	 */
	static $logTransports = array ();
	
	/**
	 * Transports for error/exceptions logging
	 *
	 * @var array $errTransports
	 */
	static $errTransports = array ();
	
	/**
	 * Variables that should not be included in logs
	 *
	 * @var array $errSecrets
	 */
	static $errSecrets = array ();
	
	/**
	 * Custom shutdown handler
	 *
	 * @var phpMultiLogShutdownProcess $shutdownHandler
	 */
	static $shutdownHandler = null;
	

	/**
	 * @param string $appID A unique id for the application
	 * @param string $handleErrors Set to false to disable error and unhandled exceptions logging
	 * @param string $customTransportsDir Folder location for custom transports
	 * @throws phpMultiLogException
	 */
	public function __construct($appID = null, $handleErrors = true, $customTransportsDir = null) {
		self::$appID = $appID;
		
		// Get all available transports
		try {
			$iterator = new \DirectoryIterator ( dirname ( __FILE__ ) . "/Transports" );
			foreach ( $iterator as $fileinfo ) {
				if ($fileinfo->isFile () && $fileinfo->getExtension () === "php") {
					self::$allTransports [$fileinfo->getBasename ( ".php" )] = $fileinfo->getRealPath ();
				}
			}
			if ($customTransportsDir) {
				$iterator = new \DirectoryIterator ( $customTransportsDir );
				foreach ( $iterator as $fileinfo ) {
					if ($fileinfo->isFile () && $fileinfo->getExtension () === "php") {
						self::$allTransports [$fileinfo->getBasename ( ".php" )] = $fileinfo->getRealPath ();
					}
				}
			}
		} catch ( \Exception $e ) {
			throw new phpMultiLogException ( $e->getMessage (), $e->getCode () );
		}
		
		// Set error handlers
		if ($handleErrors) {
			set_error_handler ( array (
					$this,
					"customErrorHandler" 
			) );
			
			self::$shutdownHandler = new phpMultiLogShutdownProcess ( array (
					$this,
					"checkForFatal" 
			) );
			self::$shutdownHandler->register ();
			
			ini_set ( "display_errors", "off" );
			
			set_exception_handler ( array (
					$this,
					"customExceptionHandler" 
			) );
		}
	} // function __construct
	
	/**
	 * Unregister shutdownHandler
	 */
	public function __destruct() {
		if (self::$shutdownHandler) {
			self::$shutdownHandler->unregister ();
		}
	} // function __destruct
	
	/**
	 * Checks for a fatal error, work around for set_error_handler not working on fatal errors.
	 */
	static function checkForFatal() {
		$error = error_get_last ();
		if (($error ["type"] == E_ERROR) || ($error ["type"] == E_USER_ERROR)) {
			self::customErrorHandler ( $error ["type"], $error ["message"], $error ["file"], $error ["line"], array () );
			exit ( 1 );
		}
	} // function check_for_fatal
	
	/**
	 * Write log record for error
	 *
	 * @param unknown $errno        	
	 * @param unknown $errstr        	
	 * @param unknown $errfile        	
	 * @param unknown $errline        	
	 * @return boolean
	 */
	static function customErrorHandler($errno, $errstr, $errfile, $errline, $errcontext) {
		// error was suppressed with the @-operator
		if (0 === error_reporting ()) {
			return true;
		}
		// This error code is not included in error_reporting
		if (! (error_reporting () & $errno)) {
			return true;
		}
		
		// Remove secret variables from content
		foreach ( self::$errSecrets as $secret ) {
			self::array_unset_recursive ( $errcontext, $secret );
		}
		$errcontext = json_encode ( $errcontext );
		
		// Send it to transports
		$ts = self::udate ( 'Y-m-d H:i:s.u' );
		foreach ( self::$errTransports as $transport => $parameters ) {
			$adapter = __NAMESPACE__ . '\\Transports\\' . $transport;
			$adapter = new $adapter ( $parameters );
			$adapter->log ( self::$appID, $ts, $errno, $errstr, $errfile, $errline, $errcontext );
		}
		
		// Don't execute PHP internal error handler
		return true;
	} // function customErrorHandler
	
	/**
	 * Write log records for unhandled Exceptions
	 *
	 * @param \Exception $ex        	
	 */
	static protected function customExceptionHandler(\Exception $ex) {
		$errno = $ex->getCode ();
		$errstr = $ex->getMessage ();
		$errfile = $ex->getFile ();
		$errline = $ex->getLine ();
		$errcontext = $ex->getTrace ();
		
		$errcontext = json_encode ( $errcontext );
		
		// Send it to transports
		$ts = self::udate ( 'Y-m-d H:i:s.u' );
		foreach ( self::$errTransports as $transport => $parameters ) {
			$adapter = __NAMESPACE__ . '\\Transports\\' . $transport;
			$adapter = new $adapter ( $parameters );
			$adapter->log ( self::$appID, $ts, $errno, $errstr, $errfile, $errline, $errcontext );
		}
	} // function ccustomExceptionHandler
	
	/**
	 * Add a transport for error/exception log
	 *
	 * @param string $transport        	
	 * @param array $parameters        	
	 * @throws phpMultiLogException
	 */
	public function errTransportAdd($transport, $parameters = array()) {
		try {
			if (! array_key_exists ( $transport, self::$allTransports )) {
				throw new phpMultiLogException ( "Unknown transport" );
			}
			self::$errTransports [$transport] = $parameters;
			require_once self::$allTransports [$transport];
		} catch ( \Exception $e ) {
			throw new phpMultiLogException ( $e->getMessage (), $e->getCode () );
		}
	} // function errTransportAdd
	
	/**
	 * Add Variables to be excluded from logs
	 *
	 * @param array $variables        	
	 */
	public function errSecretsAdd($variables) {
		if (! is_array ( $variables )) {
			throw new phpMultiLogException ( "Parameter should be array" );
		}
		self::$errSecrets = $variables;
	} // function errSecretsAdd
	
	/**
	 * Writes a log record to all transports
	 *
	 * @param string $transport        	
	 * @param integer $level        	
	 * @param array $parameters        	
	 * @throws phpMultiLogException
	 */
	public function logTransportAdd($transport, $level = self::DEBUG, $parameters = array()) {
		try {
			if (! array_key_exists ( $transport, self::$allTransports )) {
				throw new phpMultiLogException ( "Unknown transport" );
			}
			if (! $this->logtype ( $level )) {
				throw new phpMultiLogException ( "Unknown log level" );
			}
			$parameters [get_class () . "TransportLevel"] = $level;
			self::$logTransports [$transport] = $parameters;
			require_once self::$allTransports [$transport];
		} catch ( \Exception $e ) {
			throw new phpMultiLogException ( $e->getMessage (), $e->getCode () );
		}
	} // function logTransportAdd
	
	/**
	 *
	 * @param integer $level        	
	 * @param unknown $message        	
	 * @param array $context        	
	 */
	private function log($level, $message, array $context = array()) {
		$logdate = $this->udate ( 'Y-m-d H:i:s.u' );
		$logtype = $this->logtype ( $level );
		
		// Send it to transports
		foreach ( self::$logTransports as $transport => $parameters ) {
			if ($level > $parameters [get_class () . "TransportLevel"]) {
				continue;
			}
			
			if (! is_string ( $message )) {
				$message = json_encode ( $message );
			}
			
			if (count ( $context ) > 0) {
				$context = json_encode ( $context );
			} else {
				$context = null;
			}
			
			$adapter = __NAMESPACE__ . '\\Transports\\' . $transport;
			$adapter = new $adapter ( $parameters );
			$adapter->log ( self::$appID, $logdate, $logtype, $level, $message, $context );
		}
	} // function log
	
	/**
	 * @param unknown $message
	 * @param array $context
	 */
	public function logEmergency($message, array $context = array()) {
		$this->log ( self::EMERG, $message, $context);
	} // function logEmergency
	
	/**
	 * @param unknown $message
	 * @param array $context
	 */
	public function logAlert($message, array $context = array()) {
		$this->log ( self::ALERT, $message, $context);
	} // function logAlert
	
	/**
	 * @param unknown $message
	 * @param array $context
	 */
	public function logCritical($message, array $context = array()) {
		$this->log ( self::CRIT, $message, $context);
	} // function logCritical
	
	/**
	 * @param unknown $message
	 * @param array $context
	 */
	public function logError($message, array $context = array()) {
		$this->log ( self::ERR, $message, $context);
	} // function logError
	
	/**
	 * @param unknown $message
	 * @param array $context
	 */
	public function logWarn($message, array $context = array()) {
		$this->log ( self::WARN, $message, $context );
	} // function logWarn
	
	/**
	 * @param unknown $message
	 * @param array $context
	 */
	public function logNotice($message, array $context = array()) {
		$this->log ( self::NOTICE, $message, $context );
	} // function logNotice
	
	/**
	 * @param unknown $message
	 * @param array $context
	 */
	public function logInfo($message, array $context = array()) {
		$this->log ( self::INFO, $message, $context );
	} // function logInfo
	
	/**
	 * @param unknown $message
	 * @param array $context
	 */
	public function logDebug($message, array $context = array()) {
		$this->log ( self::DEBUG, $message, $context );
	} // function logDebug
	
	/**
	 * @param \Exception $ex
	 */
	public function logException(\Exception $ex) {
		$this->customExceptionHandler($ex);
	} // function logException
	
	/**
	 *
	 * @param unknown $array        	
	 * @param unknown $remove        	
	 */
	private static function array_unset_recursive(&$array, $unwanted_key) {
		unset ( $array [$unwanted_key] );
		foreach ( $array as &$value ) {
			if (is_array ( $value )) {
				self::array_unset_recursive ( $value, $unwanted_key );
			}
		}
	} // function array_unset_recursive
	
	/**
	 *
	 * @param number $loglevel        	
	 * @return string
	 */
	private function logtype($loglevel) {
		$types = array (
				self::EMERG => 'Emergency',
				self::ALERT => 'Alert',
				self::CRIT => 'Critical',
				self::ERR => 'Error',
				self::WARN => 'Warning',
				self::NOTICE => 'Notice',
				self::INFO => 'Informational',
				self::DEBUG => 'Debug' 
		);
		
		if (isset ( $types [$loglevel] )) {
			return $types [$loglevel];
		} else {
			return null;
		}
	} // function logtype
	
	/**
	 * generate a datetime with microseconds and timezone
	 *
	 * @param string $format        	
	 * @param string $utimestamp        	
	 * @return string
	 */
	private static function udate($format = 'u', $utimestamp = null) {
		if (is_null ( $utimestamp ))
			$utimestamp = microtime ( true );
		
		$timestamp = floor ( $utimestamp );
		$milliseconds = round ( ($utimestamp - $timestamp) * 1000000 );
		
		return date ( preg_replace ( '`(?<!\\\\)u`', $milliseconds, $format ), $timestamp );
	} // function udate
} // class phpMultiLog


