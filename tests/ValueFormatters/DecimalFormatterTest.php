<?php

namespace ValueFormatters\Test;

use DataValues\DecimalValue;
use ValueFormatters\DecimalFormatter;
use ValueFormatters\FormatterOptions;
use ValueFormatters\ValueFormatter;
use Wikibase\Lib\Serializers\SerializationOptions;

/**
 * @covers ValueFormatters\DecimalFormatter
 *
 * @since 0.1
 *
 * @group ValueFormatters
 * @group DataValueExtensions
 *
 * @licence GNU GPL v2+
 * @author Daniel Kinzler
 */
class DecimalFormatterTest extends ValueFormatterTestBase {

	/**
	 * @see ValueFormatterTestBase::validProvider
	 *
	 * @since 0.1
	 *
	 * @return array
	 */
	public function validProvider() {
		$options = new FormatterOptions();

		$optionsForceSign = new FormatterOptions( array(
			DecimalFormatter::OPT_FORCE_SIGN => true
		) );

		$decimals = array(
			'+0' => array( '0', $options ),
			'+0.0' => array( '0.0', $options ),
			'-0.0130' => array( '-0.0130', $options ),
			'+10000.013' => array( '10000.013', $options ),
			'+20000.4' => array( '+20000.4', $optionsForceSign ),
			'-12' => array( '-12', $options )
		);

		$argLists = array();
		foreach ( $decimals as $input => $args ) {
			$inputValue = new DecimalValue( $input );

			$argLists[$input] = array_merge( array( $inputValue ), $args );
		}

		return $argLists;
	}

	/**
	 * @see ValueFormatterTestBase::getFormatterClass
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	protected function getFormatterClass() {
		return 'ValueFormatters\DecimalFormatter';
	}

	public function testLocalization() {
		$localizer = $this->getMock( 'ValueFormatters\NumberLocalizer' );

		$localizer->expects( $this->once() )
			->method( 'localizeNumber' )
			->will( $this->returnCallback( function ( $number ) {
				return "n:$number";
			} ) );

		$options = new FormatterOptions( );
		$value = new DecimalValue( '+12345' );
		$formatter = new DecimalFormatter( $options, $localizer );

		$this->assertEquals( 'n:12345', $formatter->format( $value ) );
	}

}
