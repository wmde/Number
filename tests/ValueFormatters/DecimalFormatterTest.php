<?php

namespace ValueFormatters\Test;

use DataValues\DecimalValue;
use ValueFormatters\FormatterOptions;

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

		$decimals = array(
			'+0' => '0',
			'+0.0' => '0.0',
			'-0.0130' => '-0.0130',
			'+10000.013' => '10000.013',
			'-12' => '-12'
		);

		$argLists = array();
		foreach ( $decimals as $input => $expected ) {
			$inputValue = new DecimalValue( $input );

			$argLists[$input] = array( $inputValue, $expected, $options );
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

}
