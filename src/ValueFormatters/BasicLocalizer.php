<?php

namespace ValueFormatters;

/**
 * Basic (dummy) localizer.
 *
 * @see ValueParsers\Unlocalizer
 *
 * @since 0.3
 *
 * @licence GNU GPL v2+
 * @author Daniel Kinzler
 */
class BasicLocalizer implements Localizer {

	/**
	 * @see Localizer::localizeNumber()
	 *
	 * Returns PHP's default representation of the given number.
	 *
	 * @since 0.3
	 *
	 * @param string|int|float $number
	 *
	 * @return string
	 */
	public function localizeNumber( $number ) {
		return "$number";
	}
}