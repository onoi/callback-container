<?php

namespace Onoi\CallbackContainer\Tests;

use Onoi\CallbackContainer\DeferredCallbackLoader;

/**
 * @covers \Onoi\CallbackContainer\DeferredCallbackLoader
 * @group onoi-callback-container
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class DeferredCallbackLoaderTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\Onoi\CallbackContainer\DeferredCallbackLoader',
			new DeferredCallbackLoader()
		);
	}

	public function testCanConstructWithCallbackContainer() {

		$callbackContainer = $this->getMockBuilder( '\Onoi\CallbackContainer\CallbackContainer' )
			->disableOriginalConstructor()
			->getMock();

		$callbackContainer->expects( $this->once() )
			->method( 'register' );

		$this->assertInstanceOf(
			'\Onoi\CallbackContainer\DeferredCallbackLoader',
			new DeferredCallbackLoader( $callbackContainer )
		);
	}

	public function testRegisterCallback() {

		$instance = new DeferredCallbackLoader();

		$instance->registerCallback( '\stdClass', function() {
			return new \stdClass;
		} );

		$instance->registerAlias( 'Foo', '\stdClass' );

		$this->assertEquals(
			new \stdClass,
			$instance->load( 'Foo' )
		);

		$this->assertEquals(
			new \stdClass,
			$instance->singleton( 'Foo' )
		);

		$instance->deregister( 'Foo' );
	}

	public function testRegisterCallbackContainer() {

		$instance = new DeferredCallbackLoader( new FooCallbackContainer() );

		$this->assertEquals(
			new \stdClass,
			$instance->load( 'Foo' )
		);

		$this->assertEquals(
			new \stdClass,
			$instance->singleton( 'Foo' )
		);
	}

	public function testRegisterObject() {

		$expected = new \stdClass;

		$instance = new DeferredCallbackLoader();

		$instance->registerAlias( 'Foo', '\stdClass' );
		$instance->registerObject( 'Foo', $expected );

		$this->assertEquals(
			$expected,
			$instance->load( 'Foo' )
		);

		$this->assertEquals(
			$expected,
			$instance->singleton( 'Foo' )
		);
	}

	public function testLoadParameterizedInstance() {

		$instance = new DeferredCallbackLoader();

		$instance->registerCallback( '\stdClass', function( $a ) {
			$stdClass = new \stdClass;
			$stdClass->a = $a;

			return $stdClass;
		} );

		$instance->registerAlias( 'Foo', '\stdClass' );

		$this->assertEquals(
			'abc',
			$instance->load( 'Foo', 'abc' )->a
		);
	}

	public function testSingleton() {

		$instance = new DeferredCallbackLoader();

		$instance->registerCallback( '\stdClass', function( $a ) {
			$stdClass = new \stdClass;
			$stdClass->a = $a;

			return $stdClass;
		} );

		$instance->registerAlias( 'Foo', '\stdClass' );

		$singleton = $instance->singleton( 'Foo', '123' );

		$this->assertTrue(
			$singleton === $instance->singleton( 'Foo', 'abc' )
		);

		$this->assertFalse(
			$singleton === $instance->load( 'Foo', 'abc' )
		);
	}

	public function testTryToLoadRecursiveRegisterdCallbackHandlerThrowsException() {

		$instance = new DeferredCallbackLoader();

		$this->setExpectedException( 'RuntimeException' );

		$instance->registerCallback( '\stdClass', function() use ( $instance ) {
			return $instance->load( 'Foo' );
		} );

		$instance->registerAlias( 'Foo', '\stdClass' );
		$instance->load( 'Foo' );
	}

	public function testTryToLoadNonAliasedCallbackHandlerThrowsException() {

		$instance = new DeferredCallbackLoader();

		$this->setExpectedException( 'RuntimeException' );

		$instance->registerCallback( '\stdClass', function() use ( $instance ) {
			return new \stdClass;
		} );

		$instance->load( 'Foo' );
	}

	public function testTryToLoadForNonStringNamedCallbackHandlerThrowsException() {

		$instance = new DeferredCallbackLoader();

		$this->setExpectedException( 'InvalidArgumentException' );

		$instance->registerCallback( '\stdClass', function() use ( $instance ) {
			return $instance->load( 'Foo' );
		} );

		$instance->load( new \stdClass );
	}

	public function testTryToLoadSingletonRecursiveRegisterdCallbackHandlerThrowsException() {

		$instance = new DeferredCallbackLoader();

		$this->setExpectedException( 'RuntimeException' );

		$instance->registerCallback( '\stdClass', function() use ( $instance ) {
			return $instance->singleton( 'Foo' );
		} );

		$instance->registerAlias( 'Foo', '\stdClass' );
		$instance->singleton( 'Foo' );
	}

	public function testTryToLoadSingletonForNonStringNamedCallbackHandlerThrowsException() {

		$instance = new DeferredCallbackLoader();

		$this->setExpectedException( 'InvalidArgumentException' );

		$instance->registerCallback( '\stdClass', function() use ( $instance ) {
			return $instance->load( 'Foo' );
		} );

		$instance->singleton( new \stdClass );
	}

	public function testTryToLoadUnregisterCallbackHandlerThrowsException() {

		$instance = new DeferredCallbackLoader();

		$this->setExpectedException( 'RuntimeException' );
		$instance->load( 'Foo' );
	}

	public function testTryToLoadUnregisterSingletonCallbackHandlerThrowsException() {

		$instance = new DeferredCallbackLoader();

		$this->setExpectedException( 'RuntimeException' );
		$instance->singleton( 'Foo' );
	}

	public function testRegisterCallbackThrowsException() {

		$instance = new DeferredCallbackLoader();

		$this->setExpectedException( 'InvalidArgumentException' );
		$instance->registerCallback( new \stdClass, function() {
			return new \stdClass;
		} );
	}

	public function testRegisterObjectThrowsException() {

		$instance = new DeferredCallbackLoader();

		$this->setExpectedException( 'InvalidArgumentException' );
		$instance->registerObject( new \stdClass, new \stdClass );
	}

	public function testRegisterAliasException() {

		$instance = new DeferredCallbackLoader();

		$this->setExpectedException( 'InvalidArgumentException' );
		$instance->registerAlias( new \stdClass, 'Bar' );
	}

}
