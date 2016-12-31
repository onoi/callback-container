<?php

namespace Onoi\CallbackContainer;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class NullContainerBuilder implements ContainerBuilder {

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function registerCallback( $handlerName, \Closure $callback ) {}

	/**
	 * @since 1.1
	 *
	 * {@inheritDoc}
	 */
	public function registerCallbackContainer( CallbackContainer $callbackContainer ) {}

	/**
	 * @since 2.0
	 *
	 * {@inheritDoc}
	 */
	public function registerFromFile( $file ) {}

	/**
	 * @since 2.0
	 *
	 * {@inheritDoc}
	 */
	public function registerObject( $serviceName, $instance ) {}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function registerExpectedReturnType( $handlerName, $type ) {}

	/**
	 * @since 1.2
	 *
	 * {@inheritDoc}
	 */
	public function isRegistered( $handlerName ) { return false; }

	/**
	 * @since 1.1
	 *
	 * {@inheritDoc}
	 */
	public function create( $handlerName ) {}

	/**
	 * @since 1.0
	 *
	 * {@inheritDoc}
	 */
	public function singleton( $handlerName ) {}

}
