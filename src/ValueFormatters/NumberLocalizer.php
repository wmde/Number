<?php

namespace ValueFormatters;

use InvalidArgumentException;

/**
 * Interface defining a service for localizing a string based on a language code.
 * This may for instance be used to re-format a numeric string according to
 * the rules of a given locale.
 *
 * @see ValueParsers\Unlocalizer
 *
 * @since 0.2
 *
 * @licence GNU GPL v2+
 * @author Daniel Kinzler
 */
interface NumberLocalizer {

	/**
	 * Localizes a number.
	 *
	 * @since 0.3
	 *
	 * @param string|int|float $number
	 *
	 * @return string
	 * @throws InvalidArgumentException
	 */
	public function localizeNumber( $number );
}