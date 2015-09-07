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

		$instance->registerCallback( 'Foo', function() {
			return new \stdClass;
		} );

		$instance->registerExpectedReturnType( 'Foo', '\stdClass' );

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

	public function testLoadTypedReturn() {

		$instance = new DeferredCallbackLoader();

		$instance->registerCallback( 'Foo', function() {
			return new \stdClass;
		} );

		$instance->registerExpectedReturnType( 'Foo', '\stdClass' );

		$this->assertEquals(
			new \stdClass,
			$instance->load( 'Foo' )
		);
	}

	public function testLoadUntypedHandler() {

		$instance = new DeferredCallbackLoader();

		$instance->registerCallback( 'Foo', function() {
			return 'abc';
		} );

		$this->assertEquals(
			'abc',
			$instance->load( 'Foo' )
		);
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

		$instance->registerExpectedReturnType( 'Foo', '\stdClass' );
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

	public function testInjectInstanceToExistingCallbackHandler() {

		$stdClass = $this->getMockBuilder( '\stdClass' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new DeferredCallbackLoader( new FooCallbackContainer() );
		$instance->singleton( 'Foo' );

		$instance->registerObject( 'Foo', $stdClass );

		$this->assertTrue(
			$stdClass === $instance->load( 'Foo' )
		);

		$this->assertTrue(
			$stdClass === $instance->singleton( 'Foo' )
		);
	}

	public function testLoadParameterizedInstance() {

		$instance = new DeferredCallbackLoader();

		$instance->registerCallback( 'Foo', function( $a ) {
			$stdClass = new \stdClass;
			$stdClass->a = $a;

			return $stdClass;
		} );

		$instance->registerExpectedReturnType( 'Foo', '\stdClass' );

		$this->assertEquals(
			'abc',
			$instance->load( 'Foo', 'abc' )->a
		);
	}

	public function testSingleton() {

		$instance = new DeferredCallbackLoader();

		$instance->registerCallback( 'Foo', function( $a ) {
			$stdClass = new \stdClass;
			$stdClass->a = $a;

			return $stdClass;
		} );

		$instance->registerExpectedReturnType( 'Foo', '\stdClass' );

		$singleton = $instance->singleton( 'Foo', '123' );

		$this->assertTrue(
			$singleton === $instance->singleton( 'Foo', 'abc' )
		);

		$this->assertFalse(
			$singleton === $instance->load( 'Foo', 'abc' )
		);
	}

	public function testLoadUnregisteredCallbackHandlerToReturnNull() {

		$instance = new DeferredCallbackLoader();

		$this->assertNull(
			$instance->load( 'Foo' )
		);
	}

	public function testLoadUnregisteredCallbackHandlerAsSingletonToReturnNull() {

		$instance = new DeferredCallbackLoader();

		$this->assertNull(
			$instance->singleton( 'Foo' )
		);
	}

	public function testTryToLoadHandlerWithWrongReturnTypeThrowsException() {

		$instance = new DeferredCallbackLoader();

		$instance->registerCallback( 'Foo', function() {
			return new \stdClass;
		} );

		$instance->registerExpectedReturnType( 'Foo', 'Bar' );

		$this->setExpectedException( 'RuntimeException' );
		$instance->load( 'Foo' );
	}

	public function testTryToUseInvalidNameForCallbackHandlerOnLoadThrowsException() {

		$instance = new DeferredCallbackLoader();

		$this->setExpectedException( 'InvalidArgumentException' );
		$instance->load( new \stdClass );
	}

	public function testTryToUseInvalidNameForCallbackHandlerOnSingletonThrowsException() {

		$instance = new DeferredCallbackLoader();

		$this->setExpectedException( 'InvalidArgumentException' );
		$instance->singleton( new \stdClass );
	}

	public function testTryToLoadRecursiveRegisterdCallbackHandlerThrowsException() {

		$instance = new DeferredCallbackLoader();

		$this->setExpectedException( 'RuntimeException' );

		$instance->registerCallback( 'Foo', function() use ( $instance ) {
			return $instance->load( 'Foo' );
		} );

		$instance->registerExpectedReturnType( 'Foo', '\stdClass' );
		$instance->load( 'Foo' );
	}

	public function testTryToLoadSingletonRecursiveRegisterdCallbackHandlerThrowsException() {

		$instance = new DeferredCallbackLoader();

		$this->setExpectedException( 'RuntimeException' );

		$instance->registerCallback( '\stdClass', function() use ( $instance ) {
			return $instance->singleton( 'Foo' );
		} );

		$instance->registerExpectedReturnType( 'Foo', '\stdClass' );
		$instance->singleton( 'Foo' );
	}

	public function testTryToUseInvalidNameForCallbackHandlerThrowsException() {

		$instance = new DeferredCallbackLoader();

		$this->setExpectedException( 'InvalidArgumentException' );
		$instance->registerCallback( new \stdClass, function() {
			return new \stdClass;
		} );
	}

	public function testTryToUseInvalidNameForObjectRegistrationThrowsException() {

		$instance = new DeferredCallbackLoader();

		$this->setExpectedException( 'InvalidArgumentException' );
		$instance->registerObject( new \stdClass, new \stdClass );
	}

	public function testTryToUseInvalidNameForTypeRegistrationThrowsException() {

		$instance = new DeferredCallbackLoader();

		$this->setExpectedException( 'InvalidArgumentException' );
		$instance->registerExpectedReturnType( new \stdClass, 'Bar' );
	}

}
