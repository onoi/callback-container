<?php

namespace Onoi\CallbackContainer\Tests;

use Onoi\CallbackContainer\CallFuncMemorySniffer;

/**
 * @covers \Onoi\CallbackContainer\CallFuncMemorySniffer
 * @group onoi-callback-container
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class CallFuncMemorySnifferTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\Onoi\CallbackContainer\CallFuncMemorySniffer',
			new CallFuncMemorySniffer()
		);
	}

	public function testCallWithoutArguments() {

		$instance = new CallFuncMemorySniffer();

		$this->assertEquals(
			'Foo',
			$instance->call( function(){ return 'Foo'; } )
		);

		$this->assertInternalType(
			'integer',
			$instance->getMemoryUsed()
		);

		$this->assertInternalType(
			'float',
			$instance->getTimeUsed()
		);
	}

	public function testCallWithArguments() {

		$instance = new CallFuncMemorySniffer();

		$this->assertEquals(
			'Foo-abc',
			$instance->call( function( $arg ) { return 'Foo-'. $arg; }, array( 'abc' ) )
		);

		$this->assertInternalType(
			'integer',
			$instance->getMemoryUsed()
		);

		$this->assertInternalType(
			'float',
			$instance->getTimeUsed()
		);
	}

	public function testInvalidCallThrowsException() {

		$instance = new CallFuncMemorySniffer();

		$this->setExpectedException( 'RuntimeException' );
		$instance->call( 'foo' );
	}

}
