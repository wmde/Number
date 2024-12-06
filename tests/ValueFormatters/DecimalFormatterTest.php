<?php

namespace ValueFormatters\Test;

use DataValues\DecimalValue;
use PHPUnit\Framework\TestCase;
use ValueFormatters\DecimalFormatter;
use ValueFormatters\FormatterOptions;
use ValueFormatters\NumberLocalizer;

/**
 * @covers ValueFormatters\DecimalFormatter
 *
 * @group ValueFormatters
 * @group DataValueExtensions
 *
 * @license GPL-2.0-or-later
 * @author Daniel Kinzler
 */
class DecimalFormatterTest extends TestCase {

	/**
	 * @see ValueFormatterTestBase::getInstance
	 *
	 * @param FormatterOptions|null $options
	 *
	 * @return DecimalFormatter
	 */
	protected function getInstance( ?FormatterOptions $options = null ) {
		return new DecimalFormatter( $options );
	}

	/**
	 * @see ValueFormatterTestBase::validProvider
	 */
	public function validProvider() {
		$optionsForceSign = new FormatterOptions( [
			DecimalFormatter::OPT_FORCE_SIGN => true
		] );

		$decimals = [
			'+0' => [ '0', null ],
			'+0.0' => [ '0.0', null ],
			'-0.0130' => [ '-0.0130', null ],
			'+10000.013' => [ '10000.013', null ],
			'+20000.4' => [ '+20000.4', $optionsForceSign ],
			'-12' => [ '-12', null ]
		];

		$argLists = [];
		foreach ( $decimals as $input => $args ) {
			$inputValue = new DecimalValue( $input );

			$argLists[$input] = array_merge( [ $inputValue ], $args );
		}

		return $argLists;
	}

	public function testLocalization() {
		$localizer = $this->createMock( NumberLocalizer::class );

		$localizer->expects( $this->once() )
			->method( 'localizeNumber' )
			->willReturnCallback( static function ( $number ) {
				return "n:$number";
			} );

		$value = new DecimalValue( '+12345' );
		$formatter = new DecimalFormatter( null, $localizer );

		$this->assertSame( 'n:12345', $formatter->format( $value ) );
	}

}
