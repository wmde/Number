<?php

namespace ValueParsers\Test;

use ValueFormatters\BasicNumberLocalizer;

/**
 * @covers ValueFormatters\BasicNumberLocalizer
 *
 * @group DataValue
 * @group DataValueExtensions
 *
 * @licence GNU GPL v2+
 * @author Daniel Kinzler
 */
class BasicNumberLocalizerTest extends \PHPUnit_Framework_TestCase {

	public function provideLocalizeNumber() {
		return array(
			array( '5', '5' ),
			array( '+3', '+3' ),
			array( '-15', '-15' ),

			array( '5.3', '5.3' ),
			array( '+3.2', '+3.2' ),
			array( '-15.77', '-15.77' ),

			array( 77, '77' ),
			array( -7.7, '-7.7' ),
		);
	}

	/**
	 * @dataProvider provideLocalizeNumber
	 */
	public function testLocalizeNumber( $localized, $expected ) {
		$localizer = new BasicNumberLocalizer();
		$localized = $localizer->localizeNumber( $localized );

		$this->assertEquals( $expected, $localized );
	}

}
