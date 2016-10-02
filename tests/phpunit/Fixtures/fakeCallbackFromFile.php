<?php

namespace Onoi\CallbackContainer\Tests;

/**
 * Expected registration form:
 *
 * return array(
 * 	'SomeService' => function( $containerBuilder ) { ... }
 * )
 *
 * @license GNU GPL v2+
 * @since 1.2
 *
 * @author mwjames
 */
return array(

	/**
	 * @return Closure
	 */
	'SomeStdClassFromFile' => function( $containerBuilder ) {
		return new \stdClass;
	},

	/**
	 * @return string
	 */
	'InvalidDefinition' => 'Foo'
);