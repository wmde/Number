<?php

namespace ValueFormatters;

use DataValues\QuantityValue;

/**
 * HTML formatter for quantity values.
 *
 * @since 0.6
 *
 * @licence GNU GPL v2+
 * @author Thiemo Mättig
 */
class QuantityHtmlFormatter extends QuantityFormatter {

	/**
	 * @see QuantityFormatter::formatNumber
	 *
	 * @param QuantityValue $quantity
	 *
	 * @return string HTML
	 */
	protected function formatNumber( QuantityValue $quantity ) {
		$formatted = parent::formatNumber( $quantity );

		return htmlspecialchars( $formatted );
	}

	/**
	 * @see QuantityFormatter::formatUnit
	 *
	 * @param string $unit URI
	 *
	 * @return string|null HTML
	 */
	protected function formatUnit( $unit ) {
		$formatted = parent::formatUnit( $unit );

		if ( $formatted === null ) {
			return null;
		}

		return '<span class="wb-unit">' . htmlspecialchars( $formatted ) . '</span>';
	}

}
