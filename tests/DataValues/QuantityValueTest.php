<?php

namespace DataValues\Tests;

use DataValues\DecimalValue;
use DataValues\QuantityValue;
use DataValues\UnboundedQuantityValue;

/**
 * @covers DataValues\QuantityValue
 *
 * @group DataValue
 * @group DataValueExtensions
 *
 * @license GPL-2.0+
 * @author Daniel Kinzler
 */
class QuantityValueTest extends DataValueTest {

	/**
	 * @see DataValueTest::getClass
	 *
	 * @return string
	 */
	public function getClass() {
		return 'DataValues\QuantityValue';
	}

	public function validConstructorArgumentsProvider() {
		$argLists = array();

		$argLists[] = array( new DecimalValue( '+42' ), '1', new DecimalValue( '+42' ), new DecimalValue( '+42' ) );
		$argLists[] = array( new DecimalValue( '+0.01' ), '1', new DecimalValue( '+0.02' ), new DecimalValue( '+0.0001' ) );
		$argLists[] = array( new DecimalValue( '-0.5' ), '1', new DecimalValue( '+0.02' ), new DecimalValue( '-0.7' ) );

		return $argLists;
	}

	public function invalidConstructorArgumentsProvider() {
		$argLists = array();

		$argLists[] = array( new DecimalValue( '+0' ), '', new DecimalValue( '+0' ), new DecimalValue( '+0' ) );
		$argLists[] = array( new DecimalValue( '+0' ), 1, new DecimalValue( '+0' ), new DecimalValue( '+0' ) );

		$argLists[] = array( new DecimalValue( '+0' ), '1', new DecimalValue( '-0.001' ), new DecimalValue( '-1' ) );
		$argLists[] = array( new DecimalValue( '+0' ), '1', new DecimalValue( '+1' ), new DecimalValue( '+0.001' ) );

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

		$this->assertEquals( $expected->getAmount()->getValue(), $quantity->getAmount()->getValue() );
		$this->assertEquals( $expected->getUpperBound()->getValue(), $quantity->getUpperBound()->getValue() );
		$this->assertEquals( $expected->getLowerBound()->getValue(), $quantity->getLowerBound()->getValue() );
	}

	public function newFromNumberProvider() {
		return array(
			array(
				42, '1', null, null,
				new QuantityValue( new DecimalValue( '+42' ), '1', new DecimalValue( '+42' ), new DecimalValue( '+42' ) )
			),
			array(
				-0.05, '1', null, null,
				new QuantityValue( new DecimalValue( '-0.05' ), '1', new DecimalValue( '-0.05' ), new DecimalValue( '-0.05' ) )
			),
			array(
				0, 'm', 0.5, -0.5,
				new QuantityValue( new DecimalValue( '+0' ), 'm', new DecimalValue( '+0.5' ), new DecimalValue( '-0.5' ) )
			),
			array(
				'+23', '1', null, null,
				new QuantityValue( new DecimalValue( '+23' ), '1', new DecimalValue( '+23' ), new DecimalValue( '+23' ) )
			),
			array(
				'+42', '1', '+43', '+41',
				new QuantityValue( new DecimalValue( '+42' ), '1', new DecimalValue( '+43' ), new DecimalValue( '+41' ) )
			),
			array(
				'-0.05', 'm', '-0.04', '-0.06',
				new QuantityValue( new DecimalValue( '-0.05' ), 'm', new DecimalValue( '-0.04' ), new DecimalValue( '-0.06' ) )
			),
			array(
				new DecimalValue( '+42' ), '1', new DecimalValue( 43 ), new DecimalValue( 41.0 ),
				new QuantityValue( new DecimalValue( '+42' ), '1', new DecimalValue( 43 ), new DecimalValue( 41.0 ) )
			),
		);
	}

	/**
	 * @dataProvider validArraySerializationProvider
	 */
	public function testNewFromArray( $data, UnboundedQuantityValue $expected ) {
		$value = QuantityValue::newFromArray( $data );
		$this->assertTrue( $expected->equals( $value ), $value . ' should equal ' . $expected );
	}

	public function validArraySerializationProvider() {
		return array(
			'complete' => array(
				array(
					'amount' => '+2',
					'unit' => '1',
					'upperBound' => '+2.5',
					'lowerBound' => '+1.5',
				),
				QuantityValue::newFromNumber( '+2', '1', '+2.5', '+1.5' )
			),
			'unbounded' => array(
				array(
					'amount' => '+2',
					'unit' => '1',
				),
				UnboundedQuantityValue::newFromNumber( '+2', '1' )
			),
			'unbounded with existing array keys' => array(
				array(
					'amount' => '+2',
					'unit' => '1',
					'upperBound' => null,
					'lowerBound' => null,
				),
				UnboundedQuantityValue::newFromNumber( '+2', '1' )
			),
		);
	}

	/**
	 * @dataProvider invalidArraySerializationProvider
	 */
	public function testNewFromArray_failure( $data ) {
		$this->setExpectedException( 'DataValues\IllegalValueException' );
		QuantityValue::newFromArray( $data );
	}

	public function invalidArraySerializationProvider() {
		return array(
			'no-amount' => array(
				array(
					'unit' => '1',
					'upperBound' => '+2.5',
					'lowerBound' => '+1.5',
				)
			),
			'no-unit' => array(
				array(
					'amount' => '+2',
					'upperBound' => '+2.5',
					'lowerBound' => '+1.5',
				)
			),
			'no-upperBound' => array(
				array(
					'amount' => '+2',
					'unit' => '1',
					'lowerBound' => '+1.5',
				)
			),
			'no-lowerBound' => array(
				array(
					'amount' => '+2',
					'unit' => '1',
					'upperBound' => '+2.5',
				)
			),
			'bad-amount' => array(
				array(
					'amount' => 'x',
					'unit' => '1',
					'upperBound' => '+2.5',
					'lowerBound' => '+1.5',
				)
			),
			'bad-upperBound' => array(
				array(
					'amount' => '+2',
					'unit' => '1',
					'upperBound' => 'x',
					'lowerBound' => '+1.5',
				)
			),
			'bad-lowerBound' => array(
				array(
					'amount' => '+2',
					'unit' => '1',
					'upperBound' => '+2.5',
					'lowerBound' => 'x',
				)
			),
		);
	}

	/**
	 * @see https://phabricator.wikimedia.org/T110728
	 * @see http://www.regular-expressions.info/anchors.html#realend
	 */
	public function testTrailingNewlineRobustness() {
		$value = QuantityValue::newFromArray( array(
			'amount' => "-0.0\n",
			'unit' => "1\n",
			'upperBound' => "-0.0\n",
			'lowerBound' => "-0.0\n",
		) );

		$this->assertSame( array(
			'amount' => '+0.0',
			'unit' => "1\n",
			'upperBound' => '+0.0',
			'lowerBound' => '+0.0',
		), $value->getArrayValue() );
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
		$this->assertSame( $expected, $quantity->getUncertainty() );
	}

	public function getUncertaintyProvider() {
		return array(
			array( QuantityValue::newFromNumber( '+0', '1', '+0', '+0' ), 0.0 ),

			array( QuantityValue::newFromNumber( '+0', '1', '+1', '-1' ), 2.0 ),
			array( QuantityValue::newFromNumber( '+0.00', '1', '+0.01', '-0.01' ), 0.02 ),
			array( QuantityValue::newFromNumber( '+100', '1', '+101', '+99' ), 2.0 ),
			array( QuantityValue::newFromNumber( '+100.0', '1', '+100.1', '+99.9' ), 0.2 ),
			array( QuantityValue::newFromNumber( '+12.34', '1', '+12.35', '+12.33' ), 0.02 ),

			array( QuantityValue::newFromNumber( '+0', '1', '+0.2', '-0.6' ), 0.8 ),
			array( QuantityValue::newFromNumber( '+7.3', '1', '+7.7', '+5.2' ), 2.5 ),
		);
	}

	/**
	 * @dataProvider getUncertaintyMarginProvider
	 */
	public function testGetUncertaintyMargin( QuantityValue $quantity, $expected ) {
		$this->assertSame( $expected, $quantity->getUncertaintyMargin()->getValue() );
	}

	public function getUncertaintyMarginProvider() {
		return array(
			array( QuantityValue::newFromNumber( '+0', '1', '+1', '-1' ), '+1' ),
			array( QuantityValue::newFromNumber( '+0.00', '1', '+0.01', '-0.01' ), '+0.01' ),

			array( QuantityValue::newFromNumber( '-1', '1', '-1', '-1' ), '+0' ),

			array( QuantityValue::newFromNumber( '+0', '1', '+0.2', '-0.6' ), '+0.6' ),
			array( QuantityValue::newFromNumber( '+7.5', '1', '+7.5', '+5.5' ), '+2.0' ),
			array( QuantityValue::newFromNumber( '+11.5', '1', '+15', '+10.5' ), '+3.5' ),
		);
	}

	/**
	 * @dataProvider getOrderOfUncertaintyProvider
	 */
	public function testGetOrderOfUncertainty( QuantityValue $quantity, $expected ) {
		$this->assertSame( $expected, $quantity->getOrderOfUncertainty() );
	}

	public function getOrderOfUncertaintyProvider() {
		return array(
			0 => array( QuantityValue::newFromNumber( '+0' ), 0 ),
			1 => array( QuantityValue::newFromNumber( '-123' ), 0 ),
			2 => array( QuantityValue::newFromNumber( '-1.23' ), -2 ),

			10 => array( QuantityValue::newFromNumber( '-100', '1', '-99', '-101' ), 0 ),
			11 => array( QuantityValue::newFromNumber( '+0.00', '1', '+0.01', '-0.01' ), -2 ),
			12 => array( QuantityValue::newFromNumber( '-117.3', '1', '-117.2', '-117.4' ), -1 ),

			20 => array( QuantityValue::newFromNumber( '+100', '1', '+100.01', '+99.97' ), -2 ),
			21 => array( QuantityValue::newFromNumber( '-0.002', '1', '-0.001', '-0.004' ), -3 ),
			22 => array( QuantityValue::newFromNumber( '-0.002', '1', '+0.001', '-0.06' ), -3 ),
			23 => array( QuantityValue::newFromNumber( '-21', '1', '+1.1', '-120' ), 1 ),
			24 => array( QuantityValue::newFromNumber( '-2', '1', '+1.1', '-120' ), 0 ),
			25 => array( QuantityValue::newFromNumber( '+1000', '1', '+1100', '+900.03' ), 1 ),
			26 => array( QuantityValue::newFromNumber( '+1000', '1', '+1100', '+900' ), 2 ),
		);
	}

	/**
	 * @dataProvider transformProvider
	 */
	public function testTransform( QuantityValue $quantity, $transformation, QuantityValue $expected ) {
		$args = func_get_args();
		$extraArgs = array_slice( $args, 3 );

		$call = array( $quantity, 'transform' );
		$callArgs = array_merge( array( 'x', $transformation ), $extraArgs );
		$actual = call_user_func_array( $call, $callArgs );

		$this->assertSame( 'x', $actual->getUnit() );
		$this->assertEquals( $expected->getAmount()->getValue(), $actual->getAmount()->getValue(), 'value' );
		$this->assertEquals( $expected->getUpperBound()->getValue(), $actual->getUpperBound()->getValue(), 'upper bound' );
		$this->assertEquals( $expected->getLowerBound()->getValue(), $actual->getLowerBound()->getValue(), 'lower bound' );
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

		return array(
			0 => array(
				QuantityValue::newFromNumber( '+10', '1', '+11', '+9' ),
				$identity,
				QuantityValue::newFromNumber( '+10', '?', '+11', '+9' )
			),
			1 => array(
				QuantityValue::newFromNumber( '-0.5', '1', '-0.4', '-0.6' ),
				$identity,
				QuantityValue::newFromNumber( '-0.5', '?', '-0.4', '-0.6' )
			),
			2 => array(
				QuantityValue::newFromNumber( '+0', '1', '+1', '-1' ),
				$square,
				QuantityValue::newFromNumber( '+0', '?', '+1', '-1' )
			),
			3 => array(
				QuantityValue::newFromNumber( '+10', '1', '+11', '+9' ),
				$square,
				// note how rounding applies to bounds
				QuantityValue::newFromNumber( '+1000', '?', '+1300', '+700' )
			),
			4 => array(
				QuantityValue::newFromNumber( '+0.5', '1', '+0.6', '+0.4' ),
				$scale,
				QuantityValue::newFromNumber( '+0.25', '?', '+0.30', '+0.20' ),
				0.5
			),

			// note: absolutely exact values require conversion with infinite precision!
			10 => array(
				QuantityValue::newFromNumber( '+100', '1', '+100', '+100' ),
				$scale,
				QuantityValue::newFromNumber( '+12825', '?', '+12825', '+12825' ),
				128.25
			),

			11 => array(
				QuantityValue::newFromNumber( '+100', '1', '+110', '+90' ),
				$scale,
				QuantityValue::newFromNumber( '+330', '?', '+370', '+300' ),
				3.3333
			),
			12 => array(
				QuantityValue::newFromNumber( '+100', '1', '+100.1', '+99.9' ),
				$scale,
				QuantityValue::newFromNumber( '+333.3', '?', '+333.7', '+333.0' ),
				3.3333
			),
			13 => array(
				QuantityValue::newFromNumber( '+100', '1', '+100.01', '+99.99' ),
				$scale,
				QuantityValue::newFromNumber( '+333.33', '?', '+333.36', '+333.30' ),
				3.3333
			),
		);
	}

}
