<?php

namespace phpMultiLog\Transports;

/**
 *
 * @author user
 *        
 */
class sysPdoPostgres {
	
	protected $db;
	protected $table = "phpmultilogsys";
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
			$this->table = "phpmultilogsys";
		}
		
		if (isset ( $transportParameters ["pdo"] ) && $transportParameters ["pdo"] instanceof \PDO) {
			$this->db = $transportParameters ["pdo"];
			try {
				$this->query = $this->db->prepare ( "INSERT INTO $this->table (appid, date, logtype, loglevel, message, context) VALUES (?, ?, ?, ?, ?, ?)" );
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
		if (! $this->query) {
			return;
		}
		$logrecord = sprintf ( "App: %s - date: %s - type: %s - level: %s - message: %s - context: %s", $appID, $date, $logType, $logLevel, $message, $context );
		try {
			$this->query->bindParam ( 1, $appID, \PDO::PARAM_STR );
			$this->query->bindParam ( 2, $date, \PDO::PARAM_STR );
			$this->query->bindParam ( 3, $logType, \PDO::PARAM_STR );
			$this->query->bindParam ( 4, $logLevel, \PDO::PARAM_INT );
			$this->query->bindParam ( 5, $message, \PDO::PARAM_STR );
			$this->query->bindParam ( 6, $context, \PDO::PARAM_STR );
			
			if (! $this->query->execute ()) {
				syslog ( LOG_ERR, get_class () . " Cannot write $logrecord to database because: " . json_encode ( $this->db->errorInfo () ) );
			}
		} catch ( \Exception $e ) {
			syslog ( LOG_ERR, get_class () . " Cannot write $logrecord to database because: " . json_encode ( $this->db->errorInfo () ) );
		}
	} // function log
	
} // class sysPdoPostgres

?>