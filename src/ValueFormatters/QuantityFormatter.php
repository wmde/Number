<?php

namespace ValueFormatters;

use DataValues\DecimalMath;
use DataValues\DecimalValue;
use DataValues\QuantityValue;
use InvalidArgumentException;

/**
 * Formatter for quantity values
 *
 * @since 0.1
 *
 * @licence GNU GPL v2+
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
	 * @var QuantityUnitFormatter
	 */
	private $unitFormatter;

	/**
	 * @param DecimalFormatter|null $decimalFormatter
	 * @param QuantityUnitFormatter|null $unitFormatter
	 * @param FormatterOptions|null $options
	 */
	public function __construct(
		DecimalFormatter $decimalFormatter = null,
		QuantityUnitFormatter $unitFormatter = null,
		FormatterOptions $options = null
	) {
		parent::__construct( $options );

		$this->defaultOption( self::OPT_SHOW_UNCERTAINTY_MARGIN, true );
		$this->defaultOption( self::OPT_APPLY_ROUNDING, true );
		$this->defaultOption( self::OPT_APPLY_UNIT, true );

		$this->decimalFormatter = $decimalFormatter ?: new DecimalFormatter( $this->options );
		$this->unitFormatter = $unitFormatter ?: new BasicQuantityUnitFormatter();

		// plain composition should be sufficient
		$this->decimalMath = new DecimalMath();
	}

	/**
	 * @see ValueFormatter::format
	 *
	 * @since 0.1
	 *
	 * @param QuantityValue $value
	 *
	 * @throws InvalidArgumentException
	 * @return string Text
	 */
	public function format( $value ) {
		if ( !( $value instanceof QuantityValue ) ) {
			throw new InvalidArgumentException( 'Data value type mismatch. Expected a QuantityValue.' );
		}

		return $this->formatQuantityValue( $value );
	}

	/**
	 * @param QuantityValue $quantity
	 *
	 * @return string Text
	 */
	private function formatQuantityValue( QuantityValue $quantity ) {
		$roundingExponent = $this->getRoundingExponent( $quantity );

		$amount = $quantity->getAmount();
		$roundedAmount = $this->decimalMath->roundToExponent( $amount, $roundingExponent );
		$formatted = $this->decimalFormatter->format( $roundedAmount );

		$margin = $this->formatMargin( $quantity->getUncertaintyMargin(), $roundingExponent );
		if ( $margin !== null ) {
			// TODO: use localizable pattern for constructing the output.
			$formatted .= '±' . $margin;
		}

		$unit = $quantity->getUnit();
		if ( $this->options->getOption( self::OPT_APPLY_UNIT ) && $unit !== '1' && $unit !== '' ) {
			$formatted = $this->unitFormatter->applyUnit( $unit, $formatted );
		}

		return $formatted;
	}

	/**
	 * Returns the rounding exponent based on the given $quantity
	 * and the @see QuantityFormatter::OPT_APPLY_ROUNDING option.
	 *
	 * @param QuantityValue $quantity
	 *
	 * @return int
	 */
	private function getRoundingExponent( QuantityValue $quantity ) {
		if ( $this->options->getOption( self::OPT_APPLY_ROUNDING ) === true ) {
			// round to the order of uncertainty
			return $quantity->getOrderOfUncertainty();
		} elseif ( $this->options->getOption( self::OPT_APPLY_ROUNDING ) === false ) {
			// to keep all digits, use the negative length of the fractional part
			return -strlen( $quantity->getAmount()->getFractionalPart() );
		} else {
			return (int)$this->options->getOption( self::OPT_APPLY_ROUNDING );
		}
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

}
