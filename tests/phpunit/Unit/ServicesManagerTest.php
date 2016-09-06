<?php

namespace Onoi\CallbackContainer\Tests;

use Onoi\CallbackContainer\ServicesManager;

/**
 * @covers \Onoi\CallbackContainer\ServicesManager
 * @group onoi-callback-container
 *
 * @license GNU GPL v2+
 * @since 1.2
 *
 * @author mwjames
 */
class ServicesManagerTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$callbackInstantiator = $this->getMockBuilder( '\Onoi\CallbackContainer\CallbackInstantiator' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\Onoi\CallbackContainer\ServicesManager',
			new ServicesManager( $callbackInstantiator )
		);

		$this->assertInstanceOf(
			'\Onoi\CallbackContainer\ServicesManager',
			ServicesManager::newManager()
		);
	}

	public function testAddServiceWithScalarType() {

		$instance = ServicesManager::newManager();
		$instance->add( 'Foo', 123 );

		$this->assertTrue(
			$instance->has( 'Foo' )
		);

		$this->assertSame(
			123,
			$instance->getBy( 'Foo' )
		);
	}

	public function testAddServiceWithObjectType() {

		$instance = ServicesManager::newManager();
		$instance->add( 'Foo', $this );

		$this->assertTrue(
			$instance->has( 'Foo' )
		);

		$this->assertSame(
			$this,
			$instance->getBy( 'Foo' )
		);
	}

	public function testRemoveService() {

		$instance = ServicesManager::newManager();
		$instance->add( 'Foo', $this );

		$this->assertTrue(
			$instance->has( 'Foo' )
		);

		$instance->removeBy( 'Foo' );

		$this->assertFalse(
			$instance->has( 'Foo' )
		);
	}

	public function testOverrideUntypedService() {

		$instance = ServicesManager::newManager();
		$instance->add( 'Foo', $this );

		$this->assertTrue(
			$instance->has( 'Foo' )
		);

		$instance->overrideWith( 'Foo', 123 );

		$this->assertSame(
			123,
			$instance->getBy( 'Foo' )
		);
	}

	public function testTryToOverrideTypedServiceWithIncompatibleTypeThrowsException() {

		$instance = ServicesManager::newManager();
		$instance->add( 'Foo', $this, '\PHPUnit_Framework_TestCase' );

		$this->assertTrue(
			$instance->has( 'Foo' )
		);

		$instance->overrideWith( 'Foo', 123 );

		$this->setExpectedException( 'RuntimeException' );
		$instance->getBy( 'Foo' );
	}

	public function testTryToAccessToUnknownServiceThrowsException() {

		$instance = ServicesManager::newManager();

		$this->setExpectedException( 'RuntimeException' );
		$instance->getBy( 'Foo' );
	}

}
