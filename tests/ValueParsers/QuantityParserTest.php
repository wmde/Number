<?php

namespace ValueParsers\Test;

use DataValues\QuantityValue;
use ValueParsers\BasicNumberUnlocalizer;
use ValueParsers\QuantityParser;
use ValueParsers\ValueParser;

/**
 * @covers ValueParsers\QuantityParser
 *
 * @since 0.1
 *
 * @group DataValue
 * @group DataValueExtensions
 *
 * @licence GNU GPL v2+
 * @author Daniel Kinzler
 */
class QuantityParserTest extends StringValueParserTest {

	/**
	 * @see ValueParserTestBase::validInputProvider
	 *
	 * @since 0.1
	 *
	 * @return array
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
			'1.4e3' => QuantityValue::newFromNumber( '+1400', '1', '+1401', '+1399' ),
			'1.4e3!m' => QuantityValue::newFromNumber( '+1400', 'm', '+1400', '+1400' ),
			'1.4e3m2' => QuantityValue::newFromNumber( '+1400', 'm2', '+1401', '+1399' ),
			'1.4ev' => QuantityValue::newFromNumber( '+1.4', 'ev', '+1.5', '+1.3' ),
			'1.4e' => QuantityValue::newFromNumber( '+1.4', 'e', '+1.5', '+1.3' ),

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

			// negative
			'5.3 +/- -0.2' => QuantityValue::newFromNumber( '+5.3', '1', '+5.5', '+5.1' ),
			'5.3+--0.2' => QuantityValue::newFromNumber( '+5.3', '1', '+5.5', '+5.1' ),
			'5.3 ±-0.2' => QuantityValue::newFromNumber( '+5.3', '1', '+5.5', '+5.1' ),

			// units
			'5.3+-0.2cm' => QuantityValue::newFromNumber( '+5.3', 'cm', '+5.5', '+5.1' ),
			'10.003! km' => QuantityValue::newFromNumber( '+10.003', 'km', '+10.003', '+10.003' ),
			'-200~ %  ' => QuantityValue::newFromNumber( -200, '%', -199, -201 ),
			'100003 m³' => QuantityValue::newFromNumber( 100003, 'm³', 100004, 100002 ),
			'3.±-0.2µ' => QuantityValue::newFromNumber( '+3', 'µ', '+3.2', '+2.8' ),
			'+00.20 Å' => QuantityValue::newFromNumber( '+0.20', 'Å', '+0.21', '+0.19' ),
		);

		$argLists = array();

		foreach ( $amounts as $amount => $expected ) {
			//NOTE: PHP may "helpfully" have converted $amount to an integer. Yay.
			$argLists[] = array( strval( $amount ), $expected );
		}

		return $argLists;
	}

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

	/**
	 * @see ValueParserTestBase::getParserClass
	 * @since 0.1
	 * @return string
	 */
	protected function getParserClass() {
		return 'ValueParsers\QuantityParser';
	}

	/**
	 * @since 0.1
	 * @return QuantityParser
	 */
	protected function getInstance() {
		$options = $this->newParserOptions();

		$class = $this->getParserClass();
		return new $class( $options, new BasicNumberUnlocalizer() );
	}

	public function testParseLocalizedQuantity() {
		$options = $this->newParserOptions( array( ValueParser::OPT_LANG => 'test' ) );

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
			->will(  $this->returnValue( '[0-9 ]+(?:,[0-9]+)?' ) );

		$unlocalizer->expects( $this->any() )
			->method( 'getUnitRegex' )
			->will( $this->returnValue( '[a-z~]+' ) );

		$parser = new QuantityParser( $options, $unlocalizer );

		$quantity = $parser->parse( '1 22 333,77+-3a~b' );

		$this->assertEquals( '122333.77', $quantity->getAmount() );
		$this->assertEquals( 'a~b', $quantity->getUnit() );
	}

}
