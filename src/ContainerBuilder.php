<?php

namespace Onoi\CallbackContainer;

use Closure;

/**
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
interface ContainerBuilder {

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
	 * Registers the expected return type of an instance that is called either
	 * via ContainerBuilder::create or ContainerBuilder::singleton.
	 *
	 * @since 1.0
	 *
	 * @param string $serviceName
	 * @param string $type
	 */
	public function registerExpectedReturnType( $serviceName, $type );

	/**
	 * @since 1.2
	 *
	 * @param string $serviceName
	 *
	 * @return boolean
	 */
	public function isRegistered( $serviceName );

	/**
	 * Returns a new instance for each call to a requested service.
	 *
	 * @since 1.1
	 *
	 * @param string $serviceName
	 *
	 * @return mixed
	 * @throws RuntimeException
	 */
	public function create( $serviceName );

	/**
	 * Returns a singleton instance for a requested service that relies on the
	 * same argument fingerprint.
	 *
	 * @since 1.0
	 *
	 * @param string $serviceName
	 *
	 * @return mixed
	 * @throws RuntimeException
	 */
	public function singleton( $serviceName );

}
