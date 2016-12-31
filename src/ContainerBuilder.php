<?php

namespace Onoi\CallbackContainer;

use Closure;

/**
 * @license GNU GPL v2+
 * @since 1.2
 *
 * @author mwjames
 */
interface ContainerBuilder extends ContainerRegistry {

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
