<?php
	use PHPUnit\Framework\TestCase;

	class R7LoggerTest extends TestCase
	{
		/**
		 *	@expectedException PHPUnit\Framework\Error\Warning
		 */
		public function testOneParameter()
		{
			$this->assertNotInstanceOf('R7Logger.php', R7Logger::getLogger('token'));
		}

		/**
		 *  @expectedException PHPUnit\Framework\Error\Warning
		 */
		public function testTwoParameter()
		{
			$this->assertNotInstanceOf('R7Logger', R7Logger::getLogger('token', false));
		}

		/**
		 *  @expectedException PHPUnit\Framework\Error\Warning
		 */
		public function testThreeParameter()
		{
			$this->assertNotInstanceOf('R7Logger', R7Logger::getLogger('token', false, false));
		}

		public function testAllParameters()
		{
			$this->assertInstanceOf('R7Logger', R7Logger::getLogger('token', false, false, LOG_DEBUG, false, "", 10000, "", "", false, true));

		}

		public function testMultiplyConnections()
		{
			$logFirst = R7Logger::getLogger('token1', false, false, LOG_DEBUG, false, "", 10000, "", "", false, true);
			$logSecond = R7Logger::getLogger('token2', false, false, LOG_DEBUG, false, "", 10000, "", "", false, true);
			$logThird = R7Logger::getLogger('token3', false, false, LOG_DEBUG, false, "", 10000, "", "", false, true);

			$this->assertNotEquals('token1', $logSecond->getToken());
			$this->assertNotEquals('token2', $logThird->getToken());

			$this->assertEquals('token1', $logFirst->getToken());
			$this->assertEquals('token2', $logSecond->getToken());
			$this->assertEquals('token3', $logThird->getToken());
		}

		public function testIsPersistent()
		{
			$log = R7Logger::getLogger('token', false, true, LOG_DEBUG, false, "", 10000, "", "", false, true);

			$this->assertFalse($log->isPersistent());

			$this->tearDown();

			$log = R7Logger::getLogger('token', true, true, LOG_DEBUG, false, "", 10000, "", "", false, true);

			$this->assertTrue($log->isPersistent());
		}

		public function testIsTLS()
		{
			$log = R7Logger::getLogger('token',false,false, LOG_DEBUG, false, "", 10000, "", "", false, true);

			$this->assertFalse($log->isTLS());

			$this->tearDown();

			$log = R7Logger::getLogger('token', true, true, LOG_DEBUG, false, "", 10000, "", "", false, true);

			$this->assertTrue($log->isTLS());
		}

		public function testGetPort()
		{

			$log = R7Logger::getLogger('token', true, false, LOG_DEBUG, false, "",  10000, "", "", false, true);


			$this->assertEquals(10000, $log->getPort());

			$this->tearDown();

			$log = R7Logger::getLogger('token', true, true, LOG_DEBUG, false, "", 10000, "", "", false, true);

			$this->assertEquals(20000, $log->getPort());
		}

		public function testGetAddress()
		{
			$log = R7Logger::getLogger('token', true, false, LOG_DEBUG, false, "", 10000, "", "", false, true);

			$this->assertEquals('tcp://api.logentries.com', $log->getAddress());

			$this->tearDown();
			$log = R7Logger::getLogger('token', true, true, LOG_DEBUG, false, "", 10000, "", "", false, true);


			$this->assertEquals('tls://api.logentries.com', $log->getAddress());
		}

		public function tearDown()
		{
			R7Logger::tearDown();
		}
	}	
?>
