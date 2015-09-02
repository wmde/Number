<?php

namespace ValueFormatters;

/**
 * Interface defining a service for formatting a quantity's unit.
 *
 * @since 0.5
 * @deprecated since 0.6
 *
 * @licence GNU GPL v2+
 * @author Daniel Kinzler
 */
interface QuantityUnitFormatter {

	/**
	 * Attaches a human readable version of the unit to the given number text.
	 * The number text is expected to already be formatted, and is not modified.
	 *
	 * The unit may be placed before or after the number, with or without a separator.
	 * The unit may also be omitted in case the $unit represents "no unit".
	 *
	 * @param string $unit
	 * @param string $numberText
	 *
	 * @return string A text representing the given number with the given unit applied.
	 */
	public function applyUnit( $unit, $numberText );

}
