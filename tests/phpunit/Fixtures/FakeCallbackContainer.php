<?php

namespace Onoi\CallbackContainer\Fixtures;

use Onoi\CallbackContainer\CallbackContainer;
use Onoi\CallbackContainer\ContainerBuilder;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class FakeCallbackContainer implements CallbackContainer {

	public function register( ContainerBuilder $containerBuilder ) {
		$this->addCallbackHandlers( $containerBuilder );
	}

	private function addCallbackHandlers( $containerBuilder ) {

		$containerBuilder->registerCallback( 'Foo', function( $containerBuilder ) {
			return new \stdClass;
		} );

		$containerBuilder->registerExpectedReturnType( 'Foo', '\stdClass' );

		$containerBuilder->registerCallback( 'FooWithArgument', function( $containerBuilder, $argument ) {
			$containerBuilder->registerExpectedReturnType( 'FooWithArgument', '\stdClass' );

			$stdClass = new \stdClass;
			$stdClass->argument = $argument;

			return $stdClass;
		} );

		$containerBuilder->registerCallback( 'FooWithNullArgument', function( $containerBuilder, $argument = null ) {
			$containerBuilder->registerExpectedReturnType( 'FooWithNullArgument', '\stdClass' );

			$stdClass = new \stdClass;
			$stdClass->argument = $argument;
			$stdClass->argumentWithArgument = $containerBuilder->create( 'FooWithArgument', $argument );

			return $stdClass;
		} );
	}

}
