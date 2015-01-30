<?php

namespace phpMultiLog\Transports;

/**
 *
 * @author user
 *        
 */
class errPdoPostgres {
	
	protected $db;
	protected $table = "phpmultilogerr";
	protected $query = false;
	
	/**
	 *
	 * @param array $transportParameters
	 *        	Transport initialization parameters
	 */
	public function __construct($transportParameters = array()) {
		
		if (isset ( $transportParameters ["table"] )) {
			$this->table = $transportParameters ["table"];
		} else {
			$this->table = "phpmultilogerr";
		}
		
		if (isset ( $transportParameters ["pdo"] ) && $transportParameters ["pdo"] instanceof \PDO) {
			$this->db = $transportParameters ["pdo"];
			try {
				$this->query = $this->db->prepare ( "INSERT INTO $this->table (appID, date, errno, errstr, errfile, errline, errcontext) VALUES (?, ?, ?, ?, ?, ?, ?)" );
				if (! $this->query) {
					syslog ( LOG_ERR, get_class () . " Cannot prepare statement because: " . json_encode ( $this->db->errorInfo () ) );
				}
			} catch ( \Exception $e ) {
				$this->query = false;
				syslog ( LOG_ERR, get_class () . " Cannot prepare statement because: " . json_encode ( $this->db->errorInfo () ) );
			}
		}
	} // function __construct
	
	/**
	 * Writes a record to database
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
		if (! $this->query) {
			return;
		}
		$logrecord = sprintf ( "App: %s - date: %s - errno: %s - errstr: %s - errfile: %s - errline: %s", $appID, $date, $errno, $errstr, $errfile, $errline );
		try {
			$this->query->bindParam ( 1, $appID, \PDO::PARAM_STR );
			$this->query->bindParam ( 2, $date, \PDO::PARAM_STR );
			$this->query->bindParam ( 3, $errno, \PDO::PARAM_INT );
			$this->query->bindParam ( 4, $errstr, \PDO::PARAM_STR );
			$this->query->bindParam ( 5, $errfile, \PDO::PARAM_STR );
			$this->query->bindParam ( 6, $errline, \PDO::PARAM_INT );
			$this->query->bindParam ( 7, $errcontext, \PDO::PARAM_STR );
			if (! $this->query->execute ()) {
				syslog ( LOG_ERR, get_class () . " Cannot write $logrecord to database because: " . json_encode ( $this->db->errorInfo () ) );
			}
		} catch ( \Exception $e ) {
			syslog ( LOG_ERR, get_class () . " Cannot write $logrecord to database because: " . json_encode ( $this->db->errorInfo () ) );
		}
	} // function log
} // class errPdoPostgres

?>