<?php

namespace ValueParsers\Test;

use DataValues\DecimalValue;
use DataValues\MatrixValue;
use ValueParsers\ParserOptions;
use ValueParsers\MatrixParser;

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
			'[[1,3,5]]' => new MatrixValue( array( array( new DecimalValue( 1 ),
                                                          new DecimalValue( 3 ),
                                                          new DecimalValue( 5 ) ) ) ),
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
}
