<?php

namespace Onoi\CallbackContainer\Tests;

use Onoi\CallbackContainer\CallbackContainer;
use Onoi\CallbackContainer\CallbackLoader;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class FooCallbackContainer implements CallbackContainer {

	public function register( CallbackLoader $callbackLoader ) {
		$this->addCallbackHandlers( $callbackLoader);
	}

	private function addCallbackHandlers( $callbackLoader ) {

		$callbackLoader->registerCallback( '\stdClass', function() {
			return new \stdClass;
		} );

		$callbackLoader->registerAlias( 'Foo', '\stdClass' );
	}

}
