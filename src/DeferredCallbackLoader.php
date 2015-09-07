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
	private $aliases = array();

	/**
	 * @var array
	 */
	private $recursiveMarker = array();

	/**
	 * @since 1.0
	 *
	 * @param CallbackContainer $callbackContainer
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
	public function registerCallback( $name, Closure $callback ) {

		if ( !is_string( $name ) ) {
			throw new InvalidArgumentException( "Expected a string" );
		}

		$this->registry[$name] = $callback;
	}

	/**
	 * If your not running PHPUnit or for that matter any other testing
	 * environment then you are not suppose to use this function.
	 *
	 * @since  1.0
	 *
	 * @param string $name
	 * @param mixed $instance
	 */
	public function registerObject( $name, $instance ) {

		if ( !is_string( $name ) ) {
			throw new InvalidArgumentException( "Expected a string" );
		}

		if ( isset( $this->aliases[$name] ) ) {
			$name = $this->aliases[$name];
		}

		$this->registry[$name] = $instance;
		$this->singletons[$name] = $instance;
	}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function registerAlias( $alias, $handlerName ) {

		if ( !is_string( $alias ) || !is_string( $handlerName ) ) {
			throw new InvalidArgumentException( "Expected a string" );
		}

		$this->aliases[$alias] = $handlerName;
	}

	/**
	 * It is expected that the name used to load an instance represents the same
	 * instance signature except for when an alias is used but then the alias
	 * is required to point to a concrete class/interface construct.
	 *
	 * @since  1.0
	 *
	 * {@inheritDoc}
	 */
	public function load( $name ) {

		$parameters = func_get_args();
		array_shift( $parameters );

		$instance = null;

		$this->prepareName( $name );

		if ( isset( $this->registry[$name] ) ) {
			$instance = is_callable( $this->registry[$name] ) ? call_user_func_array( $this->registry[$name], $parameters ) : $this->registry[$name];
		}

		$this->recursiveMarker[$name]--;

		// Do a type check
		if ( is_a( $instance, $name ) ) {
			return $instance;
		}

		throw new RuntimeException( "Could not load an instance for $name" );
	}

	/**
	 * @since  1.0
	 *
	 * {@inheritDoc}
	 */
	public function singleton( $name ) {

		$parameters = func_get_args();
		array_shift( $parameters );

		$instance = null;

		$this->prepareName( $name );

		if ( isset( $this->singletons[$name] ) ) {
			$instance = is_callable( $this->singletons[$name] ) ? $this->singletons[$name]( $this ) : $this->singletons[$name];
		}

		$this->recursiveMarker[$name]--;

		if ( is_a( $instance, $name ) ) {
			return $instance;
		}

		$instance = $this->load( $name, $parameters );

		$this->singletons[$name] = function() use ( $instance ) {
			static $singleton;
			return $singleton = $singleton === null ? $instance : $singleton;
		};

		return $this->singleton( $name, $parameters );
	}

	/**
	 * @since  1.0
	 *
	 * @param string $name
	 */
	public function deregister( $name ) {

		if ( isset( $this->aliases[$name] ) ) {
			$name = $this->aliases[$name];
		}

		unset( $this->registry[$name] );
		unset( $this->singletons[$name] );
		unset( $this->aliases[$name] );
	}

	private function prepareName( &$name ) {

		if ( !is_string( $name ) ) {
			throw new InvalidArgumentException( "Expected a string" );
		}

		if ( isset( $this->aliases[$name] ) ) {
			$name = $this->aliases[$name];
		}

		if ( !isset( $this->recursiveMarker[$name] ) ) {
			$this->recursiveMarker[$name] = 0;
		}

		$this->recursiveMarker[$name]++;

		if ( $this->recursiveMarker[$name] > 1 ) {
			throw new RuntimeException( "Oh boy, your execution chain for $name caused a circular reference." );
		}
	}

}
