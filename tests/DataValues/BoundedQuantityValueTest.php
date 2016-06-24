<?php

namespace DataValues\Tests;

use DataValues\BoundedQuantityValue;
use DataValues\DecimalValue;

/**
 * @covers DataValues\BoundedQuantityValue
 *
 * @group DataValue
 * @group DataValueExtensions
 *
 * @license GPL-2.0+
 * @author Daniel Kinzler
 */
class BoundedQuantityValueTest extends DataValueTest {

	/**
	 * @see DataValueTest::getClass
	 *
	 * @return string
	 */
	public function getClass() {
		return 'DataValues\BoundedQuantityValue';
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
	public function testGetValue( BoundedQuantityValue $quantity, array $arguments ) {
		$this->assertInstanceOf( $this->getClass(), $quantity->getValue() );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetAmount( BoundedQuantityValue $quantity, array $arguments ) {
		$this->assertEquals( $arguments[0], $quantity->getAmount() );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetUnit( BoundedQuantityValue $quantity, array $arguments ) {
		$this->assertEquals( $arguments[1], $quantity->getUnit() );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetUpperBound( BoundedQuantityValue $quantity, array $arguments ) {
		$this->assertEquals( $arguments[2], $quantity->getUpperBound() );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetLowerBound( BoundedQuantityValue $quantity, array $arguments ) {
		$this->assertEquals( $arguments[3], $quantity->getLowerBound() );
	}

	/**
	 * @dataProvider newFromNumberProvider
	 */
	public function testNewFromNumber( $amount, $unit, $upperBound, $lowerBound, BoundedQuantityValue $expected ) {
		$quantity = BoundedQuantityValue::newFromNumber( $amount, $unit, $upperBound, $lowerBound );

		$this->assertEquals( $expected->getAmount()->getValue(), $quantity->getAmount()->getValue() );
		$this->assertEquals( $expected->getUpperBound()->getValue(), $quantity->getUpperBound()->getValue() );
		$this->assertEquals( $expected->getLowerBound()->getValue(), $quantity->getLowerBound()->getValue() );
	}

	public function newFromNumberProvider() {
		return array(
			array(
				42, '1', null, null,
				new BoundedQuantityValue( new DecimalValue( '+42' ), '1', new DecimalValue( '+42' ), new DecimalValue( '+42' ) )
			),
			array(
				-0.05, '1', null, null,
				new BoundedQuantityValue( new DecimalValue( '-0.05' ), '1', new DecimalValue( '-0.05' ), new DecimalValue( '-0.05' ) )
			),
			array(
				0, 'm', 0.5, -0.5,
				new BoundedQuantityValue( new DecimalValue( '+0' ), 'm', new DecimalValue( '+0.5' ), new DecimalValue( '-0.5' ) )
			),
			array(
				'+23', '1', null, null,
				new BoundedQuantityValue( new DecimalValue( '+23' ), '1', new DecimalValue( '+23' ), new DecimalValue( '+23' ) )
			),
			array(
				'+42', '1', '+43', '+41',
				new BoundedQuantityValue( new DecimalValue( '+42' ), '1', new DecimalValue( '+43' ), new DecimalValue( '+41' ) )
			),
			array(
				'-0.05', 'm', '-0.04', '-0.06',
				new BoundedQuantityValue( new DecimalValue( '-0.05' ), 'm', new DecimalValue( '-0.04' ), new DecimalValue( '-0.06' ) )
			),
			array(
				new DecimalValue( '+42' ), '1', new DecimalValue( 43 ), new DecimalValue( 41.0 ),
				new BoundedQuantityValue( new DecimalValue( '+42' ), '1', new DecimalValue( 43 ), new DecimalValue( 41.0 ) )
			),
		);
	}

	/**
	 * @see https://phabricator.wikimedia.org/T110728
	 * @see http://www.regular-expressions.info/anchors.html#realend
	 */
	public function testTrailingNewlineRobustness() {
		$value = BoundedQuantityValue::newFromArray( array(
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
	public function testGetSortKey( BoundedQuantityValue $quantity ) {
		$this->assertEquals( $quantity->getAmount()->getValueFloat(), $quantity->getSortKey() );
	}

	/**
	 * @dataProvider getUncertaintyProvider
	 */
	public function testGetUncertainty( BoundedQuantityValue $quantity, $expected ) {
		$actual = $quantity->getUncertainty();

		// floats are wonkey, accept small differences here
		$this->assertTrue( abs( $actual - $expected ) < 0.000000001, "expected $expected, got $actual" );
	}

	public function getUncertaintyProvider() {
		return array(
			array( BoundedQuantityValue::newFromNumber( '+0', '1', '+0', '+0' ), 0 ),

			array( BoundedQuantityValue::newFromNumber( '+0', '1', '+1', '-1' ), 2 ),
			array( BoundedQuantityValue::newFromNumber( '+0.00', '1', '+0.01', '-0.01' ), 0.02 ),
			array( BoundedQuantityValue::newFromNumber( '+100', '1', '+101', '+99' ), 2 ),
			array( BoundedQuantityValue::newFromNumber( '+100.0', '1', '+100.1', '+99.9' ), 0.2 ),
			array( BoundedQuantityValue::newFromNumber( '+12.34', '1', '+12.35', '+12.33' ), 0.02 ),

			array( BoundedQuantityValue::newFromNumber( '+0', '1', '+0.2', '-0.6' ), 0.8 ),
			array( BoundedQuantityValue::newFromNumber( '+7.3', '1', '+7.7', '+5.2' ), 2.5 ),
		);
	}

	/**
	 * @dataProvider getUncertaintyMarginProvider
	 */
	public function testGetUncertaintyMargin( BoundedQuantityValue $quantity, $expected ) {
		$actual = $quantity->getUncertaintyMargin();

		$this->assertEquals( $expected, $actual->getValue() );
	}

	public function getUncertaintyMarginProvider() {
		return array(
			array( BoundedQuantityValue::newFromNumber( '+0', '1', '+1', '-1' ), '+1' ),
			array( BoundedQuantityValue::newFromNumber( '+0.00', '1', '+0.01', '-0.01' ), '+0.01' ),

			array( BoundedQuantityValue::newFromNumber( '-1', '1', '-1', '-1' ), '+0' ),

			array( BoundedQuantityValue::newFromNumber( '+0', '1', '+0.2', '-0.6' ), '+0.6' ),
			array( BoundedQuantityValue::newFromNumber( '+7.5', '1', '+7.5', '+5.5' ), '+2' ),
			array( BoundedQuantityValue::newFromNumber( '+11.5', '1', '+15', '+10.5' ), '+3.5' ),
		);
	}

	/**
	 * @dataProvider getOrderOfUncertaintyProvider
	 */
	public function testGetOrderOfUncertainty( BoundedQuantityValue $quantity, $expected ) {
		$actual = $quantity->getOrderOfUncertainty();

		$this->assertEquals( $expected, $actual );
	}

	public function getOrderOfUncertaintyProvider() {
		return array(
			0 => array( BoundedQuantityValue::newFromNumber( '+0' ), 0 ),
			1 => array( BoundedQuantityValue::newFromNumber( '-123' ), 0 ),
			2 => array( BoundedQuantityValue::newFromNumber( '-1.23' ), -2 ),

			10 => array( BoundedQuantityValue::newFromNumber( '-100', '1', '-99', '-101' ), 0 ),
			11 => array( BoundedQuantityValue::newFromNumber( '+0.00', '1', '+0.01', '-0.01' ), -2 ),
			12 => array( BoundedQuantityValue::newFromNumber( '-117.3', '1', '-117.2', '-117.4' ), -1 ),

			20 => array( BoundedQuantityValue::newFromNumber( '+100', '1', '+100.01', '+99.97' ), -2 ),
			21 => array( BoundedQuantityValue::newFromNumber( '-0.002', '1', '-0.001', '-0.004' ), -3 ),
			22 => array( BoundedQuantityValue::newFromNumber( '-0.002', '1', '+0.001', '-0.06' ), -3 ),
			23 => array( BoundedQuantityValue::newFromNumber( '-21', '1', '+1.1', '-120' ), 1 ),
			24 => array( BoundedQuantityValue::newFromNumber( '-2', '1', '+1.1', '-120' ), 0 ),
			25 => array( BoundedQuantityValue::newFromNumber( '+1000', '1', '+1100', '+900.03' ), 1 ),
			26 => array( BoundedQuantityValue::newFromNumber( '+1000', '1', '+1100', '+900' ), 2 ),
		);
	}

	/**
	 * @dataProvider transformProvider
	 */
	public function testTransform( BoundedQuantityValue $quantity, $transformation, BoundedQuantityValue $expected ) {
		$args = func_get_args();
		$extraArgs = array_slice( $args, 3 );

		$call = array( $quantity, 'transform' );
		$callArgs = array_merge( array( 'x', $transformation ), $extraArgs );
		$actual = call_user_func_array( $call, $callArgs );

		$this->assertEquals( 'x', $actual->getUnit() );
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
			 0 => array( BoundedQuantityValue::newFromNumber( '+10',   '1', '+11',  '+9' ),   $identity, BoundedQuantityValue::newFromNumber(   '+10',    '?',   '+11',    '+9' ) ),
			 1 => array( BoundedQuantityValue::newFromNumber(  '-0.5', '1', '-0.4', '-0.6' ), $identity, BoundedQuantityValue::newFromNumber(    '-0.5',  '?',    '-0.4',  '-0.6' ) ),
			 2 => array( BoundedQuantityValue::newFromNumber(  '+0',   '1', '+1',   '-1' ),   $square,   BoundedQuantityValue::newFromNumber(    '+0',    '?',    '+1',    '-1' ) ),
			 3 => array( BoundedQuantityValue::newFromNumber( '+10',   '1', '+11',  '+9' ),   $square,   BoundedQuantityValue::newFromNumber( '+1000',    '?', '+1300',  '+700' ) ), // note how rounding applies to bounds
			 4 => array( BoundedQuantityValue::newFromNumber(  '+0.5', '1', '+0.6', '+0.4' ), $scale,    BoundedQuantityValue::newFromNumber(    '+0.25', '?',    '+0.3',  '+0.2' ), 0.5 ),

			// note: absolutely exact values require conversion with infinite precision!
			10 => array( BoundedQuantityValue::newFromNumber( '+100', '1', '+100',   '+100' ),    $scale, BoundedQuantityValue::newFromNumber( '+12825.0', '?', '+12825.0', '+12825.0' ), 128.25 ),

			11 => array( BoundedQuantityValue::newFromNumber( '+100', '1', '+110',    '+90' ),    $scale, BoundedQuantityValue::newFromNumber( '+330',    '?', '+370',    '+300' ), 3.3333 ),
			12 => array( BoundedQuantityValue::newFromNumber( '+100', '1', '+100.1',  '+99.9' ),  $scale, BoundedQuantityValue::newFromNumber( '+333.3',  '?', '+333.7',  '+333.0' ), 3.3333 ),
			13 => array( BoundedQuantityValue::newFromNumber( '+100', '1', '+100.01', '+99.99' ), $scale, BoundedQuantityValue::newFromNumber( '+333.33', '?', '+333.36', '+333.30' ), 3.3333 ),
		);
	}

}
