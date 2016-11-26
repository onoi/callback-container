<?php

namespace Onoi\CallbackContainer\Tests;

use Onoi\CallbackContainer\LoggableContainerBuilder;
use Onoi\CallbackContainer\CallbackContainerFactory;

/**
 * @covers \Onoi\CallbackContainer\LoggableContainerBuilder
 * @group onoi-callback-container
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class LoggableContainerBuilderTest extends \PHPUnit_Framework_TestCase {

	private $spyLogger;
	private $callbackContainerFactory;
	private $backtraceSniffer;
	private $callFuncMemorySniffer;

	protected function setUp() {

		$this->spyLogger = new SpyLogger();
		$this->callbackContainerFactory = new CallbackContainerFactory();

		$this->backtraceSniffer = $this->getMockBuilder( '\Onoi\CallbackContainer\BacktraceSniffer' )
			->disableOriginalConstructor()
			->getMock();

		$this->callFuncMemorySniffer = $this->getMockBuilder( '\Onoi\CallbackContainer\CallFuncMemorySniffer' )
			->disableOriginalConstructor()
			->getMock();
	}

	public function testCanConstruct() {

		$containerBuilder = $this->getMockBuilder( '\Onoi\CallbackContainer\ContainerBuilder' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\Onoi\CallbackContainer\LoggableContainerBuilder',
			new LoggableContainerBuilder( $containerBuilder, $this->backtraceSniffer, $this->callFuncMemorySniffer )
		);
	}

	public function testLoadCallbackHandlerWithoutExpectedReturnType() {

		$instance = new LoggableContainerBuilder(
			$this->callbackContainerFactory->newCallbackContainerBuilder()
		);

		$instance->setLogger( $this->spyLogger );
		$instance->registerCallback( 'Foo', function() {
			return 'abc';
		} );

		$this->assertEquals(
			'abc',
			$instance->create( 'Foo' )
		);

		$this->assertEquals(
			'abc',
			$instance->singleton( 'Foo' )
		);

		// Destruct
		$instance = null;

		$this->assertNotEmpty(
			$this->spyLogger->getLogs()
		);
	}

	public function testRegisterFromFile() {

		$instance = new LoggableContainerBuilder(
			$this->callbackContainerFactory->newCallbackContainerBuilder(),
			$this->callbackContainerFactory->newBacktraceSniffer(),
			$this->callbackContainerFactory->newCallFuncMemorySniffer()
		);

		$instance->setLogger( $this->spyLogger );
		$instance->registerFromFile( __DIR__ . '/../Fixtures/fakeCallbackFromFile.php' );

		$this->assertEquals(
			new \stdClass,
			$instance->create( 'SomeStdClassFromFile' )
		);

		$this->assertEquals(
			new \stdClass,
			$instance->singleton( 'SomeStdClassFromFile' )
		);

		// Destruct
		$instance = null;

		$this->assertNotEmpty(
			$this->spyLogger->getLogs()
		);
	}

}
