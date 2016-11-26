<?php

namespace Onoi\CallbackContainer;

use RuntimeException;

/**
 * @see http://stackoverflow.com/questions/19973037/benchmark-memory-usage-in-php
 *
 * @license GNU GPL v2+
 * @since 2.0
 */
class CallFuncMemorySniffer {

	/**
	 * @var integer
	 */
	private static $max;

	/**
	 * @var integer
	 */
	private static $memory;

	/**
	 * @var integer
	 */
	private $time;

	/**
	 * @since 2.0
	 */
	public static function memoryTick() {
		self::$memory = memory_get_usage() - self::$memory;
		self::$max    = self::$memory > self::$max ? self::$memory : self::$max;
		self::$memory = memory_get_usage();
	}

	/**
	 * @since 2.0
	 *
	 * @param Closure|callable $func
	 * @param array|null $args
	 *
	 * @return mixed
	 * @throws RuntimeException
	 */
	public function call( $func, $args = null ) {

		if ( !is_callable( $func ) ) {
			throw new RuntimeException( "Function is not callable" );
		}

		// HHVM ... Fatal error: Call to undefined function
		if ( !function_exists( 'register_tick_function' ) ) {
			return is_array( $args ) ? call_user_func_array( $func, $args ): call_user_func( $func );
		}

		declare( ticks=1 );

		self::$memory = memory_get_usage();
		self::$max    = 0;

		register_tick_function(
			'call_user_func_array',
			array( '\Onoi\CallbackContainer\CallFuncMemorySniffer', 'memoryTick' ),
			array()
		);

		$this->time = microtime( true );
		$result = is_array( $args ) ? call_user_func_array( $func, $args ): call_user_func( $func );
		$this->time = microtime( true ) - $this->time;

		unregister_tick_function( 'call_user_func_array' );

		return $result;
	}

	/**
	 * @since 2.0
	 *
	 * @return integer
	 */
	public function getMemoryUsed() {
		return self::$max;
	}

	/**
	 * @since 2.0
	 *
	 * @return float
	 */
	public function getTimeUsed() {
		return $this->time;
	}

}
