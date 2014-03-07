<?php

namespace ValueParsers;

/**
 * Basic unlocalizer implementation.
 *
 * @see ValueFormatters\Localizer
 *
 * @since 0.3
 *
 * @license GPL 2+
 * @author Daniel Kinzler
 */
class BasicUnlocalizer implements Unlocalizer {

	/**
	 * Converts a localized number to canonical/internal representation.
	 *
	 * @since 0.3
	 *
	 * @param string $number string to process
	 *
	 * @return string unlocalized number, in a form suitable for floatval resp. intval.
	 */
	public function unlocalizeNumber( $number ) {
		return preg_replace( '/[^-+0-9.eExX]/', '', $number );
	}

	/**
	 * @see Unlocalizer::getNumberRegex()
	 *
	 * This implementation returns an expression that will match a number
	 * in english notation, including scientific notation.
	 *
	 * @since 0.3
	 *
	 * @param string $delim
	 *
	 * @return string
	 */
	public function getNumberRegex( $delim = '/' ) {
		return '(?:[-+]\s*)?(?:[0-9,]+\.[0-9]*|\.?[0-9]+)(?:[eE][-+]?[0-9]+)?';
	}

	/**
	 * @see Unlocalizer::getUnitRegex()
	 *
	 * This implementation returns an expression that will match any sequence
	 * of latin characters in addition to a variety of characters commonly
	 * used in unit identifiers, such as µ (mu).
	 *
	 * @since 0.3
	 *
	 * @param string $delim
	 *
	 * @return string
	 */
	public function getUnitRegex( $delim = '/' ) {
		return '[a-zA-ZµåÅöÖ' . preg_quote( '°%', $delim ) . ']+'
			. '(?:[' . preg_quote( '-.^/', $delim ) . ']?'
			. '[a-zA-Z0-9åÅöÖ' . preg_quote( '°%²³', $delim ) . ']+)*';
	}

}