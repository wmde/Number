<?php

namespace ValueParsers;

/**
 * @see \ValueFormatters\BasicNumberLocalizer
 *
 * @since 0.3
 *
 * @license GPL-2.0-or-later
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
		return preg_replace( '/[^-+\d.EX]+/i', '', $number );
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
		return '(?:[-+]\s*)?(?:[\d,]+\.\d*|\.?\d+)(?:[eE][-+]?\d+)?';
	}

	/**
	 * @see NumberUnlocalizer::getUnitRegex
	 *
	 * @param string $delimiter Unused.
	 *
	 * @return string An empty string.
	 */
	public function getUnitRegex( $delimiter = '/' ) {
		// TODO: Discuss and specify what QuantityParser should accept.
		return '';
	}

}
