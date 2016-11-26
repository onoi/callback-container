<?php

namespace Onoi\CallbackContainer\Tests;

use Onoi\CallbackContainer\BacktraceSniffer;

/**
 * @covers \Onoi\CallbackContainer\BacktraceSniffer
 * @group onoi-callback-container
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class BacktraceSnifferTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\Onoi\CallbackContainer\BacktraceSniffer',
			new BacktraceSniffer()
		);
	}

	public function testGetCaller() {

		$instance = new BacktraceSniffer();

		$this->assertEquals(
			__METHOD__,
			$instance->getCaller()
		);
	}

	public function testGetCallerWithDifferentDepth() {

		$instance = new BacktraceSniffer();

		$this->assertEquals(
			'ReflectionMethod::invokeArgs',
			$instance->getCaller( 2 )
		);

		$this->assertEquals(
			'unknown',
			$instance->getCaller( 100 )
		);
	}

	public function testGetCallers() {

		$instance = new BacktraceSniffer();

		$this->assertEquals(
			array( __METHOD__ ),
			$instance->getCallers()
		);
	}

	public function testGetCallersWithDifferentDepth() {

		$instance = new BacktraceSniffer();

		$this->assertEquals(
			array(
				'ReflectionMethod::invokeArgs',
				__METHOD__
			),
			$instance->getCallers( 2 )
		);
	}

}
