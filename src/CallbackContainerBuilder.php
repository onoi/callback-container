<?php

namespace Onoi\CallbackContainer;

use Closure;
use Onoi\CallbackContainer\Exception\ServiceTypeMismatchException;
use Onoi\CallbackContainer\Exception\ServiceCircularReferenceException;
use Onoi\CallbackContainer\Exception\InvalidParameterTypeException;
use Onoi\CallbackContainer\Exception\FileNotFoundException;
use Onoi\CallbackContainer\Exception\ServiceNotFoundException;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class CallbackContainerBuilder implements ContainerBuilder {

	/**
	 * @var array
	 */
	protected $registry = array();

	/**
	 * @var array
	 */
	protected $singletons = array();

	/**
	 * @var array
	 */
	protected $expectedReturnTypeByHandler = array();

	/**
	 * @var array
	 */
	protected $recursiveMarker = array();

	/**
	 * @since 1.0
	 *
	 * @param CallbackContainer|null $callbackContainer
	 */
	public function __construct( CallbackContainer $callbackContainer = null ) {
		if ( $callbackContainer !== null ) {
			$this->registerCallbackContainer( $callbackContainer );
		}
	}

	/**
	 * @since 1.0
	 *
	 * @param CallbackContainer $callbackContainer
	 */
	public function registerCallbackContainer( CallbackContainer $callbackContainer ) {
		$callbackContainer->register( $this );
	}

	/**
	 * @since 1.2
	 *
	 * @param string $file
	 * @throws FileNotFoundException
	 */
	public function registerFromFile( $file ) {

		if ( !is_readable( ( $file = str_replace( array( '\\', '/' ), DIRECTORY_SEPARATOR, $file ) ) ) ) {
			throw new FileNotFoundException( "Cannot access or read {$file}" );
		}

		$defintions = require_once $file;

		foreach ( $defintions as $serviceName => $callback ) {

			if ( !is_callable( $callback ) ) {
				continue;
			}

			$this->registerCallback( $serviceName, $callback );
		}
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function registerCallback( $serviceName, Closure $callback ) {

		if ( !is_string( $serviceName ) ) {
			throw new InvalidParameterTypeException( "Expected a string" );
		}

		$this->registry[$serviceName] = $callback;
	}

	/**
	 * If you are not running PHPUnit or for that matter any other testing
	 * environment then you are not suppose to use this function.
	 *
	 * @since  1.0
	 *
	 * @param string $serviceName
	 * @param mixed $instance
	 */
	public function registerObject( $serviceName, $instance ) {

		if ( !is_string( $serviceName ) ) {
			throw new InvalidParameterTypeException( "Expected a string" );
		}

		unset( $this->singletons[$serviceName] );

		$this->registry[$serviceName] = $instance;
		$this->singletons[$serviceName]['#'] = $instance;
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function registerExpectedReturnType( $serviceName, $type ) {

		if ( !is_string( $serviceName ) || !is_string( $type ) ) {
			throw new InvalidParameterTypeException( "Expected a string" );
		}

		$this->expectedReturnTypeByHandler[$serviceName] = $type;
	}

	/**
	 * @see PSR-11 ContainerInterface
	 * @since 1.2
	 *
	 * {@inheritDoc}
	 */
	public function has( $id ) {
		return $this->isRegistered( $id );
	}

	/**
	 * @see PSR-11 ContainerInterface
	 * @since 1.2
	 *
	 * {@inheritDoc}
	 */
	public function get( $id ) {

		// A call to the get method with a non-existing id SHOULD throw a Psr\Container\Exception\NotFoundExceptionInterface.
		if ( !$this->has( $id ) ) {
		//	throw new NotFoundExceptionInterface( "Unknown {$id} handler or service" );
		}

		$parameters = func_get_args();
		array_shift( $parameters );

		return $this->getReturnValueFromCallbackHandlerFor( $id, $parameters );
	}

	/**
	 * @since 1.2
	 *
	 * {@inheritDoc}
	 */
	public function isRegistered( $serviceName ) {
		return isset( $this->registry[$serviceName] );
	}

	/**
	 * @since  1.0
	 *
	 * {@inheritDoc}
	 */
	public function create( $serviceName ) {

		$parameters = func_get_args();
		array_shift( $parameters );

		return $this->getReturnValueFromCallbackHandlerFor( $serviceName, $parameters );
	}

	/**
	 * @since  1.0
	 *
	 * {@inheritDoc}
	 */
	public function singleton( $serviceName ) {

		$parameters = func_get_args();
		array_shift( $parameters );

		$fingerprint = $parameters !== array() ? md5( json_encode( $parameters ) ) : '#';

		$instance = $this->getReturnValueFromSingletonFor( $serviceName, $fingerprint );

		if ( $instance !== null && ( !isset( $this->expectedReturnTypeByHandler[$serviceName] ) || is_a( $instance, $this->expectedReturnTypeByHandler[$serviceName] ) ) ) {
			return $instance;
		}

		$instance = $this->getReturnValueFromCallbackHandlerFor( $serviceName, $parameters );

		$this->singletons[$serviceName][$fingerprint] = function() use ( $instance ) {
			static $singleton;
			return $singleton = $singleton === null ? $instance : $singleton;
		};

		return $instance;
	}

	/**
	 * @since  1.0
	 *
	 * @param string $serviceName
	 */
	public function deregister( $serviceName ) {
		unset( $this->registry[$serviceName] );
		unset( $this->singletons[$serviceName] );
		unset( $this->expectedReturnTypeByHandler[$serviceName] );
	}

	protected function addRecursiveMarkerFor( $serviceName ) {

		if ( !is_string( $serviceName ) ) {
			throw new InvalidParameterTypeException( "Expected a string" );
		}

		if ( !isset( $this->recursiveMarker[$serviceName] ) ) {
			$this->recursiveMarker[$serviceName] = 0;
		}

		$this->recursiveMarker[$serviceName]++;

		if ( $this->recursiveMarker[$serviceName] > 1 ) {
			throw new ServiceCircularReferenceException( $serviceName );
		}
	}

	protected function getReturnValueFromCallbackHandlerFor( $serviceName, $parameters ) {

		$instance = null;
		$this->addRecursiveMarkerFor( $serviceName );

		if ( !isset( $this->registry[$serviceName] ) ) {
			throw new ServiceNotFoundException( "$serviceName is an unknown service." );
		}

		// Shift the ContainerBuilder to the first position in the parameter list
		array_unshift( $parameters, $this );
		$service = $this->registry[$serviceName];

		$instance = is_callable( $service ) ? call_user_func_array( $service, $parameters ) : $service;
		$this->recursiveMarker[$serviceName]--;

		if ( !isset( $this->expectedReturnTypeByHandler[$serviceName] ) || is_a( $instance, $this->expectedReturnTypeByHandler[$serviceName] ) ) {
			return $instance;
		}

		throw new ServiceTypeMismatchException( $serviceName, $this->expectedReturnTypeByHandler[$serviceName], ( is_object( $instance ) ? get_class( $instance ) : $instance ) );
	}

	private function getReturnValueFromSingletonFor( $serviceName, $fingerprint ) {

		$instance = null;
		$this->addRecursiveMarkerFor( $serviceName );

		if ( isset( $this->singletons[$serviceName][$fingerprint] ) ) {
			$service = $this->singletons[$serviceName][$fingerprint];
			$instance = is_callable( $service ) ? call_user_func( $service ) : $service;
		}

		$this->recursiveMarker[$serviceName]--;

		return $instance;
	}

}
