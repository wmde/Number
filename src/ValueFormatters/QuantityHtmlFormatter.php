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
	 * @see QuantityFormatter::formatQuantityValue
	 *
	 * @param QuantityValue $quantity
	 *
	 * @return string HTML
	 */
	protected function formatQuantityValue( QuantityValue $quantity ) {
		$html = htmlspecialchars( $this->formatNumber( $quantity ) );
		$unit = $this->formatUnit( $quantity->getUnit() );

		if ( $unit !== null ) {
			// TODO: localizable pattern for placement (before/after, separator)
			$html .= ' <span class="wb-unit">' . htmlspecialchars( $unit ) . '</span>';
		}

		return $html;
	}

}
