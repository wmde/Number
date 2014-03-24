<?php

namespace ValueParsers\Test;

use DataValues\DecimalValue;
use ValueParsers\DecimalParser;
use ValueParsers\ParserOptions;
use ValueParsers\ValueParser;

/**
 * @covers ValueParsers\DecimalParser
 *
 * @since 0.1
 *
 * @group DataValue
 * @group DataValueExtensions
 *
 * @licence GNU GPL v2+
 * @author Daniel Kinzler
 */
class DecimalParserTest extends StringValueParserTest {

	/**
	 * @see ValueParserTestBase::validInputProvider
	 *
	 * @since 0.1
	 *
	 * @return array
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

	/**
	 * @see ValueParserTestBase::getParserClass
	 * @since 0.1
	 * @return string
	 */
	protected function getParserClass() {
		return 'ValueParsers\DecimalParser';
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

		$options = new ParserOptions();
		$parser = new DecimalParser( $options, $unlocalizer );

		$input = '###20#000#000###';
		$value = $parser->parse( $input );

		$this->assertEquals( '20000000', $value->getValue() );
	}

}
