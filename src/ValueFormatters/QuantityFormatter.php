<?php

namespace ValueFormatters;

use DataValues\DecimalMath;
use DataValues\QuantityValue;
use InvalidArgumentException;

/**
 * Formatter for quantity values
 *
 * @since 0.1
 *
 * @licence GNU GPL v2+
 * @author Daniel Kinzler
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
	 */
	const OPT_APPLY_UNIT = 'applyUnit';

	/**
	 * @var DecimalMath
	 */
	protected $decimalMath;

	/**
	 * @var QuantityUnitFormatter
	 */
	private $unitFormatter;

	/**
	 * @var DecimalFormatter
	 */
	protected $decimalFormatter;

	/**
	 * @param DecimalFormatter|null $decimalFormatter
	 * @param FormatterOptions|null $options
	 * @param QuantityUnitFormatter $unitFormatter
	 */
	public function __construct( QuantityUnitFormatter $unitFormatter, DecimalFormatter $decimalFormatter = null, FormatterOptions $options = null ) {
		parent::__construct( $options );

		$this->defaultOption( self::OPT_SHOW_UNCERTAINTY_MARGIN, true );
		$this->defaultOption( self::OPT_APPLY_ROUNDING, true );
		$this->defaultOption( self::OPT_APPLY_UNIT, true );

		$this->decimalFormatter = $decimalFormatter ?: new DecimalFormatter( $this->options );
		$this->unitFormatter = $unitFormatter;

		// plain composition should be sufficient
		$this->decimalMath = new DecimalMath();
	}

	/**
	 * Returns the rounding exponent based on the given $quantity
	 * and the @see QuantityFormatter::OPT_APPLY_ROUNDING option.
	 *
	 * @param QuantityValue $quantity
	 *
	 * @return int
	 */
	protected function getRoundingExponent( QuantityValue $quantity ) {
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
	 * Formats a QuantityValue data value
	 *
	 * @since 0.1
	 *
	 * @param mixed $dataValue value to format
	 *
	 * @return string
	 * @throws InvalidArgumentException
	 */
	public function format( $dataValue ) {
		if ( !( $dataValue instanceof QuantityValue ) ) {
			throw new InvalidArgumentException( 'DataValue is not a QuantityValue.' );
		}

		$roundingExponent = $this->getRoundingExponent( $dataValue );

		$amountValue = $dataValue->getAmount();
		$amountValue = $this->decimalMath->roundToExponent( $amountValue, $roundingExponent );
		$amount = $this->decimalFormatter->format( $amountValue );

		$unit = $dataValue->getUnit();

		$margin = '';

		if ( $this->options->getOption( self::OPT_SHOW_UNCERTAINTY_MARGIN ) ) {

			// TODO: never round to 0! See bug #56892
			$marginValue = $dataValue->getUncertaintyMargin();
			$marginValue = $this->decimalMath->roundToExponent( $marginValue, $roundingExponent );

			if ( !$marginValue->isZero() ) {
				$margin = $this->decimalFormatter->format( $marginValue );
			}
		}

		$quantity = $amount;

		if ( $margin !== '' ) {
			//TODO: use localizable pattern for constructing the output.
			$quantity .= '±' . $margin;
		}

		if ( $this->options->getOption( self::OPT_APPLY_UNIT ) && $unit !== '1' && $unit !== '' ) {
			$quantity = $this->unitFormatter->applyUnit( $unit, $quantity );
		}

		return $quantity;
	}

}
