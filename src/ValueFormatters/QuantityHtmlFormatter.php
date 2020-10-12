<?php

namespace ValueFormatters;

use DataValues\UnboundedQuantityValue;

/**
 * HTML formatter for quantity values.
 *
 * @since 0.6
 *
 * @license GPL-2.0-or-later
 * @author Thiemo Kreuz
 */
class QuantityHtmlFormatter extends QuantityFormatter {

	/**
	 * @see QuantityFormatter::formatNumber
	 *
	 * @param UnboundedQuantityValue $quantity
	 *
	 * @return string HTML
	 */
	protected function formatNumber( UnboundedQuantityValue $quantity ) {
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
