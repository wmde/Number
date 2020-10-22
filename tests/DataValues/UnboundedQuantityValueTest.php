<?php

namespace DataValues\Tests;

use DataValues\DecimalValue;
use DataValues\IllegalValueException;
use DataValues\QuantityValue;
use DataValues\UnboundedQuantityValue;

/**
 * @covers DataValues\UnboundedQuantityValue
 *
 * @group DataValue
 * @group DataValueExtensions
 *
 * @license GPL-2.0-or-later
 * @author Daniel Kinzler
 */
class UnboundedQuantityValueTest extends DataValuesTest {

	/**
	 * @see DataValueTest::getClass
	 *
	 * @return string
	 */
	public function getClass() {
		return UnboundedQuantityValue::class;
	}

	public function validConstructorArgumentsProvider() {
		$argLists = [];

		$argLists[] = [ new DecimalValue( '+42' ), '1' ];
		$argLists[] = [ new DecimalValue( '+0.01' ), '1' ];
		$argLists[] = [ new DecimalValue( '-0.5' ), '1' ];

		return $argLists;
	}

	public function invalidConstructorArgumentsProvider() {
		$argLists = [];

		$argLists[] = [ new DecimalValue( '+0' ), '' ];
		$argLists[] = [ new DecimalValue( '+0' ), 1 ];

		return $argLists;
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetValue( UnboundedQuantityValue $quantity, array $arguments ) {
		$this->assertSame( $quantity, $quantity->getValue() );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetAmount( UnboundedQuantityValue $quantity, array $arguments ) {
		$this->assertSame( $arguments[0], $quantity->getAmount() );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetUnit( UnboundedQuantityValue $quantity, array $arguments ) {
		$this->assertSame( $arguments[1], $quantity->getUnit() );
	}

	/**
	 * @dataProvider newFromNumberProvider
	 */
	public function testNewFromNumber( $amount, $unit, UnboundedQuantityValue $expected ) {
		$quantity = UnboundedQuantityValue::newFromNumber( $amount, $unit );

		$this->assertEquals( $expected->getAmount()->getValue(), $quantity->getAmount()->getValue() );
	}

	public function newFromNumberProvider() {
		return [
			[
				42, '1',
				new UnboundedQuantityValue( new DecimalValue( '+42' ), '1' )
			],
			[
				-0.05, '1',
				new UnboundedQuantityValue( new DecimalValue( '-0.05' ), '1' )
			],
			[
				0, 'm',
				new UnboundedQuantityValue( new DecimalValue( '+0' ), 'm' )
			],
			[
				'+23', '1',
				new UnboundedQuantityValue( new DecimalValue( '+23' ), '1' )
			],
			[
				'+42', '1',
				new UnboundedQuantityValue( new DecimalValue( '+42' ), '1' )
			],
			[
				'-0.05', 'm',
				new UnboundedQuantityValue( new DecimalValue( '-0.05' ), 'm' )
			],
			[
				new DecimalValue( '+42' ), '1',
				new UnboundedQuantityValue( new DecimalValue( '+42' ), '1' )
			],
		];
	}

	/**
	 * @dataProvider validArraySerializationProvider
	 */
	public function testNewFromArray( $data, UnboundedQuantityValue $expected ) {
		$value = UnboundedQuantityValue::newFromArray( $data );
		$this->assertTrue( $expected->equals( $value ), $value . ' should equal ' . $expected );
	}

	public function validArraySerializationProvider() {
		return [
			'unbounded' => [
				[
					'amount' => '+2',
					'unit' => '1',
				],
				UnboundedQuantityValue::newFromNumber( '+2', '1' )
			],
			'unbounded with existing array keys' => [
				[
					'amount' => '+2',
					'unit' => '1',
					'upperBound' => null,
					'lowerBound' => null,
				],
				UnboundedQuantityValue::newFromNumber( '+2', '1' )
			],
			'with-extra' => [
				[
					'amount' => '+2',
					'unit' => '1',
					'upperBound' => '+2.5',
					'lowerBound' => '+1.5',
				],
				QuantityValue::newFromNumber( '+2', '1', '+2.5', '+1.5' )
			],
		];
	}

	/**
	 * @dataProvider invalidArraySerializationProvider
	 */
	public function testNewFromArray_failure( $data ) {
		$this->expectException( IllegalValueException::class );
		UnboundedQuantityValue::newFromArray( $data );
	}

	public function invalidArraySerializationProvider() {
		return [
			'not an array (string)' => [
				'foo'
			],
			'not an array (int)' => [
				303
			],
			'not an array (object)' => [
				new \stdClass()
			],
			'no-amount' => [
				[
					'unit' => '1',
				]
			],
			'no-unit' => [
				[
					'amount' => '+2',
				]
			],
			'no-upperBound' => [
				[
					'amount' => '+2',
					'unit' => '1',
					'lowerBound' => '+1.5',
				]
			],
			'no-lowerBound' => [
				[
					'amount' => '+2',
					'unit' => '1',
					'upperBound' => '+2.5',
				]
			],
			'bad-amount' => [
				[
					'amount' => 'x',
					'unit' => '1',
				]
			],
			'bad-upperBound' => [
				[
					'amount' => '+2',
					'unit' => '1',
					'upperBound' => 'x',
					'lowerBound' => '+1.5',
				]
			],
			'bad-lowerBound' => [
				[
					'amount' => '+2',
					'unit' => '1',
					'upperBound' => '+2.5',
					'lowerBound' => 'x',
				]
			],
		];
	}

	/**
	 * @see https://phabricator.wikimedia.org/T110728
	 * @see http://www.regular-expressions.info/anchors.html#realend
	 */
	public function testTrailingNewlineRobustness() {
		$value = UnboundedQuantityValue::newFromArray( [
			'amount' => "-0.0\n",
			'unit' => "1\n",
		] );

		$this->assertSame( [
			'amount' => '+0.0',
			'unit' => "1\n",
		], $value->getArrayValue() );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetSortKey( UnboundedQuantityValue $quantity ) {
		$this->assertSame( $quantity->getAmount()->getValueFloat(), $quantity->getSortKey() );
	}

	/**
	 * @dataProvider transformProvider
	 */
	public function testTransform(
		UnboundedQuantityValue $quantity,
		$transformation,
		UnboundedQuantityValue $expected
	) {
		$args = func_get_args();
		$extraArgs = array_slice( $args, 3 );

		$call = [ $quantity, 'transform' ];
		$callArgs = array_merge( [ 'x', $transformation ], $extraArgs );
		$actual = call_user_func_array( $call, $callArgs );

		$this->assertSame( 'x', $actual->getUnit() );
		$this->assertEquals( $expected->getAmount()->getValue(), $actual->getAmount()->getValue(), 'value' );
	}

	public function transformProvider() {
		$identity = function ( DecimalValue $value ) {
			return $value;
		};

		$square = function ( DecimalValue $value ) {
			$v = $value->getValueFloat();
			return new DecimalValue( $v * $v * $v );
		};

		$scale = function ( DecimalValue $value, $factor ) {
			return new DecimalValue( $value->getValueFloat() * $factor );
		};

		return [
			0 => [
				UnboundedQuantityValue::newFromNumber( '+10', '1' ),
				$identity,
				UnboundedQuantityValue::newFromNumber( '+10', '?' )
			],
			1 => [
				UnboundedQuantityValue::newFromNumber( '-0.5', '1' ),
				$identity,
				UnboundedQuantityValue::newFromNumber( '-0.5', '?' )
			],
			2 => [
				UnboundedQuantityValue::newFromNumber( '+0', '1' ),
				$square,
				UnboundedQuantityValue::newFromNumber( '+0', '?' )
			],
			3 => [
				UnboundedQuantityValue::newFromNumber( '+10', '1' ),
				$square,
				UnboundedQuantityValue::newFromNumber( '+1000', '?' )
			],
			4 => [
				UnboundedQuantityValue::newFromNumber( '+0.5', '1' ),
				$scale,
				UnboundedQuantityValue::newFromNumber( '+0.25', '?' ),
				0.5
			],

			// note: absolutely exact values require conversion with infinite precision!
			10 => [
				UnboundedQuantityValue::newFromNumber( '+100', '1' ),
				$scale,
				UnboundedQuantityValue::newFromNumber( '+12825', '?' ),
				128.25
			],
			13 => [
				UnboundedQuantityValue::newFromNumber( '+100', '1' ),
				$scale,
				UnboundedQuantityValue::newFromNumber( '+333.33', '?' ),
				3.3333
			],
		];
	}

}
