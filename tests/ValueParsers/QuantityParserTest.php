<?php

namespace ValueParsers\Test;

use DataValues\QuantityValue;
use ValueParsers\ParserOptions;
use ValueParsers\QuantityParser;
use ValueParsers\ValueParser;

/**
 * @covers ValueParsers\QuantityParser
 *
 * @group DataValue
 * @group DataValueExtensions
 *
 * @licence GNU GPL v2+
 * @author Daniel Kinzler
 */
class QuantityParserTest extends StringValueParserTest {

	/**
	 * @deprecated since 0.3, just use getInstance.
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
		return new QuantityParser();
	}

	/**
	 * @see ValueParserTestBase::validInputProvider
	 */
	public function validInputProvider() {
		$amounts = array(
			// amounts in various styles and forms
			'0' => QuantityValue::newFromNumber( 0, '1', 1, -1 ),
			'-0' => QuantityValue::newFromNumber( 0, '1', 1, -1 ),
			'-00.00' => QuantityValue::newFromNumber( '+0.00', '1', '+0.01', '-0.01' ),
			'+00.00' => QuantityValue::newFromNumber( '+0.00', '1', '+0.01', '-0.01' ),
			'0001' => QuantityValue::newFromNumber( 1, '1', 2, 0 ),
			'+01' => QuantityValue::newFromNumber( 1, '1', 2, 0 ),
			'-1' => QuantityValue::newFromNumber( -1, '1', 0, -2 ),
			'+42' => QuantityValue::newFromNumber( 42, '1', 43, 41 ),
			' -  42' => QuantityValue::newFromNumber( -42, '1', -41, -43 ),
			'9001' => QuantityValue::newFromNumber( 9001, '1', 9002, 9000 ),
			'.5' => QuantityValue::newFromNumber( '+0.5', '1', '+0.6', '+0.4' ),
			'-.125' => QuantityValue::newFromNumber( '-0.125', '1', '-0.124', '-0.126' ),
			'3.' => QuantityValue::newFromNumber( 3, '1', 4, 2 ),
			' 3 ' => QuantityValue::newFromNumber( 3, '1', 4, 2 ),
			'2.125' => QuantityValue::newFromNumber( '+2.125', '1', '+2.126', '+2.124' ),
			'2.1250' => QuantityValue::newFromNumber( '+2.1250', '1', '+2.1251', '+2.1249' ),

			'1.4e-2' => QuantityValue::newFromNumber( '+0.014', '1', '+0.015', '+0.013' ),
			'1.4e3' => QuantityValue::newFromNumber( '+1400', '1', '+1500', '+1300' ),
			'1.4e3!' => QuantityValue::newFromNumber( '+1400', '1', '+1400', '+1400' ),
			'1.5e3' => QuantityValue::newFromNumber( '+1500', '1', '+1600', '+1400' ),
			'1.4' => QuantityValue::newFromNumber( '+1.4', '1', '+1.5', '+1.3' ),
			'1.5' => QuantityValue::newFromNumber( '+1.5', '1', '+1.6', '+1.4' ),
			'12e3' => QuantityValue::newFromNumber( '+12000', '1', '+13000', '+11000' ),
			// FIXME: Add support for 12x10^3, see DecimalParser.
			'0.004e3' => QuantityValue::newFromNumber( '+4', '1', '+5', '+3' ),
			'0.004e-3' => QuantityValue::newFromNumber( '+0.000004', '1', '+0.000005', '+0.000003' ),
			'4000e3' => QuantityValue::newFromNumber( '+4000000', '1', '+4001000', '+3999000' ),
			'4000e-3' => QuantityValue::newFromNumber( '+4.000', '1', '+4.001', '+3.999' ),

			// precision
			'0!' => QuantityValue::newFromNumber( 0, '1', 0, 0 ),
			'10.003!' => QuantityValue::newFromNumber( '+10.003', '1', '+10.003', '+10.003' ),
			'-200!' => QuantityValue::newFromNumber( -200, '1', -200, -200 ),
			'0~' => QuantityValue::newFromNumber( 0, '1', 1, -1 ),
			'10.003~' => QuantityValue::newFromNumber( '+10.003', '1', '+10.004', '+10.002' ),
			'-200~' => QuantityValue::newFromNumber( -200, '1', -199, -201 ),

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
			'5.4+-0.2' => QuantityValue::newFromNumber( '+5.4', '1', '+5.6', '+5.2' ),
			'10.004!' => QuantityValue::newFromNumber( '+10.004', '1', '+10.004', '+10.004' ),
			'-300~' => QuantityValue::newFromNumber( -300, '1', -299, -301 ),
			'100003' => QuantityValue::newFromNumber( 100003, '1', 100004, 100002 ),
			'3.±-0.2' => QuantityValue::newFromNumber( '+3', '1', '+3.2', '+2.8' ),
			'+00.20' => QuantityValue::newFromNumber( '+0.20', '1', '+0.21', '+0.19' ),
		);

		$argLists = array();

		foreach ( $amounts as $amount => $expected ) {
			//NOTE: PHP may "helpfully" have converted $amount to an integer. Yay.
			$argLists[] = array( strval( $amount ), $expected );
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
			->will(  $this->returnValue( '[\d ]+(?:,\d+)?' ) );

		$unlocalizer->expects( $this->any() )
			->method( 'getUnitRegex' )
			->will( $this->returnValue( '[a-z~]+' ) );

		$parser = new QuantityParser( $options, $unlocalizer );

		$quantity = $parser->parse( '1 22 333,77+-3a~b' );

		$this->assertEquals( '122333.77', $quantity->getAmount() );
		$this->assertEquals( 'a~b', $quantity->getUnit() );
	}

	/**
	 * @dataProvider unitOptionProvider
	 */
	public function testUnitOption( $value, $unit, $expected ) {
		$options = new ParserOptions();
		$options->setOption( QuantityParser::OPT_UNIT, $unit );

		$parser = new QuantityParser( $options );

		$quantity = $parser->parse( $value );
		$this->assertEquals( $expected, $quantity->getUnit() );
	}

	public function unitOptionProvider() {
		return array(
			array( '17', null, '1' ),
			array( '17', 'kittens', 'kittens' ),
			array( '17', 'kittens', 'kittens' ),
			array( '17', 'm', 'm' ),
			array( ' 17 ', ' http://concept.uri ', 'http://concept.uri' ),
		);
	}

	/**
	 * @dataProvider conflictingUnitOptionProvider
	 */
	public function testConflictingUnitOption( $value, $unit ) {
		$options = new ParserOptions();
		$options->setOption( QuantityParser::OPT_UNIT, $unit );

		$parser = new QuantityParser( $options );

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
