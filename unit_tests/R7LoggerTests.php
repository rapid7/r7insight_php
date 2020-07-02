<?php
	use PHPUnit\Framework\TestCase;

	class R7LoggerTest extends TestCase
	{
		public $token = 'token';
		public $region = 'eu';
		public $persistent = true;
		public $ssl = true;
		public $severity = LOG_DEBUG;
		public $datahubEnabled = false;
		public $datahubIPAddress = '';
		public $datahubPort = 10000;
		public $host_id = '123-123';
		public $host_name = 'car';
		public $host_name_enabled = true;
		public $add_local_timestamp = true;
		public $use_json = true;

		public function testMultiplyConnections()
		{
			$logFirst = R7Logger::getLogger('token1', $this->region, $this->persistent, $this->ssl, $this->severity, $this->datahubEnabled, $this->datahubIPAddress, $this->datahubPort, $this->host_id, $this->host_name, $this->host_name_enabled, $this->add_local_timestamp, $this->use_json);
			$logSecond = R7Logger::getLogger('token2', $this->region, $this->persistent, $this->ssl, $this->severity, $this->datahubEnabled, $this->datahubIPAddress, $this->datahubPort, $this->host_id, $this->host_name, $this->host_name_enabled, $this->add_local_timestamp, $this->use_json);
			$logThird = R7Logger::getLogger('token3', $this->region, $this->persistent, $this->ssl, $this->severity, $this->datahubEnabled, $this->datahubIPAddress, $this->datahubPort, $this->host_id, $this->host_name, $this->host_name_enabled, $this->add_local_timestamp, $this->use_json);

			$this->assertNotEquals('token1', $logSecond->getToken());
			$this->assertNotEquals('token2', $logThird->getToken());

			$this->assertEquals('token1', $logFirst->getToken());
			$this->assertEquals('token2', $logSecond->getToken());
			$this->assertEquals('token3', $logThird->getToken());
		}

		public function testIsPersistent()
		{
			$log = R7Logger::getLogger($this->token, $this->region, false, $this->ssl, $this->severity, $this->datahubEnabled, $this->datahubIPAddress, $this->datahubPort, $this->host_id, $this->host_name, $this->host_name_enabled, $this->add_local_timestamp, $this->use_json);

			$this->assertFalse($log->isPersistent());

			$this->tearDown();

			$log = R7Logger::getLogger($this->token, $this->region, $this->persistent, $this->ssl, $this->severity, $this->datahubEnabled, $this->datahubIPAddress, $this->datahubPort, $this->host_id, $this->host_name, $this->host_name_enabled, $this->add_local_timestamp, $this->use_json);

			$this->assertTrue($log->isPersistent());
		}

		public function testIsTLS()
		{
			$log = R7Logger::getLogger($this->token, $this->region, $this->persistent, false, $this->severity, $this->datahubEnabled, $this->datahubIPAddress, $this->datahubPort, $this->host_id, $this->host_name, $this->host_name_enabled, $this->add_local_timestamp, $this->use_json);

			$this->assertFalse($log->isTLS());

			$this->tearDown();

			$log = R7Logger::getLogger($this->token, $this->region, $this->persistent, $this->ssl, $this->severity, $this->datahubEnabled, $this->datahubIPAddress, $this->datahubPort, $this->host_id, $this->host_name, $this->host_name_enabled, $this->add_local_timestamp, $this->use_json);

			$this->assertTrue($log->isTLS());
		}

		public function testGetPort()
		{

			$log = R7Logger::getLogger($this->token, $this->region, $this->persistent, false, $this->severity, $this->datahubEnabled, $this->datahubIPAddress, 20000, $this->host_id, $this->host_name, $this->host_name_enabled, $this->add_local_timestamp, $this->use_json);


			$this->assertEquals(10000, $log->getPort());

			$this->tearDown();

			$log = R7Logger::getLogger($this->token, $this->region, $this->persistent, true, $this->severity, $this->datahubEnabled, $this->datahubIPAddress, $this->datahubPort, $this->host_id, $this->host_name, $this->host_name_enabled, $this->add_local_timestamp, $this->use_json);

			$this->assertEquals(20000, $log->getPort());
		}

		public function testGetAddress()
		{
			$log = R7Logger::getLogger($this->token, $this->region, $this->persistent, false, $this->severity, $this->datahubEnabled, $this->datahubIPAddress, $this->datahubPort, $this->host_id, $this->host_name, $this->host_name_enabled, $this->add_local_timestamp, $this->use_json);

			$this->assertEquals('tcp://eu.data.logs.insight.rapid7.com', $log->getAddress());

			$this->tearDown();
			$log = R7Logger::getLogger($this->token, $this->region, $this->persistent, true, $this->severity, $this->datahubEnabled, $this->datahubIPAddress, $this->datahubPort, $this->host_id, $this->host_name, $this->host_name_enabled, $this->add_local_timestamp, $this->use_json);

			$this->assertEquals('tls://eu.data.logs.insight.rapid7.com', $log->getAddress());
		}

		public function tearDown()
		{
			R7Logger::tearDown();
		}
	}	
?>
