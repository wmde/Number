<?php

namespace DataValues\Tests;

use DataValues\DecimalMath;
use DataValues\DecimalValue;

/**
 * @covers \DataValues\DecimalMath
 *
 * @group DataValue
 * @group DataValueExtensions
 *
 * @license GPL-2.0-or-later
 * @author Daniel Kinzler
 */
class DecimalMathTest extends \PHPUnit\Framework\TestCase {

	public function setUp(): void {
		if ( !\extension_loaded( 'bcmath' ) ) {
			$this->markTestSkipped( 'bcmath extension not loaded' );
		}
	}

	/**
	 * @dataProvider bumpProvider
	 */
	public function testBump( DecimalValue $value, $expected ) {
		$math = new DecimalMath();
		$actual = $math->bump( $value );
		$this->assertSame( $expected, $actual->getValue() );
	}

	public function bumpProvider() {
		return [
			[ new DecimalValue( '+0' ), '+1' ],
			[ new DecimalValue( '-0' ), '+1' ],
			[ new DecimalValue( '+0.0' ), '+0.1' ],
			[ new DecimalValue( '-0.0' ), '+0.1' ],
			[ new DecimalValue( '+1' ), '+2' ],
			[ new DecimalValue( '-1' ), '-2' ],
			[ new DecimalValue( '+10' ), '+11' ],
			[ new DecimalValue( '-10' ), '-11' ],
			[ new DecimalValue( '+9' ), '+10' ],
			[ new DecimalValue( '-9' ), '-10' ],
			[ new DecimalValue( '+0.01' ), '+0.02' ],
			[ new DecimalValue( '-0.01' ), '-0.02' ],
			[ new DecimalValue( '+0.09' ), '+0.10' ],
			[ new DecimalValue( '-0.09' ), '-0.10' ],
			[ new DecimalValue( '+0.9' ), '+1.0' ],
			[ new DecimalValue( '-0.9' ), '-1.0' ],
		];
	}

	/**
	 * @dataProvider slumpProvider
	 */
	public function testSlump( DecimalValue $value, $expected ) {
		$math = new DecimalMath();
		$actual = $math->slump( $value );
		$this->assertSame( $expected, $actual->getValue() );
	}

	public function slumpProvider() {
		return [
			[ new DecimalValue( '+0' ), '-1' ],
			[ new DecimalValue( '-0' ), '-1' ],
			[ new DecimalValue( '+0.0' ), '-0.1' ],
			[ new DecimalValue( '-0.0' ), '-0.1' ],
			[ new DecimalValue( '+0.00' ), '-0.01' ],
			[ new DecimalValue( '-0.00' ), '-0.01' ],
			[ new DecimalValue( '+1' ), '+0' ],
			[ new DecimalValue( '-1' ), '+0' ],
			[ new DecimalValue( '+1.0' ), '+0.9' ],
			[ new DecimalValue( '-1.0' ), '-0.9' ],
			[ new DecimalValue( '+0.1' ), '+0.0' ],
			[ new DecimalValue( '-0.1' ), '+0.0' ], // zero is always normalized to be positive
			[ new DecimalValue( '+0.01' ), '+0.00' ],
			[ new DecimalValue( '-0.01' ), '+0.00' ], // zero is always normalized to be positive
			[ new DecimalValue( '+12' ), '+11' ],
			[ new DecimalValue( '-12' ), '-11' ],
			[ new DecimalValue( '+10' ), '+9' ],
			[ new DecimalValue( '-10' ), '-9' ],
			[ new DecimalValue( '+100' ), '+99' ],
			[ new DecimalValue( '-100' ), '-99' ],
			[ new DecimalValue( '+0.02' ), '+0.01' ],
			[ new DecimalValue( '-0.02' ), '-0.01' ],
			[ new DecimalValue( '+0.10' ), '+0.09' ],
			[ new DecimalValue( '-0.10' ), '-0.09' ],
		];
	}

	/**
	 * @dataProvider productProvider
	 */
	public function testProduct( $useBC, DecimalValue $a, DecimalValue $b, $value ) {
		$math = new DecimalMath( $useBC );

		$actual = $math->product( $a, $b );
		$this->assertSame( $value, $actual->getValue() );

		$actual = $math->product( $b, $a );
		$this->assertSame( $value, $actual->getValue() );
	}

	public function productProvider() {
		$cases = [
			[ new DecimalValue( '+0' ), new DecimalValue( '+0' ), '+0' ],
			[ new DecimalValue( '+0' ), new DecimalValue( '+1' ), '+0' ],
			[ new DecimalValue( '+0' ), new DecimalValue( '+2' ), '+0' ],

			[ new DecimalValue( '+1' ), new DecimalValue( '+0' ), '+0' ],
			[ new DecimalValue( '+1' ), new DecimalValue( '+1' ), '+1' ],
			[ new DecimalValue( '+1' ), new DecimalValue( '+2' ), '+2' ],

			[ new DecimalValue( '+2' ), new DecimalValue( '+0' ), '+0' ],
			[ new DecimalValue( '+2' ), new DecimalValue( '+1' ), '+2' ],
			[ new DecimalValue( '+2' ), new DecimalValue( '+2' ), '+4' ],

			[ new DecimalValue( '+0.5' ), new DecimalValue( '+0' ), '+0.0' ],
			[ new DecimalValue( '+0.5' ), new DecimalValue( '+1' ), '+0.5' ],
			[ new DecimalValue( '+0.5' ), new DecimalValue( '+2' ), '+1.0' ],
		];

		foreach ( $cases as $case ) {
			yield array_merge( [ true ], $case );
			yield array_merge( [ false ], $case );
		}
	}

	/**
	 * @dataProvider productLargeFloatProvider
	 */
	public function testProductLargeFloat( $useBC, DecimalValue $a, DecimalValue $b, $regex ) {
		$math = new DecimalMath( $useBC );

		$actual = $math->product( $a, $b );
		$this->assertRegExp( $regex, $actual->getValue() );

		$actual = $math->product( $b, $a );
		$this->assertRegExp( $regex, $actual->getValue() );
	}

	public function productLargeFloatProvider() {
		$cases = [
			[
				new DecimalValue( '+1600000000000000000000000000000000000000000000' ),
				new DecimalValue( '123.45' ),
				'/^\+1975200000000000\d{32}\.\d+$/'
			],
		];

		foreach ( $cases as $case ) {
			yield array_merge( [ true ], $case );
			yield array_merge( [ false ], $case );
		}
	}

	/**
	 * @dataProvider productWithBCProvider
	 */
	public function testProductWithBC( DecimalValue $a, DecimalValue $b, $value ) {
		$math = new DecimalMath();

		$actual = $math->product( $a, $b );
		$this->assertSame( $value, $actual->getValue() );

		$actual = $math->product( $b, $a );
		$this->assertSame( $value, $actual->getValue() );
	}

	public function productWithBCProvider() {
		return [
			[ new DecimalValue( '+0.1' ), new DecimalValue( '+0.1' ), '+0.01' ],
			[ new DecimalValue( '-5000000' ), new DecimalValue( '-0.1' ), '+500000.0' ],
		];
	}

	/**
	 * @dataProvider productLengthOverrunProvider
	 */
	public function testProductLengthOverrun( DecimalValue $a, DecimalValue $b ) {
		$math = new DecimalMath();
		$actual = $math->product( $a, $b );

		$this->assertSame( 127, strlen( $actual->getValue() ) );
	}

	public function productLengthOverrunProvider() {
		return [
			[
				new DecimalValue( '+0.' . str_repeat( '3', 124 ) ),
				new DecimalValue( '+0.' . str_repeat( '6', 124 ) )
			],
			[
				new DecimalValue( '+' . str_repeat( '9', 126 ) ),
				new DecimalValue( '+0.' . str_repeat( '9', 124 ) )
			],
		];
	}

	/**
	 * @dataProvider sumProvider
	 */
	public function testSum( DecimalValue $a, DecimalValue $b, $value ) {
		$math = new DecimalMath();

		$actual = $math->sum( $a, $b );
		$this->assertSame( $value, $actual->getValue() );

		$actual = $math->sum( $b, $a );
		$this->assertSame( $value, $actual->getValue() );
	}

	public function sumProvider() {
		return [
			[ new DecimalValue( '+0' ), new DecimalValue( '+0' ), '+0' ],
			[ new DecimalValue( '+0' ), new DecimalValue( '+1' ), '+1' ],
			[ new DecimalValue( '+0' ), new DecimalValue( '+2' ), '+2' ],

			[ new DecimalValue( '+2' ), new DecimalValue( '+0' ), '+2' ],
			[ new DecimalValue( '+2' ), new DecimalValue( '+1' ), '+3' ],
			[ new DecimalValue( '+2' ), new DecimalValue( '+2' ), '+4' ],

			[ new DecimalValue( '+0.5' ), new DecimalValue( '+0' ), '+0.5' ],
			[ new DecimalValue( '+0.5' ), new DecimalValue( '+0.5' ), '+1.0' ],
			[ new DecimalValue( '+0.5' ), new DecimalValue( '+2' ), '+2.5' ],
		];
	}

	/**
	 * @dataProvider minMaxProvider
	 */
	public function testMin( DecimalValue $min, DecimalValue $max ) {
		$math = new DecimalMath();

		$actual = $math->min( $min, $max );
		$this->assertSame( $min->getValue(), $actual->getValue() );

		$actual = $math->min( $max, $min );
		$this->assertSame( $min->getValue(), $actual->getValue() );
	}

	/**
	 * @dataProvider minMaxProvider
	 */
	public function testMax( DecimalValue $min, DecimalValue $max ) {
		$math = new DecimalMath();

		$actual = $math->max( $min, $max );
		$this->assertSame( $max->getValue(), $actual->getValue() );

		$actual = $math->max( $max, $min );
		$this->assertSame( $max->getValue(), $actual->getValue() );
	}

	public function minMaxProvider() {
		return [
			[ new DecimalValue( '+0' ), new DecimalValue( '+0' ) ],
			[ new DecimalValue( '+1' ), new DecimalValue( '+1' ) ],
			[ new DecimalValue( '-0.2' ), new DecimalValue( '-0.2' ) ],

			[ new DecimalValue( '-2' ), new DecimalValue( '+1' ) ],
			[ new DecimalValue( '+0.33333' ), new DecimalValue( '+1' ) ],
		];
	}

	/**
	 * @dataProvider roundToDigitProvider
	 */
	public function testRoundToDigit( DecimalValue $value, $digits, $expected ) {
		$math = new DecimalMath();

		$actual = $math->roundToDigit( $value, $digits );
		$this->assertSame( $expected, $actual->getValue() );
	}

	public function roundToDigitProvider() {
		$argLists = [];

		//NOTE: Rounding is applied using the "round half away from zero" logic.

		$argLists[] = [ new DecimalValue( '-2' ), 0, '+0' ]; // no digits left

		$argLists[] = [ new DecimalValue( '+0' ), 1, '+0' ];
		$argLists[] = [ new DecimalValue( '+0' ), 2, '+0' ];
		$argLists[] = [ new DecimalValue( '+0.0' ), 1, '+0' ];
		$argLists[] = [ new DecimalValue( '+0.0' ), 2, '+0' ];
		$argLists[] = [ new DecimalValue( '+0.0' ), 3, '+0.0' ];

		$argLists[] = [ new DecimalValue( '+1.45' ), 1, '+1' ];
		$argLists[] = [ new DecimalValue( '+1.45' ), 3, '+1.5' ];
		$argLists[] = [ new DecimalValue( '+1.45' ), 4, '+1.45' ];

		$argLists[] = [ new DecimalValue( '-1.45' ), 1, '-1' ];
		$argLists[] = [ new DecimalValue( '-1.45' ), 3, '-1.5' ];
		$argLists[] = [ new DecimalValue( '-1.45' ), 4, '-1.45' ];

		$argLists[] = [ new DecimalValue( '+9.99' ), 1, '+10' ];
		$argLists[] = [ new DecimalValue( '+9.99' ), 3, '+10.0' ];
		$argLists[] = [ new DecimalValue( '+9.99' ), 4, '+9.99' ];

		$argLists[] = [ new DecimalValue( '+135.7' ), 1, '+100' ];
		$argLists[] = [ new DecimalValue( '+135.7' ), 3, '+136' ];
		$argLists[] = [ new DecimalValue( '+135.7' ), 5, '+135.7' ];

		$argLists[] = [ new DecimalValue( '-2' ), 1, '-2' ];
		$argLists[] = [ new DecimalValue( '-2' ), 2, '-2' ];

		$argLists[] = [ new DecimalValue( '+23' ), 1, '+20' ];
		$argLists[] = [ new DecimalValue( '+23' ), 2, '+23' ];
		$argLists[] = [ new DecimalValue( '+23' ), 3, '+23' ];
		$argLists[] = [ new DecimalValue( '+23' ), 4, '+23' ];

		$argLists[] = [ new DecimalValue( '-234' ), 1, '-200' ];
		$argLists[] = [ new DecimalValue( '-234' ), 2, '-230' ];
		$argLists[] = [ new DecimalValue( '-234' ), 3, '-234' ];

		$argLists[] = [ new DecimalValue( '-2.0' ), 1, '-2' ];
		$argLists[] = [ new DecimalValue( '-2.0' ), 2, '-2' ];   // not padded (can't end with decimal point)
		$argLists[] = [ new DecimalValue( '-2.0' ), 3, '-2.0' ];
		$argLists[] = [ new DecimalValue( '-2.0' ), 4, '-2.0' ]; // no extra digits

		$argLists[] = [ new DecimalValue( '-2.000' ), 1, '-2' ];
		$argLists[] = [ new DecimalValue( '-2.000' ), 2, '-2' ];
		$argLists[] = [ new DecimalValue( '-2.000' ), 3, '-2.0' ];
		$argLists[] = [ new DecimalValue( '-2.000' ), 4, '-2.00' ];

		$argLists[] = [ new DecimalValue( '+2.5' ), 1, '+3' ]; // rounded up
		$argLists[] = [ new DecimalValue( '+2.5' ), 2, '+3' ];
		$argLists[] = [ new DecimalValue( '+2.5' ), 3, '+2.5' ];
		$argLists[] = [ new DecimalValue( '+2.5' ), 4, '+2.5' ];

		$argLists[] = [ new DecimalValue( '+2.05' ), 1, '+2' ];
		$argLists[] = [ new DecimalValue( '+2.05' ), 2, '+2' ];
		$argLists[] = [ new DecimalValue( '+2.05' ), 3, '+2.1' ]; // rounded up
		$argLists[] = [ new DecimalValue( '+2.05' ), 4, '+2.05' ];

		$argLists[] = [ new DecimalValue( '-23.05' ), 1, '-20' ];
		$argLists[] = [ new DecimalValue( '-23.05' ), 2, '-23' ];
		$argLists[] = [ new DecimalValue( '-23.05' ), 3, '-23' ]; // not padded (can't end with decimal point)
		$argLists[] = [ new DecimalValue( '-23.05' ), 4, '-23.1' ]; // rounded down
		$argLists[] = [ new DecimalValue( '-23.05' ), 5, '-23.05' ];

		$argLists[] = [ new DecimalValue( '+9.33' ), 1, '+9' ]; // no rounding
		$argLists[] = [ new DecimalValue( '+9.87' ), 1, '+10' ]; // rounding ripples up
		$argLists[] = [ new DecimalValue( '+9.87' ), 3, '+9.9' ]; // rounding ripples up
		$argLists[] = [ new DecimalValue( '+99' ), 1, '+100' ]; // rounding ripples up
		$argLists[] = [ new DecimalValue( '+99' ), 2, '+99' ]; // rounding ripples up

		$argLists[] = [ new DecimalValue( '-9.33' ), 1, '-9' ]; // no rounding
		$argLists[] = [ new DecimalValue( '-9.87' ), 1, '-10' ]; // rounding ripples down
		$argLists[] = [ new DecimalValue( '-9.87' ), 3, '-9.9' ]; // rounding ripples down
		$argLists[] = [ new DecimalValue( '-99' ), 1, '-100' ]; // rounding ripples down
		$argLists[] = [ new DecimalValue( '-99' ), 2, '-99' ]; // rounding ripples down

		return $argLists;
	}

	/**
	 * @dataProvider getPositionForExponentProvider
	 */
	public function testGetPositionForExponent( $exponent, DecimalValue $decimal, $expected ) {
		$math = new DecimalMath();

		$actual = $math->getPositionForExponent( $exponent, $decimal );
		$this->assertSame( $expected, $actual );
	}

	public function getPositionForExponentProvider() {
		$argLists = [];

		$argLists[] = [ 0, new DecimalValue( '+0' ), 1 ];
		$argLists[] = [ 1, new DecimalValue( '+10.25' ), 1 ];
		$argLists[] = [ 1, new DecimalValue( '-100.25' ), 2 ];
		$argLists[] = [ 2, new DecimalValue( '+100.25' ), 1 ];
		$argLists[] = [ -2, new DecimalValue( '+0.234' ), 4 ];
		$argLists[] = [ -2, new DecimalValue( '+11.234' ), 5 ];

		return $argLists;
	}

	/**
	 * @dataProvider roundToExponentProvider
	 */
	public function testRoundToExponent( DecimalValue $value, $digits, $expected ) {
		$math = new DecimalMath();

		$actual = $math->roundToExponent( $value, $digits );
		$this->assertSame( $expected, $actual->getValue() );
	}

	public function roundToExponentProvider() {
		$argLists = [];

		//NOTE: Rounding is applied using the "round half away from zero" logic.

		$argLists[] = [ new DecimalValue( '+0' ), 0, '+0' ];
		$argLists[] = [ new DecimalValue( '+0' ), 1, '+0' ];
		$argLists[] = [ new DecimalValue( '+0.0' ), 0, '+0' ];
		$argLists[] = [ new DecimalValue( '+0.0' ), 2, '+0' ];
		$argLists[] = [ new DecimalValue( '+0.0' ), -5, '+0.0' ]; // no extra digits

		$argLists[] = [ new DecimalValue( '+1.45' ), 0, '+1' ];
		$argLists[] = [ new DecimalValue( '+1.45' ), 2, '+0' ];
		$argLists[] = [ new DecimalValue( '+1.45' ), -5, '+1.45' ];

		$argLists[] = [ new DecimalValue( '+99.99' ), 0, '+100' ];
		$argLists[] = [ new DecimalValue( '+99.99' ), 2, '+0' ];
		$argLists[] = [ new DecimalValue( '+99.99' ), -1, '+100.0' ];
		$argLists[] = [ new DecimalValue( '+99.99' ), -5, '+99.99' ];

		$argLists[] = [ new DecimalValue( '-2' ), 0, '-2' ];
		$argLists[] = [ new DecimalValue( '-2' ), -1, '-2' ];
		$argLists[] = [ new DecimalValue( '-2' ), 1, '+0' ];

		$argLists[] = [ new DecimalValue( '+23' ), 0, '+23' ];
		$argLists[] = [ new DecimalValue( '+23' ), 1, '+20' ];
		$argLists[] = [ new DecimalValue( '+23' ), 2, '+0' ];

		$argLists[] = [ new DecimalValue( '-234' ), 2, '-200' ];
		$argLists[] = [ new DecimalValue( '-234' ), 1, '-230' ];
		$argLists[] = [ new DecimalValue( '-234' ), 0, '-234' ];

		$argLists[] = [ new DecimalValue( '-2.0' ), 0, '-2' ];
		$argLists[] = [ new DecimalValue( '-2.0' ), -1, '-2.0' ];
		$argLists[] = [ new DecimalValue( '-2.0' ), -2, '-2.0' ]; // no extra digits

		$argLists[] = [ new DecimalValue( '-2.000' ), 0, '-2' ];
		$argLists[] = [ new DecimalValue( '-2.000' ), -1, '-2.0' ];
		$argLists[] = [ new DecimalValue( '-2.000' ), -2, '-2.00' ];

		$argLists[] = [ new DecimalValue( '+2.5' ), 0, '+3' ]; // rounded up
		$argLists[] = [ new DecimalValue( '+2.5' ), -1, '+2.5' ];
		$argLists[] = [ new DecimalValue( '+2.5' ), -2, '+2.5' ]; // no extra digits

		$argLists[] = [ new DecimalValue( '+2.05' ), 0, '+2' ];
		$argLists[] = [ new DecimalValue( '+2.05' ), -1, '+2.1' ]; // rounded up
		$argLists[] = [ new DecimalValue( '+2.05' ), -2, '+2.05' ];

		$argLists[] = [ new DecimalValue( '-23.05' ), 1, '-20' ];
		$argLists[] = [ new DecimalValue( '-23.05' ), 0, '-23' ];

		$argLists[] = [ new DecimalValue( '-23.05' ), -1, '-23.1' ]; // rounded down
		$argLists[] = [ new DecimalValue( '-23.05' ), -2, '-23.05' ];

		$argLists[] = [ new DecimalValue( '+9.33' ), 0, '+9' ]; // no rounding
		$argLists[] = [ new DecimalValue( '+9.87' ), 0, '+10' ]; // rounding ripples up
		$argLists[] = [ new DecimalValue( '+9.87' ), -1, '+9.9' ]; // rounding ripples up
		$argLists[] = [ new DecimalValue( '+99' ), 1, '+100' ]; // rounding ripples up
		$argLists[] = [ new DecimalValue( '+99' ), 0, '+99' ]; // rounding ripples up

		$argLists[] = [ new DecimalValue( '-9.33' ), 0, '-9' ]; // no rounding
		$argLists[] = [ new DecimalValue( '-9.87' ), 0, '-10' ]; // rounding ripples down
		$argLists[] = [ new DecimalValue( '-9.87' ), -1, '-9.9' ]; // rounding ripples down
		$argLists[] = [ new DecimalValue( '-99' ), 1, '-100' ]; // rounding ripples down
		$argLists[] = [ new DecimalValue( '-99' ), 0, '-99' ]; // rounding ripples down

		return $argLists;
	}

	/**
	 * @dataProvider shiftProvider
	 */
	public function testShift( DecimalValue $value, $exponent, $expected ) {
		$math = new DecimalMath();

		$actual = $math->shift( $value, $exponent );
		$this->assertSame( $expected, $actual->getValue() );
	}

	public function shiftProvider() {
		$argLists = [];

		$argLists[] = [ new DecimalValue( '+0' ), 0, '+0' ];
		$argLists[] = [ new DecimalValue( '+0' ), 1, '+0' ];
		$argLists[] = [ new DecimalValue( '+0' ), 2, '+0' ];
		$argLists[] = [ new DecimalValue( '+0' ), -1, '+0.0' ];
		$argLists[] = [ new DecimalValue( '+0' ), -2, '+0.00' ];

		$argLists[] = [ new DecimalValue( '+0.0' ), 0, '+0.0' ];
		$argLists[] = [ new DecimalValue( '+0.0' ), 1, '+0' ];
		$argLists[] = [ new DecimalValue( '+0.0' ), 2, '+0' ];
		$argLists[] = [ new DecimalValue( '+0.0' ), -1, '+0.00' ];
		$argLists[] = [ new DecimalValue( '+0.0' ), -2, '+0.000' ];

		$argLists[] = [ new DecimalValue( '-125' ), 0, '-125' ];
		$argLists[] = [ new DecimalValue( '-125' ), 1, '-1250' ];
		$argLists[] = [ new DecimalValue( '-125' ), 2, '-12500' ];
		$argLists[] = [ new DecimalValue( '-125' ), -1, '-12.5' ];
		$argLists[] = [ new DecimalValue( '-125' ), -2, '-1.25' ];
		$argLists[] = [ new DecimalValue( '-125' ), -3, '-0.125' ];
		$argLists[] = [ new DecimalValue( '-125' ), -4, '-0.0125' ];

		$argLists[] = [ new DecimalValue( '-2.5' ), 0, '-2.5' ];
		$argLists[] = [ new DecimalValue( '-2.5' ), 1, '-25' ];
		$argLists[] = [ new DecimalValue( '-2.5' ), 2, '-250' ];
		$argLists[] = [ new DecimalValue( '-2.5' ), -1, '-0.25' ];
		$argLists[] = [ new DecimalValue( '-2.5' ), -2, '-0.025' ];
		$argLists[] = [ new DecimalValue( '-2.5' ), -3, '-0.0025' ];

		$argLists[] = [ new DecimalValue( '+5' ), -4, '+0.0005' ];
		$argLists[] = [ new DecimalValue( '+5.0' ), -4, '+0.00050' ];
		$argLists[] = [ new DecimalValue( '+5.00' ), -4, '+0.000500' ];

		return $argLists;
	}

}
