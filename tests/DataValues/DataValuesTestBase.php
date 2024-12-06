<?php

namespace DataValues\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Base for unit tests for DataValue implementing classes.
 *
 * @since 0.1
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DataValuesTestBase extends TestCase {

	/**
	 * Creates and returns a new instance of the concrete class.
	 *
	 * @since 0.1
	 *
	 * @return mixed
	 */
	public function newInstance() {
		$reflector = new ReflectionClass( $this->getClass() );
		$args = func_get_args();
		$instance = $reflector->newInstanceArgs( $args );
		return $instance;
	}

	/**
	 * @since 0.1
	 *
	 * @return array [instance, constructor args]
	 */
	public function instanceProvider() {
		$instanceBuilder = [ $this, 'newInstance' ];

		return array_map(
			static function ( array $args ) use ( $instanceBuilder ) {
				return [
					call_user_func_array( $instanceBuilder, $args ),
					$args
				];
			},
			$this->validConstructorArgumentsProvider()
		);
	}

}
