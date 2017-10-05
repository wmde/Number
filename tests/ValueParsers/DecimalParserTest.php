<?php

namespace ValueParsers\Test;

use DataValues\DecimalValue;
use ValueParsers\DecimalParser;
use ValueParsers\NumberUnlocalizer;

/**
 * @covers ValueParsers\DecimalParser
 *
 * @group DataValue
 * @group DataValueExtensions
 *
 * @license GPL-2.0+
 * @author Daniel Kinzler
 */
class DecimalParserTest extends StringValueParserTest {

	/**
	 * @see ValueParserTestBase::getInstance
	 *
	 * @return DecimalParser
	 */
	protected function getInstance() {
		$unlocalizer = $this->getMock( NumberUnlocalizer::class );
		$unlocalizer->method( 'unlocalizeNumber' )
			->will( $this->returnArgument( 0 ) );

		return new DecimalParser( null, $unlocalizer );
	}

	/**
	 * @see ValueParserTestBase::validInputProvider
	 */
	public function validInputProvider() {
		$argLists = [];

		$valid = [
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

			// U+000C (form feed)
			"5\f" => 5,
			// U+00A0 (non-break space)
			"5\xC2\xA0200" => 5200,
			// U+202F (narrow no-break space)
			"5\xE2\x80\xAF300" => 5300,
		];

		foreach ( $valid as $value => $expected ) {
			// Because PHP turns them into ints using black magic
			$value = (string)$value;

			$expected = new DecimalValue( $expected );
			$argLists[] = [ $value, $expected ];
		}

		return $argLists;
	}

	/**
	 * @see StringValueParserTest::invalidInputProvider
	 */
	public function invalidInputProvider() {
		$argLists = parent::invalidInputProvider();

		$invalid = [
			'foo',
			'',
			'--1',
			'1-',
			'one',
			'0x20',
			'1+1',
			'1-1',
			'1.2.3',
		];

		foreach ( $invalid as $value ) {
			$argLists[] = [ $value ];
		}

		return $argLists;
	}

	public function testUnlocalization() {
		$unlocalizer = $this->getMock( NumberUnlocalizer::class );

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
		/** @var DecimalValue $value */
		$value = $parser->parse( $input );

		$this->assertSame( '+20000000', $value->getValue() );
	}

	public function splitDecimalExponentProvider() {
		return [
			'trailing newline' => [ "1.2E3\n", '1.2', 3 ],
			'whitespace' => [ ' 1.2E3 ', ' 1.2E3 ', 0 ],
			'no exponent' => [ '1.2', '1.2', 0 ],
			'exponent' => [ '1.2E3', '1.2', 3 ],
			'negative exponent' => [ '+1.2e-2', '+1.2', -2 ],
			'positive exponent' => [ '-12e+3', '-12', 3 ],
			'leading zero' => [ '12e+09', '12', 9 ],
			'trailing decimal point' => [ '12.e+3', '12.', 3 ],
			'leading decimal point' => [ '.12e+3', '.12', 3 ],
			'space' => [ '12 e+3', '12 ', 3 ],
			'x10 syntax' => [ '12x10^3', '12', 3 ],
			'comma' => [ '12e3,4', '12', 34 ],
		];
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
		return [
			'no exponent' => [ new DecimalValue( '+1.2' ), 0, new DecimalValue( '+1.2' ) ],
			'negative exponent' => [ new DecimalValue( '-1.2' ), -2, new DecimalValue( '-0.012' ) ],
			'positive exponent' => [ new DecimalValue( '-12' ), 3, new DecimalValue( '-12000' ) ],
		];
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
