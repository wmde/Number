<?php

namespace ValueParsers;

/**
 * @see \ValueFormatters\BasicNumberLocalizer
 *
 * @since 0.3
 *
 * @license GPL 2+
 * @author Daniel Kinzler
 */
class BasicNumberUnlocalizer implements NumberUnlocalizer {

	/**
	 * @see NumberUnlocalizer::unlocalizeNumber
	 *
	 * @param string $number string to process
	 *
	 * @return string unlocalized number, in a form suitable for floatval resp. intval.
	 */
	public function unlocalizeNumber( $number ) {
		return preg_replace( '/[^-+0-9.eExX]/', '', $number );
	}

	/**
	 * @see NumberUnlocalizer::getNumberRegex
	 *
	 * This implementation returns an expression that will match a number
	 * in english notation, including scientific notation.
	 *
	 * @param string $delimiter The regex delimiter, used for escaping.
	 *
	 * @return string regular expression
	 */
	public function getNumberRegex( $delimiter = '/' ) {
		return '(?:[-+]\s*)?(?:[0-9,]+\.[0-9]*|\.?[0-9]+)(?:[eE][-+]?[0-9]+)?';
	}

	/**
	 * @see NumberUnlocalizer::getUnitRegex
	 *
	 * This implementation returns an expression that will match any sequence
	 * of latin characters in addition to a variety of characters commonly
	 * used in unit identifiers, such as µ (mu).
	 *
	 * @param string $delimiter The regex delimiter, used for escaping.
	 *
	 * @return string regular expression
	 */
	public function getUnitRegex( $delimiter = '/' ) {
		return '[a-zA-ZµåÅöÖ' . preg_quote( '°%', $delimiter ) . ']+'
			. '(?:[' . preg_quote( '-.^/', $delimiter ) . ']?'
			. '[a-zA-Z0-9åÅöÖ' . preg_quote( '°%²³', $delimiter ) . ']+)*';
	}

}
