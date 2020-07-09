<?php
	use PHPUnit\Framework\TestCase;

	class R7LoggerTests extends TestCase
	{
		const token = '00112233-4455-6677-8899-aabbccddeeff';
		const region = 'eu';
		const persistent = true;
		const ssl = true;
		const severity = LOG_DEBUG;
		const datahubEnabled = false;
		const datahubIPAddress = '';
		const datahubPort = 10000;
		const host_id = '123-123';
		const host_name = 'car';
		const host_name_enabled = true;
		const add_local_timestamp = true;
		const use_json = true;

		public function testMultiplyConnections()
		{
			$logFirst = R7Logger::getLogger('00112233-4455-6677-8899-aabbccddeef0', self::region, self::persistent, self::ssl, self::severity, self::datahubEnabled, self::datahubIPAddress, self::datahubPort, self::host_id, self::host_name, self::host_name_enabled, self::add_local_timestamp, self::use_json);
			$logSecond = R7Logger::getLogger('00112233-4455-6677-8899-aabbccddeef1', self::region, self::persistent, self::ssl, self::severity, self::datahubEnabled, self::datahubIPAddress, self::datahubPort, self::host_id, self::host_name, self::host_name_enabled, self::add_local_timestamp, self::use_json);
			$logThird = R7Logger::getLogger('00112233-4455-6677-8899-aabbccddeef2', self::region, self::persistent, self::ssl, self::severity, self::datahubEnabled, self::datahubIPAddress, self::datahubPort, self::host_id, self::host_name, self::host_name_enabled, self::add_local_timestamp, self::use_json);

			self::assertNotEquals('00112233-4455-6677-8899-aabbccddeef0', $logSecond->getToken());
			self::assertNotEquals('00112233-4455-6677-8899-aabbccddeef1', $logThird->getToken());

			self::assertEquals('00112233-4455-6677-8899-aabbccddeef0', $logFirst->getToken());
			self::assertEquals('00112233-4455-6677-8899-aabbccddeef1', $logSecond->getToken());
			self::assertEquals('00112233-4455-6677-8899-aabbccddeef2', $logThird->getToken());
		}

		public function testIsPersistent()
		{
			$log = R7Logger::getLogger(self::token, self::region, false, self::ssl, self::severity, self::datahubEnabled, self::datahubIPAddress, self::datahubPort, self::host_id, self::host_name, self::host_name_enabled, self::add_local_timestamp, self::use_json);

			self::assertFalse($log->isPersistent());

			self::tearDown();

			$log = R7Logger::getLogger(self::token, self::region, self::persistent, self::ssl, self::severity, self::datahubEnabled, self::datahubIPAddress, self::datahubPort, self::host_id, self::host_name, self::host_name_enabled, self::add_local_timestamp, self::use_json);

			self::assertTrue($log->isPersistent());
		}

		public function testIsTLS()
		{
			$log = R7Logger::getLogger(self::token, self::region, self::persistent, false, self::severity, self::datahubEnabled, self::datahubIPAddress, self::datahubPort, self::host_id, self::host_name, self::host_name_enabled, self::add_local_timestamp, self::use_json);

			self::assertFalse($log->isTLS());

			self::tearDown();

			$log = R7Logger::getLogger(self::token, self::region, self::persistent, self::ssl, self::severity, self::datahubEnabled, self::datahubIPAddress, self::datahubPort, self::host_id, self::host_name, self::host_name_enabled, self::add_local_timestamp, self::use_json);

			self::assertTrue($log->isTLS());
		}

		public function testGetPort()
		{
			$log = R7Logger::getLogger(self::token, self::region, self::persistent, false, self::severity, self::datahubEnabled, self::datahubIPAddress, 20000, self::host_id, self::host_name, self::host_name_enabled, self::add_local_timestamp, self::use_json);


			self::assertEquals(10000, $log->getPort());

			self::tearDown();

			$log = R7Logger::getLogger(self::token, self::region, self::persistent, true, self::severity, self::datahubEnabled, self::datahubIPAddress, self::datahubPort, self::host_id, self::host_name, self::host_name_enabled, self::add_local_timestamp, self::use_json);

			self::assertEquals(20000, $log->getPort());
		}

		public function testGetAddress()
		{
			$log = R7Logger::getLogger(self::token, self::region, self::persistent, false, self::severity, self::datahubEnabled, self::datahubIPAddress, self::datahubPort, self::host_id, self::host_name, self::host_name_enabled, self::add_local_timestamp, self::use_json);

			self::assertEquals('tcp://eu.data.logs.insight.rapid7.com', $log->getAddress());

			self::tearDown();
			$log = R7Logger::getLogger(self::token, self::region, self::persistent, true, self::severity, self::datahubEnabled, self::datahubIPAddress, self::datahubPort, self::host_id, self::host_name, self::host_name_enabled, self::add_local_timestamp, self::use_json);

			self::assertEquals('tls://eu.data.logs.insight.rapid7.com', $log->getAddress());
		}

		public function testEmptyToken() {
			$this->expectException(InvalidArgumentException::class);

			R7Logger::getLogger($this->definitely_a_valid_attribute, self::region, self::persistent, true, self::severity, self::datahubEnabled, self::datahubIPAddress, self::datahubPort, self::host_id, self::host_name, self::host_name_enabled, self::add_local_timestamp, self::use_json);
		}

		public function testInvalidTokenFormat() {
			$this->expectException(InvalidArgumentException::class);

			R7Logger::getLogger("I'm a UUID I swear...", self::region, self::persistent, true, self::severity, self::datahubEnabled, self::datahubIPAddress, self::datahubPort, self::host_id, self::host_name, self::host_name_enabled, self::add_local_timestamp, self::use_json);
		}

		public function testEmptyStringToken() {
			$this->expectException(InvalidArgumentException::class);

			R7Logger::getLogger("", self::region, self::persistent, true, self::severity, self::datahubEnabled, self::datahubIPAddress, self::datahubPort, self::host_id, self::host_name, self::host_name_enabled, self::add_local_timestamp, self::use_json);
		}

		public function tearDown(): void
		{
			R7Logger::tearDown();
		}
	}
?>

