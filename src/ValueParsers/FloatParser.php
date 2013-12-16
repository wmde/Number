<?php

namespace ValueParsers;

use DataValues\NumberValue;

/**
 * ValueParser that parses the string representation of a float.
 *
 * @since 0.1
 *
 * @file
 * @ingroup ValueParsers
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class FloatParser extends StringValueParser {

	/**
	 * @see StringValueParser::stringParse
	 *
	 * TODO: add options for different group and decimal separators.
	 *
	 * @since 0.1
	 *
	 * @param string $value
	 *
	 * @return NumberValue
	 * @throws ParseException
	 */
	protected function stringParse( $value ) {
		if ( preg_match( '/^(-)?\d+((\.|,)\d+)?$/', $value ) ) {
			return new NumberValue( (float)$value );
		}

		throw new ParseException( 'Not a float' );
	}

}
