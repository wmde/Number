<?php

namespace DataValues\Tests;

use DataValues\DecimalValue;
use DataValues\IllegalValueException;
use DataValues\QuantityValue;
use DataValues\UnboundedQuantityValue;

/**
 * @covers \DataValues\QuantityValue
 *
 * @group DataValue
 * @group DataValueExtensions
 *
 * @license GPL-2.0-or-later
 * @author Daniel Kinzler
 */
class QuantityValueTest extends DataValuesTestBase {

	public function setUp(): void {
		if ( !\extension_loaded( 'bcmath' ) ) {
			$this->markTestSkipped( 'bcmath extension not loaded' );
		}
	}

	/**
	 * @see DataValueTest::getClass
	 *
	 * @return string
	 */
	public function getClass() {
		return QuantityValue::class;
	}

	public function validConstructorArgumentsProvider() {
		$argLists = [];

		$argLists[] = [ new DecimalValue( '+42' ), '1', new DecimalValue( '+42' ), new DecimalValue( '+42' ) ];
		$argLists[] = [ new DecimalValue( '+0.01' ), '1', new DecimalValue( '+0.02' ), new DecimalValue( '+0.0001' ) ];
		$argLists[] = [ new DecimalValue( '-0.5' ), '1', new DecimalValue( '+0.02' ), new DecimalValue( '-0.7' ) ];

		return $argLists;
	}

	public function invalidConstructorArgumentsProvider() {
		$argLists = [];

		$argLists[] = [ new DecimalValue( '+0' ), '', new DecimalValue( '+0' ), new DecimalValue( '+0' ) ];
		$argLists[] = [ new DecimalValue( '+0' ), 1, new DecimalValue( '+0' ), new DecimalValue( '+0' ) ];

		$argLists[] = [ new DecimalValue( '+0' ), '1', new DecimalValue( '-0.001' ), new DecimalValue( '-1' ) ];
		$argLists[] = [ new DecimalValue( '+0' ), '1', new DecimalValue( '+1' ), new DecimalValue( '+0.001' ) ];

		return $argLists;
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetValue( QuantityValue $quantity, array $arguments ) {
		$this->assertSame( $quantity, $quantity->getValue() );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetAmount( QuantityValue $quantity, array $arguments ) {
		$this->assertSame( $arguments[0], $quantity->getAmount() );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetUnit( QuantityValue $quantity, array $arguments ) {
		$this->assertSame( $arguments[1], $quantity->getUnit() );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetUpperBound( QuantityValue $quantity, array $arguments ) {
		$this->assertSame( $arguments[2], $quantity->getUpperBound() );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetLowerBound( QuantityValue $quantity, array $arguments ) {
		$this->assertSame( $arguments[3], $quantity->getLowerBound() );
	}

	/**
	 * @dataProvider newFromNumberProvider
	 */
	public function testNewFromNumber( $amount, $unit, $upperBound, $lowerBound, QuantityValue $expected ) {
		$quantity = QuantityValue::newFromNumber( $amount, $unit, $upperBound, $lowerBound );

		$this->assertEquals( $expected->getAmount()->getValueFloat(), $quantity->getAmount()->getValueFloat() );
		$this->assertEquals( $expected->getUpperBound()->getValueFloat(), $quantity->getUpperBound()->getValueFloat() );
		$this->assertEquals( $expected->getLowerBound()->getValueFloat(), $quantity->getLowerBound()->getValueFloat() );
	}

	public function newFromNumberProvider() {
		$value = new DecimalValue( '+42' );
		$unit = '1';
		yield [
			$value->getValueFloat(), $unit, null, null,
			new QuantityValue( $value, $unit, $value, $value )
		];
		$value = new DecimalValue( '-0.05' );
		yield [
			$value->getValueFloat(), $unit, null, null,
			new QuantityValue( $value, $unit, $value, $value )
		];
		$value = new DecimalValue( '+0' );
		$value1 = new DecimalValue( '+0.5' );
		$value2 = new DecimalValue( '-0.5' );
		$unit = 'm';
		yield [
			$value->getValueFloat(), $unit, $value1->getValueFloat(), $value2->getValueFloat(),
			new QuantityValue( $value, $unit, $value1, $value2 )
		];
		$value = new DecimalValue( '+23' );
		$unit = '1';
		yield [
			$value->getValueFloat(), $unit, null, null,
			new QuantityValue( $value, $unit, $value, $value )
		];
		$value = new DecimalValue( '+42' );
		$value1 = new DecimalValue( '+43' );
		$value2 = new DecimalValue( '+41' );
		$unit = '1';
		yield [
			$value->getValueFloat(), $unit, $value1->getValueFloat(), $value2->getValueFloat(),
			new QuantityValue( $value, $unit, $value1, $value2 )
		];
		$value = new DecimalValue( '-0.05' );
		$value1 = new DecimalValue( '-0.04' );
		$value2 = new DecimalValue( '-0.06' );

		$unit = 'm';
		yield [
			$value->getValueFloat(), $unit, $value1->getValueFloat(), $value2->getValueFloat(),
			new QuantityValue( $value, $unit, $value1, $value2 )
		];
		$value = new DecimalValue( '+42' );
		$value1 = new DecimalValue( 43 );
		$value2 = new DecimalValue( 41.0 );
		$unit = '1';
		yield [
			$value, $unit, $value1, $value2,
			new QuantityValue( $value, $unit, $value1, $value2 )
		];
	}

	/**
	 * @dataProvider validArraySerializationProvider
	 */
	public function testNewFromArray( $data, UnboundedQuantityValue $expected ) {
		$value = QuantityValue::newFromArray( $data );
		$this->assertTrue( $expected->equals( $value ), $value . ' should equal ' . $expected );
	}

	public function validArraySerializationProvider() {
		return [
			'complete' => [
				[
					'amount' => '+2',
					'unit' => '1',
					'upperBound' => '+2.5',
					'lowerBound' => '+1.5',
				],
				QuantityValue::newFromNumber( '+2', '1', '+2.5', '+1.5' )
			],
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
		];
	}

	/**
	 * @dataProvider invalidArraySerializationProvider
	 */
	public function testNewFromArray_failure( $data ) {
		$this->expectException( IllegalValueException::class );
		QuantityValue::newFromArray( $data );
	}

	public function invalidArraySerializationProvider() {
		return [
			'no-amount' => [
				[
					'unit' => '1',
					'upperBound' => '+2.5',
					'lowerBound' => '+1.5',
				]
			],
			'no-unit' => [
				[
					'amount' => '+2',
					'upperBound' => '+2.5',
					'lowerBound' => '+1.5',
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
					'upperBound' => '+2.5',
					'lowerBound' => '+1.5',
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
		$value = QuantityValue::newFromArray( [
			'amount' => "-0.0\n",
			'unit' => "1\n",
			'upperBound' => "-0.0\n",
			'lowerBound' => "-0.0\n",
		] );

		$this->assertSame( [
			'amount' => '+0.0',
			'unit' => "1\n",
			'upperBound' => '+0.0',
			'lowerBound' => '+0.0',
		], $value->getArrayValue() );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetSortKey( QuantityValue $quantity ) {
		$this->assertSame( $quantity->getAmount()->getValueFloat(), $quantity->getSortKey() );
	}

	/**
	 * @dataProvider getUncertaintyProvider
	 */
	public function testGetUncertainty( QuantityValue $quantity, $expected ) {
		$this->assertEqualsWithDelta(
			$expected,
			$quantity->getUncertainty(),
			0.0000000000001
		);
	}

	public function getUncertaintyProvider() {
		return [
			[ QuantityValue::newFromNumber( '+0', '1', '+0', '+0' ), 0.0 ],

			[ QuantityValue::newFromNumber( '+0', '1', '+1', '-1' ), 2.0 ],
			[ QuantityValue::newFromNumber( '+0.00', '1', '+0.01', '-0.01' ), 0.02 ],
			[ QuantityValue::newFromNumber( '+100', '1', '+101', '+99' ), 2.0 ],
			[ QuantityValue::newFromNumber( '+100.0', '1', '+100.1', '+99.9' ), 0.2 ],
			[ QuantityValue::newFromNumber( '+12.34', '1', '+12.35', '+12.33' ), 0.02 ],

			[ QuantityValue::newFromNumber( '+0', '1', '+0.2', '-0.6' ), 0.8 ],
			[ QuantityValue::newFromNumber( '+7.3', '1', '+7.7', '+5.2' ), 2.5 ],
		];
	}

	/**
	 * @dataProvider getUncertaintyMarginProvider
	 */
	public function testGetUncertaintyMargin( QuantityValue $quantity, $expected ) {
		$this->assertSame( $expected, $quantity->getUncertaintyMargin()->getValue() );
	}

	public function getUncertaintyMarginProvider() {
		return [
			[ QuantityValue::newFromNumber( '+0', '1', '+1', '-1' ), '+1' ],
			[ QuantityValue::newFromNumber( '+0.00', '1', '+0.01', '-0.01' ), '+0.01' ],

			[ QuantityValue::newFromNumber( '-1', '1', '-1', '-1' ), '+0' ],

			[ QuantityValue::newFromNumber( '+0', '1', '+0.2', '-0.6' ), '+0.6' ],
			[ QuantityValue::newFromNumber( '+7.5', '1', '+7.5', '+5.5' ), '+2.0' ],
			[ QuantityValue::newFromNumber( '+11.5', '1', '+15', '+10.5' ), '+3.5' ],
		];
	}

	/**
	 * @dataProvider getOrderOfUncertaintyProvider
	 */
	public function testGetOrderOfUncertainty( QuantityValue $quantity, $expected ) {
		$this->assertSame( $expected, $quantity->getOrderOfUncertainty() );
	}

	public function getOrderOfUncertaintyProvider() {
		return [
			0 => [ QuantityValue::newFromNumber( '+0' ), 0 ],
			1 => [ QuantityValue::newFromNumber( '-123' ), 0 ],
			2 => [ QuantityValue::newFromNumber( '-1.23' ), -2 ],

			10 => [ QuantityValue::newFromNumber( '-100', '1', '-99', '-101' ), 0 ],
			11 => [ QuantityValue::newFromNumber( '+0.00', '1', '+0.01', '-0.01' ), -2 ],
			12 => [ QuantityValue::newFromNumber( '-117.3', '1', '-117.2', '-117.4' ), -1 ],

			20 => [ QuantityValue::newFromNumber( '+100', '1', '+100.01', '+99.97' ), -2 ],
			21 => [ QuantityValue::newFromNumber( '-0.002', '1', '-0.001', '-0.004' ), -3 ],
			22 => [ QuantityValue::newFromNumber( '-0.002', '1', '+0.001', '-0.06' ), -3 ],
			23 => [ QuantityValue::newFromNumber( '-21', '1', '+1.1', '-120' ), 1 ],
			24 => [ QuantityValue::newFromNumber( '-2', '1', '+1.1', '-120' ), 0 ],
			25 => [ QuantityValue::newFromNumber( '+1000', '1', '+1100', '+900.03' ), 1 ],
			26 => [ QuantityValue::newFromNumber( '+1000', '1', '+1100', '+900' ), 2 ],
		];
	}

	/**
	 * @dataProvider transformProvider
	 */
	public function testTransform( QuantityValue $quantity, $transformation, QuantityValue $expected ) {
		$args = func_get_args();
		$extraArgs = array_slice( $args, 3 );

		$call = [ $quantity, 'transform' ];
		$callArgs = array_merge( [ 'x', $transformation ], $extraArgs );
		$actual = call_user_func_array( $call, $callArgs );

		$this->assertSame( 'x', $actual->getUnit() );
		$this->assertEquals(
			$expected->getAmount()->getValue(),
			$actual->getAmount()->getValue(),
			'value'
		);
		$this->assertEquals(
			$expected->getUpperBound()->getValue(),
			$actual->getUpperBound()->getValue(),
			'upper bound'
		);
		$this->assertEquals(
			$expected->getLowerBound()->getValue(),
			$actual->getLowerBound()->getValue(),
			'lower bound'
		);
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
				QuantityValue::newFromNumber( '+10', '1', '+11', '+9' ),
				$identity,
				QuantityValue::newFromNumber( '+10', '?', '+11', '+9' )
			],
			1 => [
				QuantityValue::newFromNumber( '-0.5', '1', '-0.4', '-0.6' ),
				$identity,
				QuantityValue::newFromNumber( '-0.5', '?', '-0.4', '-0.6' )
			],
			2 => [
				QuantityValue::newFromNumber( '+0', '1', '+1', '-1' ),
				$square,
				QuantityValue::newFromNumber( '+0', '?', '+1', '-1' )
			],
			3 => [
				QuantityValue::newFromNumber( '+10', '1', '+11', '+9' ),
				$square,
				// note how rounding applies to bounds
				QuantityValue::newFromNumber( '+1000', '?', '+1300', '+700' )
			],
			4 => [
				QuantityValue::newFromNumber( '+0.5', '1', '+0.6', '+0.4' ),
				$scale,
				QuantityValue::newFromNumber( '+0.25', '?', '+0.30', '+0.20' ),
				0.5
			],

			// note: absolutely exact values require conversion with infinite precision!
			10 => [
				QuantityValue::newFromNumber( '+100', '1', '+100', '+100' ),
				$scale,
				QuantityValue::newFromNumber( '+12825', '?', '+12825', '+12825' ),
				128.25
			],

			11 => [
				QuantityValue::newFromNumber( '+100', '1', '+110', '+90' ),
				$scale,
				QuantityValue::newFromNumber( '+330', '?', '+370', '+300' ),
				3.3333
			],
			12 => [
				QuantityValue::newFromNumber( '+100', '1', '+100.1', '+99.9' ),
				$scale,
				QuantityValue::newFromNumber( '+333.3', '?', '+333.7', '+333.0' ),
				3.3333
			],
			13 => [
				QuantityValue::newFromNumber( '+100', '1', '+100.01', '+99.99' ),
				$scale,
				QuantityValue::newFromNumber( '+333.33', '?', '+333.36', '+333.30' ),
				3.3333
			],
		];
	}

	/** @dataProvider instanceWithHashProvider */
	public function testGetHashStability( QuantityValue $quantity, string $hash ) {
		$this->assertSame( $hash, $quantity->getHash() );
	}

	public function instanceWithHashProvider(): iterable {
		// all hashes obtained from data-values/data-values==3.0.0 data-values/number==0.11.1 under PHP 7.2.34
		yield '10+-1' => [
			QuantityValue::newFromNumber( '+10', '1', '+11', '+9' ),
			'2eb812346e6bbc1bc47ed7da130bfa5d',
		];
		yield '500 miles, or a little bit more' => [
			QuantityValue::newFromNumber(
				'+558.84719',
				'http://www.wikidata.org/entity/Q253276',
				'+558.84719',
				'+558.84719'
			),
			'5fb8c57f9ba8225146b03abdbb45c431',
		];
	}

}
