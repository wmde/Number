<?php

namespace ValueFormatters;

use DataValues\DecimalMath;
use DataValues\DecimalValue;
use DataValues\QuantityValue;
use DataValues\UnboundedQuantityValue;
use InvalidArgumentException;

/**
 * Plain text formatter for quantity values.
 *
 * @since 0.1
 *
 * @license GPL-2.0+
 * @author Daniel Kinzler
 * @author Thiemo Mättig
 */
class QuantityFormatter extends ValueFormatterBase {

	/**
	 * Option key for enabling or disabling output of the uncertainty margin (e.g. "+/-5").
	 * Per default, the uncertainty margin is included in the output.
	 * This option must have a boolean value.
	 */
	const OPT_SHOW_UNCERTAINTY_MARGIN = 'showQuantityUncertaintyMargin';

	/**
	 * Option key for determining what level of rounding to apply to the numbers
	 * included in the output. The value of this option must be an integer or a boolean.
	 *
	 * If an integer is given, this is the exponent of the last significant decimal digits
	 * - that is, -2 would round to two digits after the decimal point, and 1 would round
	 * to two digits before the decimal point. 0 would indicate rounding to integers.
	 *
	 * If the value is a boolean, false means no rounding at all (useful e.g. in diffs),
	 * and true means automatic rounding based on what $quantity->getOrderOfUncertainty()
	 * returns.
	 */
	const OPT_APPLY_ROUNDING = 'applyRounding';

	/**
	 * Option key controlling whether the quantity's unit of measurement should be included
	 * in the output.
	 *
	 * @since 0.5
	 */
	const OPT_APPLY_UNIT = 'applyUnit';

	/**
	 * @var DecimalMath
	 */
	private $decimalMath;

	/**
	 * @var DecimalFormatter
	 */
	private $decimalFormatter;

	/**
	 * @var ValueFormatter|null
	 */
	private $vocabularyUriFormatter;

	/**
	 * @var string
	 */
	private $quantityWithUnitFormat;

	/**
	 * @since 0.6
	 *
	 * @param FormatterOptions|null $options
	 * @param DecimalFormatter|null $decimalFormatter
	 * @param ValueFormatter|null $vocabularyUriFormatter
	 * @param string|null $quantityWithUnitFormat Format string with two placeholders, $1 for the
	 * number and $2 for the unit. Warning, this must be under the control of the application, not
	 * under the control of the user, because it allows HTML injections in subclasses that return
	 * HTML.
	 */
	public function __construct(
		FormatterOptions $options = null,
		DecimalFormatter $decimalFormatter = null,
		ValueFormatter $vocabularyUriFormatter = null,
		$quantityWithUnitFormat = null
	) {
		parent::__construct( $options );

		$this->defaultOption( self::OPT_SHOW_UNCERTAINTY_MARGIN, true );
		$this->defaultOption( self::OPT_APPLY_ROUNDING, true );
		$this->defaultOption( self::OPT_APPLY_UNIT, true );

		$this->decimalFormatter = $decimalFormatter ?: new DecimalFormatter( $this->options );
		$this->vocabularyUriFormatter = $vocabularyUriFormatter;
		$this->quantityWithUnitFormat = $quantityWithUnitFormat ?: '$1 $2';

		// plain composition should be sufficient
		$this->decimalMath = new DecimalMath();
	}

	/**
	 * @since 0.6
	 *
	 * @return string
	 */
	final protected function getQuantityWithUnitFormat() {
		return $this->quantityWithUnitFormat;
	}

	/**
	 * @see ValueFormatter::format
	 *
	 * @param UnboundedQuantityValue $value
	 *
	 * @throws InvalidArgumentException
	 * @return string Text
	 */
	public function format( $value ) {
		if ( !( $value instanceof UnboundedQuantityValue ) ) {
			throw new InvalidArgumentException( 'Data value type mismatch. Expected a QuantityValue.' );
		}

		return $this->formatQuantityValue( $value );
	}

	/**
	 * @since 0.6
	 *
	 * @param UnboundedQuantityValue $quantity
	 *
	 * @return string Text
	 */
	protected function formatQuantityValue( UnboundedQuantityValue $quantity ) {
		$formatted = $this->formatNumber( $quantity );
		$unit = $this->formatUnit( $quantity->getUnit() );

		if ( $unit !== null ) {
			$formatted = strtr(
				$this->quantityWithUnitFormat,
				array(
					'$1' => $formatted,
					'$2' => $unit
				)
			);
		}

		return $formatted;
	}

	/**
	 * @since 0.6
	 *
	 * @param UnboundedQuantityValue $quantity
	 *
	 * @return string Text
	 */
	protected function formatNumber( UnboundedQuantityValue $quantity ) {
		$roundingExponent = $this->getRoundingExponent( $quantity );

		$amount = $quantity->getAmount();

		if ( $quantity instanceof QuantityValue ) {
			if ( $roundingExponent === null ) {
				$formatted = $this->formatMinimalDecimal( $amount );
				$margin = $quantity->getUncertaintyMargin();
				$margin = $margin->isZero() ? null : $this->formatMinimalDecimal( $margin );
			} else {
				$roundedAmount = $this->decimalMath->roundToExponent( $amount, $roundingExponent );
				$formatted = $this->decimalFormatter->format( $roundedAmount );
				$margin = $this->formatMargin( $quantity->getUncertaintyMargin(), $roundingExponent );
			}

			if ( $margin !== null ) {
				// TODO: use localizable pattern for constructing the output.
				$formatted .= '±' . $margin;
			}
		} else {
			$formatted = $this->decimalFormatter->format( $amount );
		}

		return $formatted;
	}

	/**
	 * Returns the rounding exponent based on the given $quantity
	 * and the @see QuantityFormatter::OPT_APPLY_ROUNDING option.
	 *
	 * @param QuantityValue $quantity
	 *
	 * @return int|null
	 */
	private function getRoundingExponent( UnboundedQuantityValue $quantity ) {
		if ( $this->options->getOption( self::OPT_APPLY_ROUNDING ) === true ) {
			return $this->options->getOption( self::OPT_SHOW_UNCERTAINTY_MARGIN )
					|| !( $quantity instanceof QuantityValue )
				? null
				// round to the order of uncertainty
				: $quantity->getOrderOfUncertainty();
		} elseif ( $this->options->getOption( self::OPT_APPLY_ROUNDING ) === false ) {
			return null;
		} else {
			return (int)$this->options->getOption( self::OPT_APPLY_ROUNDING );
		}
	}

	/**
	 * @param DecimalValue $decimal
	 *
	 * @return string
	 */
	private function formatMinimalDecimal( DecimalValue $decimal ) {
		// TODO: This should be an option of DecimalFormatter.
		return preg_replace( '/(\.\d+?)0+$/', '$1',
			preg_replace( '/(?<=\d)\.0*$/', '', $this->decimalFormatter->format( $decimal ) )
		);
	}

	/**
	 * @param DecimalValue $margin
	 * @param int $roundingExponent
	 *
	 * @return string|null Text
	 */
	private function formatMargin( DecimalValue $margin, $roundingExponent ) {
		if ( $this->options->getOption( self::OPT_SHOW_UNCERTAINTY_MARGIN ) ) {
			// TODO: never round to 0! See bug #56892
			$roundedMargin = $this->decimalMath->roundToExponent( $margin, $roundingExponent );

			if ( !$roundedMargin->isZero() ) {
				return $this->decimalFormatter->format( $roundedMargin );
			}
		}

		return null;
	}

	/**
	 * @since 0.6
	 *
	 * @param string $unit URI
	 *
	 * @return string|null Text
	 */
	protected function formatUnit( $unit ) {
		if ( $this->vocabularyUriFormatter === null
			|| !$this->options->getOption( self::OPT_APPLY_UNIT )
			|| $unit === ''
			|| $unit === '1'
		) {
			return null;
		}

		return $this->vocabularyUriFormatter->format( $unit );
	}

}
