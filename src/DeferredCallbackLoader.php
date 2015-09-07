<?php

namespace Onoi\CallbackContainer;

use Closure;
use RuntimeException;
use InvalidArgumentException;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class DeferredCallbackLoader implements CallbackLoader {

	/**
	 * @var array
	 */
	private $registry = array();

	/**
	 * @var array
	 */
	private $singletons = array();

	/**
	 * @var array
	 */
	private $expectedReturnTypeByHandler = array();

	/**
	 * @var array
	 */
	private $recursiveMarker = array();

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
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function registerCallback( $handlerName, Closure $callback ) {

		if ( !is_string( $handlerName ) ) {
			throw new InvalidArgumentException( "Expected a string" );
		}

		$this->registry[$handlerName] = $callback;
	}

	/**
	 * If you are not running PHPUnit or for that matter any other testing
	 * environment then you are not suppose to use this function.
	 *
	 * @since  1.0
	 *
	 * @param string $handlerName
	 * @param mixed $instance
	 */
	public function registerObject( $handlerName, $instance ) {

		if ( !is_string( $handlerName ) ) {
			throw new InvalidArgumentException( "Expected a string" );
		}

		$this->registry[$handlerName] = $instance;
		$this->singletons[$handlerName] = $instance;
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function registerExpectedReturnType( $handlerName, $type ) {

		if ( !is_string( $handlerName ) || !is_string( $type ) ) {
			throw new InvalidArgumentException( "Expected a string" );
		}

		$this->expectedReturnTypeByHandler[$handlerName] = $type;
	}

	/**
	 * @since  1.0
	 *
	 * {@inheritDoc}
	 */
	public function load( $handlerName ) {

		$parameters = func_get_args();
		array_shift( $parameters );

		$instance = null;

		$this->addRecursiveMarkerFor( $handlerName );

		if ( isset( $this->registry[$handlerName] ) ) {
			$instance = is_callable( $this->registry[$handlerName] ) ? call_user_func_array( $this->registry[$handlerName], $parameters ) : $this->registry[$handlerName];
		}

		$this->recursiveMarker[$handlerName]--;

		if ( !isset( $this->expectedReturnTypeByHandler[$handlerName] ) || is_a( $instance, $this->expectedReturnTypeByHandler[$handlerName] ) ) {
			return $instance;
		}

		throw new RuntimeException( "Expected " . $this->expectedReturnTypeByHandler[$handlerName] . " type for {$handlerName} could not be match to " . get_class( $instance ) );
	}

	/**
	 * @since  1.0
	 *
	 * {@inheritDoc}
	 */
	public function singleton( $handlerName ) {

		$parameters = func_get_args();
		array_shift( $parameters );

		$instance = null;

		$this->addRecursiveMarkerFor( $handlerName );

		if ( isset( $this->singletons[$handlerName] ) ) {
			$instance = is_callable( $this->singletons[$handlerName] ) ? $this->singletons[$handlerName]( $this ) : $this->singletons[$handlerName];
		}

		$this->recursiveMarker[$handlerName]--;

		if ( !isset( $this->expectedReturnTypeByHandler[$handlerName] ) || is_a( $instance, $this->expectedReturnTypeByHandler[$handlerName] ) ) {
			return $instance;
		}

		$instance = $this->load( $handlerName, $parameters );

		$this->singletons[$handlerName] = function() use ( $instance ) {
			static $singleton;
			return $singleton = $singleton === null ? $instance : $singleton;
		};

		return $this->singleton( $handlerName, $parameters );
	}

	/**
	 * @since  1.0
	 *
	 * @param string $handlerName
	 */
	public function deregister( $handlerName ) {
		unset( $this->registry[$handlerName] );
		unset( $this->singletons[$handlerName] );
		unset( $this->expectedReturnTypeByHandler[$handlerName] );
	}

	private function addRecursiveMarkerFor( $handlerName ) {

		if ( !is_string( $handlerName ) ) {
			throw new InvalidArgumentException( "Expected a string" );
		}

		if ( !isset( $this->recursiveMarker[$handlerName] ) ) {
			$this->recursiveMarker[$handlerName] = 0;
		}

		$this->recursiveMarker[$handlerName]++;

		if ( $this->recursiveMarker[$handlerName] > 1 ) {
			throw new RuntimeException( "Oh boy, your execution chain for $handlerName caused a circular reference." );
		}
	}

}
