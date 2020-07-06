<?php

/**
* Logging library for use with Rapid7
*
* Design inspired by KLogger library which is available at 
*   https://github.com/katzgrau/KLogger.git
*/

class R7Logger 
{
	//BSD syslog log levels
	/*
	 *  Emergency
	 *  Alert
	 *  Critical
	 *  Error
 	 *  Warning
	 *  Notice
	 *  Info
	 *  Debug
	 */

	// Rapid7 server address for receiving logs	
	const R7DOMAIN = '.data.logs.insight.rapid7.com';
	// Rapid7 server port for receiving logs by token
	const R7_PORT = 10000;
	// Rapid7 server port for receiving logs with TLS by token
	const R7_TLS_PORT = 20000;

	private $resource = null;

	private $_logToken = null;
	
	private $_datahubIPAddress = "";
	private $use_datahub = false;
	private $_datahubPort = 10000;

	private $use_host_name = false;
	private $_host_name = "";
	private $_host_id = "";
	
	private $_region = "";
	
	private $_use_json = false;

	private $severity = LOG_DEBUG;

	private $connectionTimeout;

	private $persistent = true;

	private $use_ssl = true;
	
	private static $_timestampFormat = 'Y-m-d G:i:s';

	/** @var R7Logger[]  */
	private static $m_instance = array();

	private $errno;
	
	private $errstr;

	public static function getLogger($token, $region, $persistent, $ssl, $severity, $datahubEnabled, $datahubIPAddress, $datahubPort, $host_id, $host_name, $host_name_enabled, $add_local_timestamp, $use_json)
	{
		if ( ! isset(self::$m_instance[$token]))
		{
			self::$m_instance[$token] = new R7Logger($token, $region, $persistent, $ssl, $severity, $datahubEnabled, $datahubIPAddress, $datahubPort, $host_id, $host_name, $host_name_enabled, $add_local_timestamp, $use_json);
		}

		return self::$m_instance[$token];
	}
	
	
	
	// Destroy singleton instance, used in PHPUnit tests
	public static function tearDown()
	{	
		self::$m_instance = array();
	}

	private function __construct($token, $region, $persistent, $ssl, $severity, $datahubEnabled, $datahubIPAddress, $datahubPort, $host_id, $host_name, $host_name_enabled, $add_local_timestamp, $use_json)
	{

		if ($datahubEnabled===true)
		{
			// Check if a DataHub IP Address has been entered	
			$this->validateDataHubIP($datahubIPAddress);	
			
			// set Datahub variable values			
			$this->_datahubIPAddress = $datahubIPAddress;
			$this->use_datahub = $datahubEnabled;
			$this->_datahubPort = $datahubPort;	
		
		
			// if datahub is being used the logToken should be set to null
			$this->_logToken = null;	
		}
		else   	// only validate the token when user is not using Datahub
		{
			$this->validateToken($token);	
 			$this->_logToken = $token;
		}	

		if ($host_name_enabled===true)
		{
			$this->use_host_name = $host_name_enabled;
		
			// check host name exist.  If no host name has been specified, get the host name from the local machine, use Key value pairing.		
			if ($host_name ==="")
			{
				$this->_host_name = gethostname();
			}
			else
			{
				$this->_host_name = $host_name;	
			}
			
		}
		else     // no host name desired to appear in logs
		{  
			$this->use_host_name = $host_name_enabled;
			$this->_host_name= "";			
		}		
		
		$this->_host_id = $host_id;
		
		$this->_region = $region;
		
		$this->_use_json = $use_json;
		
		
		// Set timestamp toggle
		$this->add_timestamp = $add_local_timestamp;
		
		$this->persistent = $persistent;

		//**** possible problem here with $ssl not sending.
		$this->use_ssl = $ssl;

		$this->severity = $severity;

		$this->connectionTimeout = (float) ini_get('default_socket_timeout');
	}


	public function __destruct()
	{
		$this->closeSocket();
	}


	public function validateToken($token){
		if (empty($token) ) {
			throw new InvalidArgumentException('Rapid7 Token was not provided in r7insight.php');
		} elseif(!preg_match("/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/", $token)) {
			throw new InvalidArgumentException('Rapid7 Token is not a valid UUID');
		}
	}

	public function validateDataHubIP($datahubIPAddress)
	{
	if (empty($datahubIPAddress) ) {
			throw new InvalidArgumentException('Rapid7 Datahub IP Address was not provided in r7insight.php');
		}
	}

	public function closeSocket()
	{
		if (is_resource($this->resource)){
			fclose($this->resource);
			$this->resource = null;
		}
	}

	public function isPersistent()
	{
		return $this->persistent;
	}

	public function isTLS()
	{
		return $this->use_ssl;
	}


	public function getToken()
	{
		return $this->_logToken;
	}

	public function getPort()
	{
		if ($this->isTLS())
		{
			return self::R7_TLS_PORT;
		}
		elseif ($this->isDatahub() )
		{
		 	return $this->_datahubPort;
		}
		else
		{
			return self::R7_PORT;
		}
	}
	
	
	// check if datahub is enabled
	public function isDatahub()
	{
		return $this->use_datahub;
	}
	
	
	public function isHostNameEnabled()
	{
	return $this->use_host_name;  
	}
	

	public function getAddress()
	{
		if ($this->isDatahub() )
		{
			return $this->_datahubIPAddress;
		}
		else
		{
			if($this->isTLS())
			{
				return "tls://" . $this->_region . self::R7DOMAIN;
			}
			else
			{
				return "tcp://" . $this->_region . self::R7DOMAIN;
			}
		}
	}

	public function isConnected()
	{
		return is_resource($this->resource) && !feof($this->resource);
	}

	private function createSocket()
	{
		$port = $this->getPort();
		
		$address = $this->getAddress();
		
		if ($this->isPersistent())
		{
			$resource = $this->my_pfsockopen($port, $address);
		}
		else
		{
			$resource = $this->my_fsockopen($port, $address);
		}
		
		if (is_resource($resource) && !feof($resource)) 
		{
			$this->resource = $resource;
		}
	}
	

	private function my_pfsockopen($port, $address)
	{
         return @pfsockopen($address, $port, $this->errno, $this->errstr, $this->connectionTimeout);
	}

	private function my_fsockopen($port, $address)
	{
		return @fsockopen($address, $port, $this->errno, $this->errstr, $this->connectionTimeout);
	}

	public function debug($line)
	{
		$this->log($line, LOG_DEBUG);
	}

	public function info($line)
	{
		$this->log($line, LOG_INFO);
	}

	public function notice($line)
	{
		$this->log($line, LOG_NOTICE);
	}

	public function warning($line)
	{
		$this->log($line, LOG_WARNING);
	}

	public function warn($line)
	{
		$this->warning($line);
	}

	public function error($line)
	{
		$this->log($line, LOG_ERR);
	}

	public function err($line)
	{
		$this->error($line);
	}

	public function critical($line)
	{
		$this->log($line, LOG_CRIT);
	}

	public function crit($line)
	{
		$this->critical($line);
	}

	public function alert($line)
	{
		$this->log($line, LOG_ALERT);
	}

	public function emergency($line)
	{
		$this->log($line, LOG_EMERG);
	}

	public function emerg($line)
	{
		$this->emergency($line);
	}

	public function log($line, $curr_severity)
	{
		$this->connectIfNotConnected();

		if ($this->severity >= $curr_severity) {
			$prefix = ($this->add_timestamp ? $this->_getTime($curr_severity) . ' - ' : '') . $this->_getLevel($curr_severity) . ' - ';
			$multiline = $this->substituteNewline($line);

			if ($this->_use_json)
			{
				$myObj = new stdClass();
				$myObj->time_stamp = $this->_getTime($curr_severity);			
				if ($this->isHostNameEnabled())
				{
					$myObj->host_name = $this->_host_name;
				}
				if($this->_host_id !== "")
				{
					$myObj->host_id = $this->_host_id;
				}
				$myObj->level = $this->_getLevel($curr_severity);			
				$myObj->message = $multiline;
				$myJSONMsg = "";
				$myJSONMsg = json_encode($myObj);
			
				$data = $myJSONMsg . PHP_EOL;
			}
			else
			{
				$prefix = ($this->add_timestamp ? $this->_getTime($curr_severity) . ' - ' : '') . $this->_getLevel($curr_severity) . ' - ';
								
				if ($this->isHostNameEnabled())
				{
					$prefix = "host_name=" . $this->_host_name . " " . $prefix;
				}

				if ($this->_host_id !== "")				
				{
					$prefix = "host_id=" . $this->_host_id . " " . $prefix;
				}
				
				$data = $prefix . $multiline . PHP_EOL;
			}				

			$this->writeToSocket($data);
		}
	}


	public function writeToSocket($line)
	{
		$finalLine = $this->_logToken . $line;
		
		if($this->isConnected())
		{
			fputs($this->resource, $finalLine);
		}
	}


	private function substituteNewline($line)
	{
		$unicodeChar = chr(13);

		$newLine = str_replace(PHP_EOL,$unicodeChar, $line);
				 
		return $newLine;
	}

	private function connectIfNotConnected()
	{
		if ($this->isConnected()){
			return;
		}
		$this->connect();
	}

	private function connect()
	{
		$this->createSocket();
	}

	private function _getTime()
	{
		return date(self::$_timestampFormat);
	}
	
	private function _getLevel($level)
	{
		switch ($level) {
			case LOG_DEBUG:
				return "DEBUG";
			case LOG_INFO:
				return "INFO";
			case LOG_NOTICE:
				return "NOTICE";
			case LOG_WARNING:
				return "WARN";
			case LOG_ERR:
				return "ERROR";
			case LOG_CRIT:
				return "CRITICAL";
			case LOG_ALERT:
				return "ALERT";
			case LOG_EMERG:
				return "EMERGENCY";
			default:
				return "LOG";
		}
	}
}
