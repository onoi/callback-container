<?php

namespace Onoi\CallbackContainer;

/**
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class CallbackContainerFactory {

	/**
	 * @since 2.0
	 *
	 * @param CallbackContainer|null $callbackContainer
	 *
	 * @return CallbackContainerBuilder
	 */
	public function newCallbackContainerBuilder( CallbackContainer $callbackContainer = null ) {
		return new CallbackContainerBuilder( $callbackContainer );
	}

	/**
	 * @since 2.0
	 *
	 * @param CallbackContainer|null $callbackContainer
	 * @param BacktraceSniffer|null $backtraceSniffer
	 * @param CallFuncMemorySniffer|null $callFuncMemorySniffer
	 *
	 * @return LoggableContainerBuilder
	 */
	public function newLoggableContainerBuilder( ContainerBuilder $containerBuilder = null, BacktraceSniffer $backtraceSniffer = null, CallFuncMemorySniffer $callFuncMemorySniffer = null ) {

		if ( $containerBuilder === null ) {
			$containerBuilder = $this->newCallbackContainerBuilder();
		}

		return new LoggableContainerBuilder( $containerBuilder, $backtraceSniffer, $callFuncMemorySniffer );
	}

	/**
	 * @since 2.0
	 *
	 * @return NullContainerBuilder
	 */
	public function newNullContainerBuilder() {
		return new NullContainerBuilder();
	}

	/**
	 * @since 2.0
	 *
	 * @param ContainerBuilder|null $callbackContainer
	 *
	 * @return ServicesManager
	 */
	public function newServicesManager( ContainerBuilder $containerBuilder = null ) {

		if ( $containerBuilder === null ) {
			$containerBuilder = $this->newCallbackContainerBuilder();
		}

		return new ServicesManager( $containerBuilder );
	}

	/**
	 * @since 2.0
	 *
	 * @param integer $depth
	 *
	 * @return BacktraceSniffer
	 */
	public function newBacktraceSniffer( $depth = 1 ) {
		return new BacktraceSniffer( $depth );
	}

	/**
	 * @since 2.0
	 *
	 * @return CallFuncMemorySniffer
	 */
	public function newCallFuncMemorySniffer() {
		return new CallFuncMemorySniffer();
	}

}
