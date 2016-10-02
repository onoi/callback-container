<?php

namespace Onoi\CallbackContainer;

use Onoi\CallbackContainer\Exception\ServiceNotFoundException;

/**
 * Convenience class to handle services isolated from an active ContainerBuilder
 * instance.
 *
 * @license GNU GPL v2+
 * @since 1.2
 *
 * @author mwjames
 */
class ServicesManager {

	/**
	 * @var ContainerBuilder
	 */
	private $containerBuilder;

	/**
	 * @since 1.2
	 *
	 * @param ContainerBuilder $containerBuilder
	 */
	public function __construct( ContainerBuilder $containerBuilder ) {
		$this->containerBuilder = $containerBuilder;
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

		$this->containerBuilder->registerCallback( $serviceName, $service );

		if ( $type !== null ) {
			$this->containerBuilder->registerExpectedReturnType( $serviceName, $type );
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
		return $this->containerBuilder->isRegistered( $serviceName );
	}

	/**
	 * @since 1.2
	 *
	 * @param string $serviceName
	 *
	 * @return mixed
	 * @throws ServiceNotFoundException
	 */
	public function getBy( $serviceName ) {

		if ( !$this->containerBuilder->isRegistered( $serviceName ) ) {
			throw new ServiceNotFoundException( "$serviceName is an unknown service." );
		}

		return $this->containerBuilder->singleton( $serviceName );
	}

	/**
	 * @since 1.2
	 *
	 * @param string $serviceName
	 */
	public function removeBy( $serviceName ) {
		$this->containerBuilder->deregister( $serviceName );
	}

	/**
	 * @since 1.2
	 *
	 * @param string $serviceName
	 * @param mixed $service
	 */
	public function overrideWith( $serviceName, $service ) {
		$this->containerBuilder->registerObject( $serviceName, $service );
	}

}
