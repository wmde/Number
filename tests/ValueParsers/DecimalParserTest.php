<?php

namespace ValueParsers\Test;

use DataValues\DecimalValue;

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

}
