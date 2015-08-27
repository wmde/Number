<?php

namespace ValueParsers\Test;

use DataValues\MatrixValue;
use DataValues\NumberValue;
use ValueParsers\ParserOptions;
use ValueParsers\MatrixParser;
use ValueParsers\ValueParser;

/**
 * @covers ValueParsers\MatrixParser
 *
 * @group DataValue
 * @group DataValueExtensions
 *
 * @licence GNU GPL v2+
 * @author Andrius Merkys
 */
class MatrixParserTest extends StringValueParserTest {

	/**
	 * @deprecated since 0.3, just use getInstance.
	 */
	protected function getParserClass() {
		throw new \LogicException( 'Should not be called, use getInstance' );
	}

	/**
	 * @see ValueParserTestBase::getInstance
	 *
	 * @return MatrixParser
	 */
	protected function getInstance() {
		return new MatrixParser();
	}

	/**
	 * @see ValueParserTestBase::validInputProvider
	 */
	public function validInputProvider() {
		$valid = array(
			// amounts in various styles and forms
			'[[1,3,5]]' => new MatrixValue( array( array( new NumberValue( 1.0 ),
                                                          new NumberValue( 3.0 ),
                                                          new NumberValue( 5.0 ) ) ) ),
		);

		$argLists = array();

		foreach ( $valid as $string => $expected ) {
			$argLists[] = array( $string, $expected );
		}

		return $argLists;
	}

	/**
	 * @see ValueParserTestBase::invalidInputProvider
	 */
	public function invalidInputProvider() {
		$argLists = parent::invalidInputProvider();

		$invalid = array(
			'[]',
		);

		foreach ( $invalid as $value ) {
			$argLists[] = array( $value );
		}

		return $argLists;
	}

/*
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

		$parser = new MatrixParser( $options, $unlocalizer );

		$quantity = $parser->parse( '1 22 333,77+-3a~b' );

		$this->assertEquals( '122333.77', $quantity->getAmount() );
		$this->assertEquals( 'a~b', $quantity->getUnit() );
	}
*/
}
