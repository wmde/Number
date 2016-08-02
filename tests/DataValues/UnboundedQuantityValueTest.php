<?php

namespace DataValues\Tests;

use DataValues\DecimalValue;
use DataValues\QuantityValue;
use DataValues\UnboundedQuantityValue;

/**
 * @covers DataValues\UnboundedQuantityValue
 *
 * @group DataValue
 * @group DataValueExtensions
 *
 * @license GPL-2.0+
 * @author Daniel Kinzler
 */
class UnboundedQuantityValueTest extends DataValueTest {

	/**
	 * @see DataValueTest::getClass
	 *
	 * @return string
	 */
	public function getClass() {
		return 'DataValues\UnboundedQuantityValue';
	}

	public function validConstructorArgumentsProvider() {
		$argLists = array();

		$argLists[] = array( new DecimalValue( '+42' ), '1' );
		$argLists[] = array( new DecimalValue( '+0.01' ), '1' );
		$argLists[] = array( new DecimalValue( '-0.5' ), '1' );

		return $argLists;
	}

	public function invalidConstructorArgumentsProvider() {
		$argLists = array();

		$argLists[] = array( new DecimalValue( '+0' ), '' );
		$argLists[] = array( new DecimalValue( '+0' ), 1 );

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
		return array(
			array(
				42, '1',
				new UnboundedQuantityValue( new DecimalValue( '+42' ), '1' )
			),
			array(
				-0.05, '1',
				new UnboundedQuantityValue( new DecimalValue( '-0.05' ), '1' )
			),
			array(
				0, 'm',
				new UnboundedQuantityValue( new DecimalValue( '+0' ), 'm' )
			),
			array(
				'+23', '1',
				new UnboundedQuantityValue( new DecimalValue( '+23' ), '1' )
			),
			array(
				'+42', '1',
				new UnboundedQuantityValue( new DecimalValue( '+42' ), '1' )
			),
			array(
				'-0.05', 'm',
				new UnboundedQuantityValue( new DecimalValue( '-0.05' ), 'm' )
			),
			array(
				new DecimalValue( '+42' ), '1',
				new UnboundedQuantityValue( new DecimalValue( '+42' ), '1' )
			),
		);
	}

	/**
	 * @dataProvider validArraySerializationProvider
	 */
	public function testNewFromArray( $data, UnboundedQuantityValue $expected ) {
		$value = UnboundedQuantityValue::newFromArray( $data );
		$this->assertTrue( $expected->equals( $value ), $value . ' should equal ' . $expected );
	}

	public function validArraySerializationProvider() {
		return array(
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
			'with-extra' => array(
				array(
					'amount' => '+2',
					'unit' => '1',
					'upperBound' => '+2.5',
					'lowerBound' => '+1.5',
				),
				QuantityValue::newFromNumber( '+2', '1', '+2.5', '+1.5' )
			),
		);
	}

	/**
	 * @dataProvider invalidArraySerializationProvider
	 */
	public function testNewFromArray_failure( $data ) {
		$this->setExpectedException( 'DataValues\IllegalValueException' );
		UnboundedQuantityValue::newFromArray( $data );
	}

	public function invalidArraySerializationProvider() {
		return array(
			'no-amount' => array(
				array(
					'unit' => '1',
				)
			),
			'no-unit' => array(
				array(
					'amount' => '+2',
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
		$value = UnboundedQuantityValue::newFromArray( array(
			'amount' => "-0.0\n",
			'unit' => "1\n",
		) );

		$this->assertSame( array(
			'amount' => '+0.0',
			'unit' => "1\n",
		), $value->getArrayValue() );
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
	public function testTransform( UnboundedQuantityValue $quantity, $transformation, UnboundedQuantityValue $expected ) {
		$args = func_get_args();
		$extraArgs = array_slice( $args, 3 );

		$call = array( $quantity, 'transform' );
		$callArgs = array_merge( array( 'x', $transformation ), $extraArgs );
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

		return array(
			0 => array(
				UnboundedQuantityValue::newFromNumber( '+10', '1' ),
				$identity,
				UnboundedQuantityValue::newFromNumber( '+10', '?' )
			),
			1 => array(
				UnboundedQuantityValue::newFromNumber( '-0.5', '1' ),
				$identity,
				UnboundedQuantityValue::newFromNumber( '-0.5', '?' )
			),
			2 => array(
				UnboundedQuantityValue::newFromNumber( '+0', '1' ),
				$square,
				UnboundedQuantityValue::newFromNumber( '+0', '?' )
			),
			3 => array(
				UnboundedQuantityValue::newFromNumber( '+10', '1' ),
				$square,
				UnboundedQuantityValue::newFromNumber( '+1000', '?' )
			),
			4 => array(
				UnboundedQuantityValue::newFromNumber( '+0.5', '1' ),
				$scale,
				UnboundedQuantityValue::newFromNumber( '+0.25', '?' ),
				0.5
			),

			// note: absolutely exact values require conversion with infinite precision!
			10 => array(
				UnboundedQuantityValue::newFromNumber( '+100', '1' ),
				$scale,
				UnboundedQuantityValue::newFromNumber( '+12825', '?' ),
				128.25
			),
			13 => array(
				UnboundedQuantityValue::newFromNumber( '+100', '1' ),
				$scale,
				UnboundedQuantityValue::newFromNumber( '+333.33', '?' ),
				3.3333
			),
		);
	}

}
