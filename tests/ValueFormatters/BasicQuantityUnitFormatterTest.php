<?php

namespace ValueParsers\Test;

use ValueFormatters\BasicQuantityUnitFormatter;

/**
 * @covers ValueFormatters\BasicQuantityUnitFormatter
 *
 * @group DataValue
 * @group DataValueExtensions
 *
 * @licence GNU GPL v2+
 * @author Daniel Kinzler
 */
class BasicQuantityUnitFormatterTest extends \PHPUnit_Framework_TestCase {

	public function provideApplyUnit() {
		return array(
			array( '', '5', '5' ),
			array( '1', '+3.7', '+3.7' ),
			array( 'µ', 'seventeen', 'seventeenµ' ),
		);
	}

	/**
	 * @dataProvider provideApplyUnit
	 */
	public function testApplyUnit( $unit, $number, $expected ) {
		$formatter = new BasicQuantityUnitFormatter();
		$formatted = $formatter->applyUnit( $unit, $number );

		$this->assertEquals( $expected, $formatted );
	}

}
