<?php

namespace ValueParsers\Test;

use DataValues\DecimalValue;
use ValueParsers\DecimalParser;

/**
 * @covers ValueParsers\DecimalParser
 *
 * @group DataValue
 * @group DataValueExtensions
 *
 * @licence GNU GPL v2+
 * @author Daniel Kinzler
 */
class DecimalParserTest extends StringValueParserTest {

	/**
	 * @deprecated since 0.3, just use getInstance.
	 */
	protected function getParserClass() {
		throw new \LogicException( 'Should not be called, use getInstance' );
	}

	/**
	 * @see ValueParserTestBase::getInstance
	 *
	 * @return DecimalParser
	 */
	protected function getInstance() {
		return new DecimalParser();
	}

	/**
	 * @see ValueParserTestBase::validInputProvider
	 */
	public function validInputProvider() {
		$argLists = array();

		$valid = array(
			'0' => 0,
			'-0' => 0,
			'-00.00' => '-0.00',
			'+00.00' => '+0.00',
			'0001' => 1,
			'+42' => 42,
			'+01' => 01,
			'9001' => 9001,
			'-1' => -1,
			'-42' => -42,
			'.5' => 0.5,
			'-.125' => -0.125,
			'3.' => 3,
			',3,' => 3,
			'2.125' => 2.125,
			'2.1250' => '+2.1250',
			'2.1250e0' => '+2.1250',
			'2.1250e3' => '+2125.0',
			'2.1250e+3' => '+2125.0',
			'2.1250e-2' => '+0.021250',
			'123e+3' => '+123000',
			'123e-2' => '+1.23',
			'-123e-5' => '-0.00123',
			' 5 ' => 5,
			'100,000' => 100000,
			'100 000' => 100000,
			'100\'000' => 100000,
		);

		foreach ( $valid as $value => $expected ) {
			// Because PHP turns them into ints using black magic
			$value = (string)$value;

			$expected = new DecimalValue( $expected );
			$argLists[] = array( $value, $expected );
		}

		return $argLists;
	}

	/**
	 * @see ValueParserTestBase::invalidInputProvider
	 */
	public function invalidInputProvider() {
		$argLists = parent::invalidInputProvider();

		$invalid = array(
			'foo',
			'',
			'--1',
			'1-',
			'one',
			'0x20',
			'1+1',
			'1-1',
			'1.2.3',
		);

		foreach ( $invalid as $value ) {
			$argLists[] = array( $value );
		}

		return $argLists;
	}

	public function testUnlocalization() {
		$unlocalizer = $this->getMock( 'ValueParsers\NumberUnlocalizer' );

		$unlocalizer->expects( $this->once() )
			->method( 'unlocalizeNumber' )
			->will( $this->returnCallback( function( $number ) {
				return str_replace( '#', '', $number );
			} ) );

		$unlocalizer->expects( $this->never() )
			->method( 'getNumberRegex' );

		$unlocalizer->expects( $this->never() )
			->method( 'getUnitRegex' );

		$parser = new DecimalParser( null, $unlocalizer );

		$input = '###20#000#000###';
		$value = $parser->parse( $input );

		$this->assertEquals( '20000000', $value->getValue() );
	}

	public function splitDecimalExponentProvider() {
		return array(
			'no exponent' => array( '1.2', '1.2', 0 ),
			'exponent' => array( '1.2E3', '1.2', 3 ),
			'negative exponent' => array( '+1.2e-2', '+1.2', -2 ),
			'positive exponent' => array( '-12e+3', '-12', 3 ),
			'leading zero' => array( '12e+09', '12', 9 ),
			'trailing decimal point' => array( '12.e+3', '12.', 3 ),
			'leading decimal point' => array( '.12e+3', '.12', 3 ),
			'space' => array( '12 e+3', '12 ', 3 ),
			'x10 syntax' => array( '12x10^3', '12', 3 ),
			'comma' => array( '12e3,4', '12', 34 ),
		);
	}

	/**
	 * @dataProvider splitDecimalExponentProvider
	 */
	public function testSplitDecimalExponent( $valueString, $expectedDecimal, $expectedExponent ) {
		$parser = new DecimalParser();
		list( $decimal, $exponent ) = $parser->splitDecimalExponent( $valueString );

		$this->assertSame( $expectedDecimal, $decimal );
		$this->assertSame( $expectedExponent, $exponent );
	}

	public function applyDecimalExponentProvider() {
		return array(
			'no exponent' => array( new DecimalValue( '+1.2' ), 0, new DecimalValue( '+1.2' ) ),
			'negative exponent' => array( new DecimalValue( '-1.2' ), -2, new DecimalValue( '-0.012' ) ),
			'positive exponent' => array( new DecimalValue( '-12' ), 3, new DecimalValue( '-12000' ) ),
		);
	}

	/**
	 * @dataProvider applyDecimalExponentProvider
	 */
	public function testApplyDecimalExponent( DecimalValue $decimal, $exponent, DecimalValue $expectedDecimal ) {
		$parser = new DecimalParser();
		$actualDecimal = $parser->applyDecimalExponent( $decimal, $exponent );

		$this->assertSame( $expectedDecimal->getValue(), $actualDecimal->getValue() );
	}

}
