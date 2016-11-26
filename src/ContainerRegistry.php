<?php

namespace Onoi\CallbackContainer;

use Closure;

/**
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
interface ContainerRegistry {

	/**
	 * @since 1.0
	 *
	 * @param string $serviceName
	 * @param Closure $callback
	 */
	public function registerCallback( $serviceName, Closure $callback );

	/**
	 * @since 1.1
	 *
	 * @param CallbackContainer $callbackContainer
	 */
	public function registerCallbackContainer( CallbackContainer $callbackContainer );

	/**
	 * @since 2.0
	 *
	 * @param string $file
	 */
	public function registerFromFile( $file );

	/**
	 * @since 2.0
	 *
	 * @param string $serviceName
	 * @param mixed $instance
	 */
	public function registerObject( $serviceName, $instance );

	/**
	 * Registers the expected return type of an instance that is called either
	 * via ContainerBuilder::create or ContainerBuilder::singleton.
	 *
	 * @since 1.0
	 *
	 * @param string $serviceName
	 * @param string $type
	 */
	public function registerExpectedReturnType( $serviceName, $type );

}
