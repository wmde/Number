<?php

namespace ValueParsers\Test;

use ValueFormatters\BasicNumberLocalizer;

/**
 * @covers ValueFormatters\BasicNumberLocalizer
 *
 * @group DataValue
 * @group DataValueExtensions
 *
 * @license GPL-2.0-or-later
 * @author Daniel Kinzler
 */
class BasicNumberLocalizerTest extends \PHPUnit_Framework_TestCase {

	public function provideLocalizeNumber() {
		return [
			[ '5', '5' ],
			[ '+3', '+3' ],
			[ '-15', '-15' ],

			[ '5.3', '5.3' ],
			[ '+3.2', '+3.2' ],
			[ '-15.77', '-15.77' ],

			[ 77, '77' ],
			[ -7.7, '-7.7' ],
		];
	}

	/**
	 * @dataProvider provideLocalizeNumber
	 */
	public function testLocalizeNumber( $localized, $expected ) {
		$localizer = new BasicNumberLocalizer();
		$localized = $localizer->localizeNumber( $localized );

		$this->assertSame( $expected, $localized );
	}

}
