<?php

namespace Onoi\CallbackContainer;

use Closure;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;

/**
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class LoggableContainerBuilder implements ContainerBuilder, LoggerAwareInterface {

	/**
	 * @var ContainerBuilder
	 */
	private $containerBuilder;

	/**
	 * @var BacktraceSniffer|null
	 */
	private $backtraceSniffer;

	/**
	 * @var CallFuncMemorySniffer|null
	 */
	private $callFuncMemorySniffer;

	/**
	 * @var array
	 */
	private $logs = array();

	/**
	 * @var loggerInterface
	 */
	private $logger;

	/**
	 * @since 2.0
	 *
	 * @param ContainerBuilder $containerBuilder
	 * @param BacktraceSniffer|null $backtraceSniffer
	 * @param CallFuncMemorySniffer|null $backtraceSniffer
	 */
	public function __construct( ContainerBuilder $containerBuilder, BacktraceSniffer $backtraceSniffer = null, CallFuncMemorySniffer $callFuncMemorySniffer = null ) {
		$this->containerBuilder = $containerBuilder;
		$this->backtraceSniffer = $backtraceSniffer;
		$this->callFuncMemorySniffer = $callFuncMemorySniffer;
	}

	/**
	 * @see LoggerAwareInterface::setLogger
	 *
	 * @since 2.5
	 *
	 * @param LoggerInterface $logger
	 */
	public function setLogger( LoggerInterface $logger ) {
		$this->logger = $logger;
	}

	/**
	 * @since 2.0
	 *
	 * {@inheritDoc}
	 */
	public function registerCallbackContainer( CallbackContainer $callbackContainer ) {
		$this->containerBuilder->registerCallbackContainer( $callbackContainer );
	}

	/**
	 * @since 2.0
	 *
	 * {@inheritDoc}
	 */
	public function registerCallback( $serviceName, Closure $callback ) {
		$this->containerBuilder->registerCallback( $serviceName, $callback );
	}

	/**
	 * @since 2.0
	 *
	 * {@inheritDoc}
	 */
	public function registerExpectedReturnType( $serviceName, $type ) {
		$this->containerBuilder->registerExpectedReturnType( $serviceName, $type );
	}

	/**
	 * @since 2.0
	 *
	 * {@inheritDoc}
	 */
	public function registerObject( $serviceName, $instance ) {
		$this->containerBuilder->registerObject( $serviceName, $instance );
	}

	/**
	 * @since 2.0
	 *
	 * {@inheritDoc}
	 */
	public function registerFromFile( $file ) {
		$this->containerBuilder->registerFromFile( $file );
	}

	/**
	 * @since 2.0
	 *
	 * {@inheritDoc}
	 */
	public function isRegistered( $serviceName ) {
		return $this->containerBuilder->isRegistered( $serviceName );
	}

	/**
	 * @since  2.0
	 *
	 * {@inheritDoc}
	 */
	public function create( $serviceName ) {

		$this->initLog( $serviceName );
		$this->logs[$serviceName]['prototype']++;

		if ( $this->backtraceSniffer !== null ) {
			$this->logs[$serviceName]['prototype-backtrace'][] = $this->backtraceSniffer->getCallers();
		}

		if ( $this->callFuncMemorySniffer !== null ) {
			$instance = $this->callFuncMemorySniffer->call( array( $this->containerBuilder, 'create' ), func_get_args() );
			$this->logs[$serviceName]['prototype-memory'][] = $this->callFuncMemorySniffer->getMemoryUsed();
			$this->logs[$serviceName]['prototype-time'][] = $this->callFuncMemorySniffer->getTimeUsed();
		} else {
			$instance = call_user_func_array( array( $this->containerBuilder, 'create' ), func_get_args() );
		}

		return $instance;
	}

	/**
	 * @since  2.0
	 *
	 * {@inheritDoc}
	 */
	public function singleton( $serviceName ) {

		$this->initLog( $serviceName );
		$this->logs[$serviceName]['singleton']++;

		if ( $this->callFuncMemorySniffer !== null ) {
			$instance = $this->callFuncMemorySniffer->call( array( $this->containerBuilder, 'singleton' ), func_get_args() );
			$this->logs[$serviceName]['singleton-memory'][] = $this->callFuncMemorySniffer->getMemoryUsed();
			$this->logs[$serviceName]['singleton-time'][] = $this->callFuncMemorySniffer->getTimeUsed();
		} else {
			$instance = call_user_func_array( array( $this->containerBuilder, 'singleton' ), func_get_args() );
		}

		return $instance;
	}

	private function initLog( $serviceName ) {

		if ( isset( $this->logs[$serviceName] ) ) {
			return;
		}

		$this->logs[$serviceName] = array(
			'prototype' => 0,
			'prototype-backtrace' => array(),
			'prototype-memory' => array(),
			'prototype-time' => array(),
			'singleton' => 0,
			'singleton-memory' => array(),
			'singleton-time' => array(),
		);

		if ( $this->backtraceSniffer === null ) {
			unset( $this->logs[$serviceName]['prototype-backtrace'] );
		}

		if ( $this->callFuncMemorySniffer === null ) {
			unset( $this->logs[$serviceName]['prototype-memory'] );
			unset( $this->logs[$serviceName]['singleton-memory'] );
			unset( $this->logs[$serviceName]['prototype-time'] );
			unset( $this->logs[$serviceName]['singleton-time'] );
		}
	}

	function __destruct() {
		call_user_func_array( $this->logWithCallback(), array( $this->logger ) );
	}

	private function buildLogs() {

		foreach ( $this->logs as $serviceName => $record ) {

			$count = $this->logs[$serviceName]['singleton'];
			$this->calcMedian( 'singleton-memory', $serviceName, $record,$count );
			$this->calcMedian( 'singleton-time', $serviceName, $record, $count );

			$count = $this->logs[$serviceName]['prototype'];
			$this->calcMedian( 'prototype-memory', $serviceName, $record, $count );
			$this->calcMedian( 'prototype-time', $serviceName, $record, $count );
		}

		// PHP 5.4+
		$flag = defined( 'JSON_PRETTY_PRINT' ) ? JSON_PRETTY_PRINT : 0;

		return json_encode( $this->logs, $flag );
	}

	private function calcMedian( $type, $serviceName, $record, $count ) {
		if ( isset( $record[$type] ) && $count > 0 ) {
			$this->logs[$serviceName][$type . '-median'] = array_sum( $record[$type] ) / $count;
			unset( $this->logs[$serviceName][$type] );
		}
	}

	private function logWithCallback( $logger = null ) {
		return function( $logger = null ) {

			if ( $logger === null ) {
				return;
			}

			$context = array();
			$logger->info( $this->buildLogs(), $context );
		};
	}

}
