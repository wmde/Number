<?php

namespace ValueParsers\Test;

use DataValues\QuantityValue;
use DataValues\UnboundedQuantityValue;
use ValueParsers\ParserOptions;
use ValueParsers\QuantityParser;
use ValueParsers\ValueParser;

/**
 * @covers ValueParsers\QuantityParser
 *
 * @group DataValue
 * @group DataValueExtensions
 *
 * @license GPL-2.0+
 * @author Daniel Kinzler
 */
class QuantityParserTest extends StringValueParserTest {

	/**
	 * @deprecated since DataValues Common 0.3, just use getInstance.
	 */
	protected function getParserClass() {
		throw new \LogicException( 'Should not be called, use getInstance' );
	}

	/**
	 * @see ValueParserTestBase::getInstance
	 *
	 * @return QuantityParser
	 */
	protected function getInstance() {
		return $this->getQuantityParser();
	}

	/**
	 * @param ParserOptions|null $options
	 *
	 * @return QuantityParser
	 */
	private function getQuantityParser( ParserOptions $options = null ) {
		$unlocalizer = $this->getMock( 'ValueParsers\NumberUnlocalizer' );

		$unlocalizer->expects( $this->any() )
			->method( 'unlocalizeNumber' )
			->will( $this->returnArgument( 0 ) );

		// The most minimal regex that accepts all the test cases below.
		$unlocalizer->expects( $this->any() )
			->method( 'getNumberRegex' )
			->will( $this->returnValue( '[-+]? *(?:\d+\.\d*|\.?\d+)(?:e-?\d+)?' ) );

		// This minimal regex supports % and letters, optionally followed by a digit.
		$unlocalizer->expects( $this->any() )
			->method( 'getUnitRegex' )
			->will( $this->returnValue( '[\p{L}%]+[\d³]?' ) );

		return new QuantityParser( $options, $unlocalizer );
	}

	/**
	 * @see ValueParserTestBase::validInputProvider
	 */
	public function validInputProvider() {
		$amounts = array(
			// amounts in various styles and forms
			'0' => UnboundedQuantityValue::newFromNumber( 0 ),
			'-0' => UnboundedQuantityValue::newFromNumber( 0 ),
			'-00.00' => UnboundedQuantityValue::newFromNumber( '+0.00' ),
			'+00.00' => UnboundedQuantityValue::newFromNumber( '+0.00' ),
			'0001' => UnboundedQuantityValue::newFromNumber( 1 ),
			'+01' => UnboundedQuantityValue::newFromNumber( 1 ),
			'-1' => UnboundedQuantityValue::newFromNumber( -1 ),
			'+42' => UnboundedQuantityValue::newFromNumber( 42 ),
			' -  42' => UnboundedQuantityValue::newFromNumber( -42 ),
			'9001' => UnboundedQuantityValue::newFromNumber( 9001 ),
			'.5' => UnboundedQuantityValue::newFromNumber( '+0.5' ),
			'-.125' => UnboundedQuantityValue::newFromNumber( '-0.125' ),
			'3.' => UnboundedQuantityValue::newFromNumber( 3 ),
			' 3 ' => UnboundedQuantityValue::newFromNumber( 3 ),
			'2.125' => UnboundedQuantityValue::newFromNumber( '+2.125' ),
			'2.1250' => UnboundedQuantityValue::newFromNumber( '+2.1250' ),

			'1.4e-2' => UnboundedQuantityValue::newFromNumber( '+0.014' ),
			'-1.4e-2' => UnboundedQuantityValue::newFromNumber( '-0.014' ),
			'1.4e3' => UnboundedQuantityValue::newFromNumber( '+1400' ),
			'1.4e3!m' => QuantityValue::newFromNumber( '+1400', 'm', '+1400', '+1400' ),
			'1.4e3m2' => UnboundedQuantityValue::newFromNumber( '+1400', 'm2' ),
			'1.4ev' => UnboundedQuantityValue::newFromNumber( '+1.4', 'ev' ),
			'1.4e' => UnboundedQuantityValue::newFromNumber( '+1.4', 'e' ),
			'12e3e4' => UnboundedQuantityValue::newFromNumber( '+12000', 'e4' ),
			// FIXME: Add support for 12x10^3, see DecimalParser.
			'0.004e3' => UnboundedQuantityValue::newFromNumber( '+4' ),
			'0.004e-3' => UnboundedQuantityValue::newFromNumber( '+0.000004' ),
			'4000e3' => UnboundedQuantityValue::newFromNumber( '+4000000' ),
			'4000e-3' => UnboundedQuantityValue::newFromNumber( '+4.000' ),

			// precision
			'0!' => QuantityValue::newFromNumber( 0, '1', 0, 0 ),
			'10.003!' => QuantityValue::newFromNumber( '+10.003', '1', '+10.003', '+10.003' ),
			'-200!' => QuantityValue::newFromNumber( -200, '1', -200, -200 ),
			'0~' => QuantityValue::newFromNumber( 0, '1', 0.5, -0.5 ),
			'10.003~' => QuantityValue::newFromNumber( '+10.003', '1', '+10.0035', '+10.0025' ),
			'-200~' => QuantityValue::newFromNumber( -200, '1', -199.5, -200.5 ),

			// uncertainty
			'5.3 +/- 0.2' => QuantityValue::newFromNumber( '+5.3', '1', '+5.5', '+5.1' ),
			'5.3+-0.2' => QuantityValue::newFromNumber( '+5.3', '1', '+5.5', '+5.1' ),
			'5.3 ±0.2' => QuantityValue::newFromNumber( '+5.3', '1', '+5.5', '+5.1' ),

			'5.3 +/- +0.2' => QuantityValue::newFromNumber( '+5.3', '1', '+5.5', '+5.1' ),
			'5.3+-+0.2' => QuantityValue::newFromNumber( '+5.3', '1', '+5.5', '+5.1' ),

			'5.3e3 +/- 0.2e2' => QuantityValue::newFromNumber( '+5300', '1', '+5320', '+5280' ),
			'2e-2+/-1.1e-1' => QuantityValue::newFromNumber( '+0.02', '1', '+0.13', '-0.09' ),

			// negative
			'5.3 +/- -0.2' => QuantityValue::newFromNumber( '+5.3', '1', '+5.5', '+5.1' ),
			'5.3+--0.2' => QuantityValue::newFromNumber( '+5.3', '1', '+5.5', '+5.1' ),
			'5.3 ±-0.2' => QuantityValue::newFromNumber( '+5.3', '1', '+5.5', '+5.1' ),

			// units
			'5.3+-0.2cm' => QuantityValue::newFromNumber( '+5.3', 'cm', '+5.5', '+5.1' ),
			'10.003! km' => QuantityValue::newFromNumber( '+10.003', 'km', '+10.003', '+10.003' ),
			'-200~ %  ' => QuantityValue::newFromNumber( -200, '%', -199.5, -200.5 ),
			'100003 m³' => UnboundedQuantityValue::newFromNumber( 100003, 'm³' ),
			'3.±-0.2µ' => QuantityValue::newFromNumber( '+3', 'µ', '+3.2', '+2.8' ),
			'+00.20 Å' => UnboundedQuantityValue::newFromNumber( '+0.20', 'Å' ),
		);

		$argLists = array();

		foreach ( $amounts as $amount => $expected ) {
			//NOTE: PHP may "helpfully" have converted $amount to an integer. Yay.
			$argLists[$amount] = array( strval( $amount ), $expected );
		}

		return $argLists;
	}

	/**
	 * @see StringValueParserTest::invalidInputProvider
	 */
	public function invalidInputProvider() {
		$argLists = parent::invalidInputProvider();

		$invalid = array(
			'foo',
			'',
			'.',
			'+.',
			'-.',
			'--1',
			'++1',
			'1-',
			'one',
			//'0x20', // this is actually valid, "x20" is read as the unit.
			'1+1',
			'1-1',
			'1.2.3',

			',3,',
			'10,000',
			'10\'000',

			'2!!',
			'!2',
			'2!2',

			'2!~',
			'2~!',
			'2~~',
			'~2',
			'2~2',

			'2 -- 2',
			'2++2',
			'2+±2',
			'2-±2',

			'2()',
			'2*',
			'2x y',
			'x 2 y',

			'100 003',
			'1 . 0',
		);

		foreach ( $invalid as $value ) {
			$argLists[] = array( $value );
		}

		return $argLists;
	}

	public function testParseLocalizedQuantity() {
		$options = new ParserOptions();
		$options->setOption( ValueParser::OPT_LANG, 'test' );

		$unlocalizer = $this->getMock( 'ValueParsers\NumberUnlocalizer' );

		$charmap = array(
			' ' => '',
			',' => '.',
		);

		$unlocalizer->expects( $this->any() )
			->method( 'unlocalizeNumber' )
			->will( $this->returnCallback(
				function( $number ) use ( $charmap ) {
					return str_replace( array_keys( $charmap ), array_values( $charmap ), $number );
				}
			) );

		$unlocalizer->expects( $this->any() )
			->method( 'getNumberRegex' )
			->will( $this->returnValue( '[\d ]+(?:,\d+)?' ) );

		$unlocalizer->expects( $this->any() )
			->method( 'getUnitRegex' )
			->will( $this->returnValue( '[a-z~]+' ) );

		$parser = new QuantityParser( $options, $unlocalizer );

		/** @var QuantityValue $quantity */
		$quantity = $parser->parse( '1 22 333,77+-3a~b' );

		$this->assertSame( '+122333.77', $quantity->getAmount()->getValue() );
		$this->assertSame( 'a~b', $quantity->getUnit() );
	}

	/**
	 * @dataProvider unitOptionProvider
	 */
	public function testUnitOption( $value, $unit, $expected ) {
		$options = new ParserOptions();
		$options->setOption( QuantityParser::OPT_UNIT, $unit );

		$parser = $this->getQuantityParser( $options );

		$quantity = $parser->parse( $value );
		$this->assertSame( $expected, $quantity->getUnit() );
	}

	public function unitOptionProvider() {
		return array(
			array( '17 kittens', null, 'kittens' ),
			array( '17', 'kittens', 'kittens' ),
			array( '17 kittens', 'kittens', 'kittens' ),
			array( '17m', 'm', 'm' ),
			array( ' 17 ', ' http://concept.uri ', 'http://concept.uri' ),
		);
	}

	/**
	 * @dataProvider conflictingUnitOptionProvider
	 */
	public function testConflictingUnitOption( $value, $unit ) {
		$options = new ParserOptions();
		$options->setOption( QuantityParser::OPT_UNIT, $unit );

		$parser = $this->getQuantityParser( $options );

		$this->setExpectedException( 'ValueParsers\ParseException' );
		$parser->parse( $value );
	}

	public function conflictingUnitOptionProvider() {
		return array(
			array( '17 kittens', 'm' ),
			array( '17m', 'kittens' ),
			array( '17m', '' ),
		);
	}

}
