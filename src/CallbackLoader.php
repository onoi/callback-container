<?php

namespace Onoi\CallbackContainer;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
interface CallbackLoader {

	/**
	 * Register a callback handler that is expected to return a typed
	 * instance equal to the name used for the registration.
	 *
	 * @since 1.0
	 *
	 * @param string $name
	 * @param Closure $callback
	 */
	public function registerCallback( $name, \Closure $callback );

	/**
	 * Register an alias for a registered callback handlerto access
	 * an instance with a non-typed name.
	 *
	 * @since 1.0
	 *
	 * @param string $alias
	 * @param string $handlerName
	 */
	public function registerAlias( $alias, $handlerName );

	/**
	 * @since 1.0
	 *
	 * @param string $name
	 *
	 * @return mixed
	 * @throws RuntimeException
	 */
	public function load( $name );

	/**
	 * @since 1.0
	 *
	 * @param string $name
	 */
	public function singleton( $name );

}
