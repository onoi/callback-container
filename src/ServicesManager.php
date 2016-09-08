<?php

namespace Onoi\CallbackContainer;

use RuntimeException;

/**
 * Convenience class to handle services isolated from an active CallbackInstantiator
 * instance.
 *
 * @license GNU GPL v2+
 * @since 1.2
 *
 * @author mwjames
 */
class ServicesManager {

	/**
	 * @var CallbackInstantiator
	 */
	private $callbackInstantiator;

	/**
	 * @since 1.2
	 *
	 * @param CallbackInstantiator $callbackInstantiator
	 */
	public function __construct( CallbackInstantiator $callbackInstantiator ) {
		$this->callbackInstantiator = $callbackInstantiator;
	}

	/**
	 * @since 1.2
	 *
	 * @return ServicesManager
	 */
	public static function newManager() {
		return new self( new DeferredCallbackLoader() );
	}

	/**
	 * @since 1.2
	 *
	 * @param string $serviceName
	 * @param mixed $service
	 * @param string|null $type
	 */
	public function add( $serviceName, $service, $type = null ) {

		if ( !is_callable( $service ) ) {
			$service = function() use( $service ) {
				return $service;
			};
		}

		$this->callbackInstantiator->registerCallback( $serviceName, $service );

		if ( $type !== null ) {
			$this->callbackInstantiator->registerExpectedReturnType( $serviceName, $type );
		}
	}

	/**
	 * @since 1.2
	 *
	 * @param string $serviceName
	 *
	 * @return boolean
	 */
	public function has( $serviceName ) {
		return $this->callbackInstantiator->isRegistered( $serviceName );
	}

	/**
	 * @since 1.2
	 *
	 * @param string $serviceName
	 *
	 * @return mixed
	 */
	public function getBy( $serviceName ) {

		if ( !$this->callbackInstantiator->isRegistered( $serviceName ) ) {
			throw new RuntimeException( "$serviceName is an unknown service." );
		}

		return $this->callbackInstantiator->singleton( $serviceName );
	}

	/**
	 * @since 1.2
	 *
	 * @param string $serviceName
	 */
	public function removeBy( $serviceName ) {
		$this->callbackInstantiator->deregister( $serviceName );
	}

	/**
	 * @since 1.2
	 *
	 * @param string $serviceName
	 * @param mixed $service
	 */
	public function overrideWith( $serviceName, $service ) {
		$this->callbackInstantiator->registerObject( $serviceName, $service );
	}

}
