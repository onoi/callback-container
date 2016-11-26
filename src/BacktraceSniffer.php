<?php

namespace Onoi\CallbackContainer;

/**
 * @license GNU GPL v2+
 * @since 2.0
 */
class BacktraceSniffer {

	/**
	 * @var integer
	 */
	private $depth = 1;

	/**
	 * @since 2.0
	 *
	 * @param integer $depth
	 */
	public function __construct( $depth = 1 ) {
		$this->depth = $depth;
	}

	/**
	 * @see MediaWiki::wfGetCaller
	 * @since 2.0
	 *
	 * @return $string
	 */
	public function getCaller( $depth = null ) {

		$depth = $depth === null ? $this->depth : $depth;
		$backtrace = $this->getBackTrace( $depth + 1 );

		if ( isset( $backtrace[$depth] ) ) {
			return $this->doFormatStackFrame( $backtrace[$depth] );
		}

		return 'unknown';
	}

	/**
	 * @see MediaWiki::wfGetCallers
	 * @since 2.0
	 *
	 * @return array
	 */
	public function getCallers( $depth = null ) {

		$depth = $depth === null ? $this->depth : $depth;
		$backtrace = array_reverse( $this->getBackTrace() );


		if ( !$depth || $depth > count( $backtrace ) - 1 ) {
			$depth = count( $backtrace ) - 1;
		}

		$backtrace = array_slice( $backtrace, -$depth - 1, $depth );

		return array_map( array( $this, 'doFormatStackFrame' ), $backtrace );
	}

	private function doFormatStackFrame( $frame ) {
		return isset( $frame['class'] ) ? $frame['class'] . '::' . $frame['function'] : $frame['function'];
	}

	private function getBackTrace( $limit = 0 ) {
		static $disabled = null;

		if ( $disabled || ( $disabled = !function_exists( 'debug_backtrace' ) ) === true ) {
			return array();
		}

		if ( $limit && version_compare( PHP_VERSION, '5.4.0', '>=' ) ) {
			return array_slice( debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, $limit + 1 ), 1 );
		} else {
			return array_slice( debug_backtrace(), 1 );
		}
	}

}
