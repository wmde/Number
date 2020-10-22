<?php

namespace DataValues\Tests;

use Comparable;
use DataValues\DataValue;
use Exception;
use Hashable;
use Immutable;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Serializable;

/**
 * Base for unit tests for DataValue implementing classes.
 *
 * @since 0.1
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class DataValuesTest extends TestCase {

	/**
	 * Returns the name of the concrete class tested by this test.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	abstract public function getClass();

	abstract public function validConstructorArgumentsProvider();

	abstract public function invalidConstructorArgumentsProvider();

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
			function ( array $args ) use ( $instanceBuilder ) {
				return [
					call_user_func_array( $instanceBuilder, $args ),
					$args
				];
			},
			$this->validConstructorArgumentsProvider()
		);
	}

	/**
	 * @dataProvider validConstructorArgumentsProvider
	 *
	 * @since 0.1
	 */
	public function testConstructorWithValidArguments() {
		$dataItem = call_user_func_array(
			[ $this, 'newInstance' ],
			func_get_args()
		);

		$this->assertInstanceOf( $this->getClass(), $dataItem );
	}

	/**
	 * @dataProvider invalidConstructorArgumentsProvider
	 *
	 * @since 0.1
	 */
	public function testConstructorWithInvalidArguments() {
		$this->expectException( Exception::class );

		call_user_func_array(
			[ $this, 'newInstance' ],
			func_get_args()
		);
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testImplements( DataValue $value, array $arguments ) {
		$this->assertInstanceOf( Immutable::class, $value );
		$this->assertInstanceOf( Hashable::class, $value );
		$this->assertInstanceOf( Comparable::class, $value );
		$this->assertInstanceOf( Serializable::class, $value );
		$this->assertInstanceOf( DataValue::class, $value );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetType( DataValue $value, array $arguments ) {
		$valueType = $value->getType();
		$this->assertIsString( $valueType );
		$this->assertTrue( strlen( $valueType ) > 0 );

		// Check whether using getType statically returns the same as called from an instance:
		$staticValueType = call_user_func( [ $this->getClass(), 'getType' ] );
		$this->assertEquals( $staticValueType, $valueType );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testSerialization( DataValue $value, array $arguments ) {
		$serialization = serialize( $value );
		$this->assertIsString( $serialization );

		$unserialized = unserialize( $serialization );
		$this->assertInstanceOf( DataValue::class, $unserialized );

		$this->assertTrue( $value->equals( $unserialized ) );
		$this->assertEquals( $value, $unserialized );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testEquals( DataValue $value, array $arguments ) {
		$this->assertTrue( $value->equals( $value ) );

		foreach ( [ true, false, null, 'foo', 42, [], 4.2 ] as $otherValue ) {
			$this->assertFalse( $value->equals( $otherValue ) );
		}
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetHash( DataValue $value, array $arguments ) {
		$hash = $value->getHash();

		$this->assertIsString( $hash );
		$this->assertEquals( $hash, $value->getHash() );
		$this->assertEquals( $hash, $value->getCopy()->getHash() );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetCopy( DataValue $value, array $arguments ) {
		$copy = $value->getCopy();

		$this->assertInstanceOf( DataValue::class, $copy );
		$this->assertTrue( $value->equals( $copy ) );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetValueSimple( DataValue $value, array $arguments ) {
		$value->getValue();
		$this->assertTrue( true );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetArrayValueSimple( DataValue $value, array $arguments ) {
		$value->getArrayValue();
		$this->assertTrue( true );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testToArray( DataValue $value, array $arguments ) {
		$array = $value->toArray();

		$this->assertIsArray( $array );

		$this->assertTrue( array_key_exists( 'type', $array ) );
		$this->assertTrue( array_key_exists( 'value', $array ) );

		$this->assertEquals( $value->getType(), $array['type'] );
		$this->assertEquals( $value->getArrayValue(), $array['value'] );
	}

}
