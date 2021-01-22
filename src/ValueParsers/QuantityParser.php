<?php

namespace ValueParsers;

use DataValues\DecimalMath;
use DataValues\DecimalValue;
use DataValues\IllegalValueException;
use DataValues\QuantityValue;
use DataValues\UnboundedQuantityValue;

/**
 * ValueParser that parses the string representation of a quantity.
 *
 * @since 0.1
 *
 * @license GPL-2.0-or-later
 * @author Daniel Kinzler
 */
class QuantityParser extends StringValueParser {

	public const FORMAT_NAME = 'quantity';

	/**
	 * The unit of the value to parse. If this option is given, it's illegal to also specify
	 * a unit in the input string.
	 *
	 * @since 0.5
	 */
	public const OPT_UNIT = 'unit';

	/**
	 * @var DecimalParser
	 */
	private $decimalParser;

	/**
	 * @var NumberUnlocalizer
	 */
	private $unlocalizer;

	/**
	 * @since 0.1
	 *
	 * @param ParserOptions|null $options
	 * @param NumberUnlocalizer|null $unlocalizer
	 */
	public function __construct( ParserOptions $options = null, NumberUnlocalizer $unlocalizer = null ) {
		parent::__construct( $options );

		$this->defaultOption( self::OPT_UNIT, null );

		$this->unlocalizer = $unlocalizer ?: new BasicNumberUnlocalizer();
		$this->decimalParser = new DecimalParser( $this->options, $this->unlocalizer );
	}

	/**
	 * @see StringValueParser::stringParse
	 *
	 * @since 0.1
	 *
	 * @param string $value
	 *
	 * @return UnboundedQuantityValue|QuantityValue
	 * @throws ParseException
	 */
	protected function stringParse( $value ) {
		list( $amount, $exactness, $margin, $unit ) = $this->splitQuantityString( $value );

		$unitOption = $this->getUnitFromOptions();

		if ( $unit === null ) {
			$unit = $unitOption !== null ? $unitOption : '1';
		} elseif ( $unitOption !== null && $unit !== $unitOption ) {
			throw new ParseException( 'Cannot specify a unit in input if a unit was fixed via options.' );
		}

		try {
			$quantity = $this->newQuantityFromParts( $amount, $exactness, $margin, $unit );
			return $quantity;
		} catch ( IllegalValueException $ex ) {
			throw new ParseException( $ex->getMessage(), $value, self::FORMAT_NAME );
		}
	}

	/**
	 * @return string|null
	 */
	private function getUnitFromOptions() {
		$unit = $this->getOption( self::OPT_UNIT );
		return $unit === null ? null : trim( $unit );
	}

	/**
	 * Constructs a QuantityValue from the given parts.
	 *
	 * @see splitQuantityString
	 *
	 * @param string $amount decimal representation of the amount
	 * @param string|null $exactness either '!' to indicate an exact value,
	 *        or '~' for "automatic", or null if $margin should be used.
	 * @param string|null $margin decimal representation of the uncertainty margin
	 * @param string $unit the unit identifier (use "1" for unitless quantities).
	 *
	 * @throws ParseException if one of the decimals could not be parsed.
	 * @throws IllegalValueException if the QuantityValue could not be constructed
	 * @return UnboundedQuantityValue|QuantityValue
	 */
	private function newQuantityFromParts( $amount, $exactness, $margin, $unit ) {
		list( $amount, $exponent ) = $this->decimalParser->splitDecimalExponent( $amount );
		$amountValue = $this->decimalParser->parse( $amount );

		if ( $exactness === '!' ) {
			// the amount is an exact number
			$amountValue = $this->decimalParser->applyDecimalExponent( $amountValue, $exponent );
			$quantity = $this->newExactQuantity( $amountValue, $unit );
		} elseif ( $margin !== null ) {
			// uncertainty margin given
			// NOTE: the pattern for scientific notation is 2e3 +/- 1e2, so the exponents are treated separately.
			$marginValue = $this->decimalParser->parse( $margin );
			$amountValue = $this->decimalParser->applyDecimalExponent( $amountValue, $exponent );
			$quantity = $this->newUncertainQuantityFromMargin( $amountValue, $marginValue, $unit );
		} elseif ( $exactness === '~' ) {
			// derive uncertainty from given decimals
			// NOTE: with scientific notation, the exponent applies to the uncertainty bounds, too
			$quantity = $this->newUncertainQuantityFromDigits( $amountValue, $unit, $exponent );
		} else {
			$amountValue = $this->decimalParser->applyDecimalExponent( $amountValue, $exponent );
			return new UnboundedQuantityValue( $amountValue, $unit );
		}

		return $quantity;
	}

	/**
	 * Splits a quantity string into its syntactic parts.
	 *
	 * @see newQuantityFromParts
	 *
	 * @param string $value
	 *
	 * @throws ParseException If $value does not match the expected pattern
	 * @return array list( $amount, $exactness, $margin, $unit ).
	 *         Parts not present in $value will be null
	 */
	private function splitQuantityString( $value ) {
		//TODO: allow explicitly specifying the number of significant figures
		//TODO: allow explicitly specifying the uncertainty interval

		$numberPattern = $this->unlocalizer->getNumberRegex( '@' );
		$unitPattern = $this->unlocalizer->getUnitRegex( '@' );

		$pattern = '@^'
			. '\s*(' . $numberPattern . ')' // $1: amount
			. '\s*(?:'
				. '([~!])'  // $2: '!' for "exact", '~' for "approx", or nothing
				. '|(?:\+/?-|±)\s*(' . $numberPattern . ')' // $3: plus/minus offset (uncertainty margin)
				. '|' // or nothing
			. ')'
			. '\s*(' . $unitPattern . ')?' // $4: unit
			. '\s*$@u';

		if ( !preg_match( $pattern, $value, $groups ) ) {
			throw new ParseException( 'Malformed quantity', $value, self::FORMAT_NAME );
		}

		// Remove $0.
		array_shift( $groups );

		array_walk( $groups, function ( &$element ) {
			if ( $element === '' ) {
				$element = null;
			}
		} );

		return array_pad( $groups, 4, null );
	}

	/**
	 * Returns a QuantityValue representing the given amount.
	 * The amount is assumed to be absolutely exact, that is,
	 * the upper and lower bound will be the same as the amount.
	 *
	 * @param DecimalValue $amount
	 * @param string $unit The quantity's unit (use "1" for unit-less quantities)
	 *
	 * @return QuantityValue
	 */
	private function newExactQuantity( DecimalValue $amount, $unit = '1' ) {
		return new QuantityValue( $amount, $unit, $amount, $amount );
	}

	/**
	 * Returns a QuantityValue representing the given amount, automatically assuming
	 * a level of uncertainty based on the digits given.
	 *
	 * The upper and lower bounds are determined automatically from the given
	 * digits by increasing resp. decreasing the least significant digit.
	 * E.g. "+0.01" would have upperBound "+0.02" and lowerBound "+0.01",
	 * while "-100" would have upperBound "-99" and lowerBound "-101".
	 *
	 * @param DecimalValue $amount The quantity
	 * @param string $unit The quantity's unit (use "1" for unit-less quantities)
	 * @param DecimalValue $margin
	 *
	 * @return QuantityValue
	 */
	private function newUncertainQuantityFromMargin( DecimalValue $amount, DecimalValue $margin, $unit = '1' ) {
		$decimalMath = new DecimalMath();
		$margin = $margin->computeAbsolute();

		$lowerBound = $decimalMath->sum( $amount, $margin->computeComplement() );
		$upperBound = $decimalMath->sum( $amount, $margin );

		return new QuantityValue( $amount, $unit, $upperBound, $lowerBound );
	}

	/**
	 * Returns a QuantityValue representing the given amount, automatically assuming
	 * a level of uncertainty based on the digits given.
	 *
	 * The upper and lower bounds are determined automatically from the given
	 * digits by adding/subtracting half the order of magnitude of the least
	 * significant digit. Trailing zeros before the decimal point are considered
	 * significant.
	 *
	 * E.g. "+0.01" would have upperBound "+0.015" and lowerBound "+0.005",
	 * while "-100" would have upperBound "-99.5" and lowerBound "-100.5".
	 *
	 * @param DecimalValue $amount The quantity
	 * @param string $unit The quantity's unit (use "1" for unit-less quantities)
	 * @param int $exponent Decimal exponent to apply
	 *
	 * @return QuantityValue
	 */
	private function newUncertainQuantityFromDigits( DecimalValue $amount, $unit = '1', $exponent = 0 ) {
		$math = new DecimalMath();

		// Add/subtract one from least significant digit
		$high = $math->bump( $amount );
		$low = $math->slump( $amount );

		// Compute margin = abs( high - low ) / 4.
		$highLow = $math->sum( $high, $low->computeComplement() )->computeAbsolute();
		$margin = $math->product( $highLow, new DecimalValue( '0.25' ) );

		// Bounds = amount +/- margin
		$upperBound = $math->sum( $amount, $margin )->getTrimmed();
		$lowerBound = $math->sum( $amount, $margin->computeComplement() )->getTrimmed();

		// Apply exponent
		$amount = $this->decimalParser->applyDecimalExponent( $amount, $exponent );
		$lowerBound = $this->decimalParser->applyDecimalExponent( $lowerBound, $exponent );
		$upperBound = $this->decimalParser->applyDecimalExponent( $upperBound, $exponent );

		return new QuantityValue( $amount, $unit, $upperBound, $lowerBound );
	}

}
