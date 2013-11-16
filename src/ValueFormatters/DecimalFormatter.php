<?php

namespace ValueFormatters;

use DataValues\DecimalValue;
use InvalidArgumentException;

/**
 * Formatter for decimal values
 *
 * @since 0.1
 *
 * @licence GNU GPL v2+
 * @author Daniel Kinzler
 */
class DecimalFormatter extends ValueFormatterBase {

	/**
	 * Formats a QuantityValue data value
	 *
	 * @since 0.1
	 *
	 * @param mixed $dataValue value to format
	 *
	 * @return string
	 * @throws InvalidArgumentException
	 */
	public function format( $dataValue ) {
		if ( !( $dataValue instanceof DecimalValue ) ) {
			throw new InvalidArgumentException( 'DataValue is not a DecimalValue.' );
		}

		// TODO: Implement localization of decimal numbers!
		$decimal = $dataValue->getValue();

		// strip leading +
		$decimal = ltrim( $decimal, '+' );

		return $decimal;
	}

}
