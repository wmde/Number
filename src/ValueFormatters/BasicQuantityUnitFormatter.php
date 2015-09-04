<?php

namespace ValueFormatters;

/**
 * A basic QuantityUnitFormatter that just appends non-default units to the number string.
 *
 * @since 0.5
 * @deprecated since 0.6
 *
 * @licence GNU GPL v2+
 * @author Daniel Kinzler
 */
class BasicQuantityUnitFormatter implements QuantityUnitFormatter {

	/**
	 * @see QuantityUnitFormatter::applyUnit
	 *
	 * This basic implementation simply appends $unit to $numberText,
	 * unless $unit is "1" or "", in which case  $numberText is returned unmodified.
	 *
	 * @param string $unit
	 * @param string $numberText
	 *
	 * @return string A text representing the given number with the given unit applied.
	 */
	public function applyUnit( $unit, $numberText ) {
		if ( $unit === '1' || $unit === '' ) {
			return $numberText;
		}

		return $numberText . $unit;
	}

}
