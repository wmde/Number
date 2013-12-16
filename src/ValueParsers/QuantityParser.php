<?php

namespace ValueParsers;

use DataValues\DecimalMath;
use DataValues\DecimalValue;
use DataValues\IllegalValueException;
use DataValues\QuantityValue;

/**
 * ValueParser that parses the string representation of a quantity.
 *
 * @since 0.1
 *
 * @licence GNU GPL v2+
 * @author Daniel Kinzler
 */
class QuantityParser extends StringValueParser {

	const NUMBER_PATTERN = '(?:[-+]\s*)?(?:[0-9,\'`]+\.[0-9,\'`]*|\.?[0-9,\'`]+)(?:[eE][-+]?[0-9,\'`]+)?';

	const UNIT_PATTERN = '[a-zA-ZµåÅöÖ°%][-.a-zA-Z0-9åÅöÖ°%²³^]*';

	/**
	 * @var DecimalParser
	 */
	protected $decimalParser;

	/**
	 * @since 0.1
	 *
	 * @param DecimalParser $decimalParser
	 * @param ParserOptions|null $options
	 */
	public function __construct( DecimalParser $decimalParser, ParserOptions $options = null ) {
		parent::__construct( $options );

		$this->decimalParser = $decimalParser;
	}

	/**
	 * @see StringValueParser::stringParse
	 *
	 * @since 0.1
	 *
	 * @param string $value
	 *
	 * @return QuantityValue
	 * @throws ParseException
	 */
	protected function stringParse( $value ) {
		list( $amount, $exactness, $margin, $unit ) = $this->splitQuantityString( $value );

		if ( $unit === null ) {
			$unit = '1';
		}

		try {
			$quantity = $this->newQuantityFromParts( $amount, $exactness, $margin, $unit );
			return $quantity;
		} catch ( IllegalValueException $ex ) {
			throw new ParseException( $ex->getMessage() );
		}
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
	 * @return QuantityValue
	 */
	private function newQuantityFromParts( $amount, $exactness, $margin, $unit ) {
		$amountValue = $this->decimalParser->parse( $amount );

		if ( $exactness === '!' ) {
			// the amount is an exact number
			$quantity = $this->newExactQuantity( $amountValue, $unit );
		} elseif ( $margin !== null ) {
			// uncertainty margin given
			$marginValue = $this->decimalParser->parse( $margin );
			$quantity = $this->newUncertainQuantityFromMargin( $amountValue, $unit, $marginValue );
		} else {
			// derive uncertainty from given decimals
			$quantity = $this->newUncertainQuantityFromDigits( $amountValue, $unit );
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
	 * @throws \InvalidArgumentException If $value is not a string
	 * @throws ParseException If $value does not match the expected pattern
	 *
	 * @return array list( $amount, $exactness, $margin, $unit ).
	 *         Parts not present in $value will be null
	 */
	private function splitQuantityString( $value ) {
		if ( !is_string( $value ) ) {
			throw new \InvalidArgumentException( '$value must be a string' );
		}

		//TODO: allow explicitly specifying the number of significant figures
		//TODO: allow explicitly specifying the uncertainty interval

		$pattern = '@^'
			. '\s*(' . self::NUMBER_PATTERN . ')' // $1: amount
			. '\s*(?:'
				. '([~!])'  // $2: '!' for "exact", '~' for "approx", or nothing
				. '|(?:\+/?-|±)\s*(' . self::NUMBER_PATTERN . ')' // $3: plus/minus offset (uncertainty margin)
				. '|' // or nothing
			. ')'
			. '\s*(' . self::UNIT_PATTERN . ')?' // $4: unit
			. '\s*$@u';

		if ( !preg_match( $pattern, $value, $groups ) ) {
			throw new ParseException( 'Malformed quantity: ' . $value );
		}

		for ( $i = 1; $i <= 4; $i++ ) {
			if ( !isset( $groups[$i] ) ) {
				$groups[$i] = null;
			} elseif ( $groups[$i] === '' ) {
				$groups[$i] = null;
			}
		}

		array_shift( $groups ); // remove $groups[0]
		return $groups;
	}

	/**
	 * Returns a QuantityValue representing the given amount.
	 * The amount is assumed to be absolutely exact, that is,
	 * the upper and lower bound will be the same as the amount.
	 *
	 * @since 0.1
	 *
	 * @param DecimalValue $amount
	 * @param string $unit The quantity's unit (use "1" for unit-less quantities)
	 *
	 * @return QuantityValue
	 */
	protected function newExactQuantity( DecimalValue $amount, $unit = '1' ) {
		$lowerBound = $amount;
		$upperBound = $amount;

		return new QuantityValue( $amount, $unit, $upperBound, $lowerBound );
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
	 * @since 0.1
	 *
	 * @param DecimalValue $amount The quantity
	 * @param string $unit The quantity's unit (use "1" for unit-less quantities)
	 * @param DecimalValue $margin
	 *
	 * @return QuantityValue
	 */
	protected function newUncertainQuantityFromMargin( DecimalValue $amount, $unit = '1', DecimalValue $margin ) {
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
	 * digits by increasing resp. decreasing the least significant digit.
	 * E.g. "+0.01" would have upperBound "+0.02" and lowerBound "+0.01",
	 * while "-100" would have upperBound "-99" and lowerBound "-101".
	 *
	 * @since 0.1
	 *
	 * @param DecimalValue $amount The quantity
	 * @param string $unit The quantity's unit (use "1" for unit-less quantities)
	 *
	 * @return QuantityValue
	 * @throws IllegalValueException
	 */
	protected function newUncertainQuantityFromDigits( DecimalValue $amount, $unit = '1' ) {
		$math = new DecimalMath();

		if ( $amount->getSign() === '+' ) {
			$upperBound = $math->bump( $amount );
			$lowerBound = $math->slump( $amount );
		} else {
			$upperBound = $math->slump( $amount );
			$lowerBound = $math->bump( $amount );
		}

		return new QuantityValue( $amount, $unit, $upperBound, $lowerBound );
	}
}
