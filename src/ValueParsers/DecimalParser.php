<?php

namespace ValueParsers;

use DataValues\DecimalMath;
use DataValues\DecimalValue;
use DataValues\IllegalValueException;

/**
 * ValueParser that parses the string representation of a decimal number.
 *
 * @since 0.1
 *
 * @license GPL-2.0-or-later
 * @author Daniel Kinzler
 */
class DecimalParser extends StringValueParser {

	public const FORMAT_NAME = 'decimal';

	/**
	 * @var DecimalMath
	 */
	private $math;

	/**
	 * @var null|NumberUnlocalizer
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

		$this->unlocalizer = $unlocalizer ?: new BasicNumberUnlocalizer();
	}

	/**
	 * @return DecimalMath
	 */
	private function getMath() {
		if ( $this->math === null ) {
			$this->math = new DecimalMath();
		}

		return $this->math;
	}

	/**
	 * Splits the exponent from the scientific notation of a decimal number.
	 *
	 * @since 0.5
	 *
	 * @example splitDecimalExponent( '1.2' ) is [ '1.2', 0 ]
	 * @example splitDecimalExponent( '1.2e3' ) is [ '1.2', 3 ]
	 * @example splitDecimalExponent( '1.2e-2' ) is [ '1.2', -2 ]
	 *
	 * @param string $valueString A decimal string, possibly using scientific notation.
	 *
	 * @return array list( $decimal, $exponent ) A pair of the decimal value without the
	 *         decimal exponent, and the decimal exponent as an integer. If $valueString
	 *         does not use scientific notation, $exponent will be 0.
	 */
	public function splitDecimalExponent( $valueString ) {
		if ( preg_match( '/^(.*)(?:[eE]|x10\^)([-+]?[\d,]+)$/', $valueString, $matches ) ) {
			$exponent = (int)str_replace( ',', '', $matches[2] );
			return [ $matches[1], $exponent ];
		}

		return [ $valueString, 0 ];
	}

	/**
	 * Applies a decimal exponent, by shifting the decimal point in the decimal string
	 * representation of the value.
	 *
	 * @since 0.5
	 *
	 * @example applyDecimalExponent( new DecimalValue( '1.2' ), 0 )  is  new DecimalValue( '1.2' )
	 * @example applyDecimalExponent( new DecimalValue( '1.2' ), 3 )  is  new DecimalValue( '1200' )
	 * @example applyDecimalExponent( new DecimalValue( '1.2' ), -2 )  is  new DecimalValue( '0.012' )
	 *
	 * @param DecimalValue $decimal
	 * @param int $exponent
	 *
	 * @return DecimalValue
	 */
	public function applyDecimalExponent( DecimalValue $decimal, $exponent ) {
		if ( $exponent !== 0 ) {
			$math = $this->getMath();
			$decimal = $math->shift( $decimal, $exponent );
		}

		return $decimal;
	}

	/**
	 * Creates a DecimalValue from a given string.
	 *
	 * The decimal notation for the value is based on ISO 31-0, with some modifications:
	 * - the decimal separator is '.' (period). Comma is not used anywhere.
	 * - leading and trailing as well as any internal whitespace is ignored
	 * - the following characters are ignored: comma (","), apostrophe ("'").
	 * - scientific (exponential) notation is supported using the pattern /e[-+]\d+/
	 * - the number may start (or end) with a decimal point.
	 * - leading zeroes are stripped, except directly before the decimal point
	 * - trailing zeroes are stripped, except directly after the decimal point
	 * - zero is always positive.
	 *
	 * @see StringValueParser::stringParse
	 *
	 * @since 0.1
	 *
	 * @param string $value
	 *
	 * @return DecimalValue
	 * @throws ParseException
	 */
	protected function stringParse( $value ) {
		$rawValue = $value;

		$value = $this->unlocalizer->unlocalizeNumber( $value );

		//handle scientific notation
		list( $value, $exponent ) = $this->splitDecimalExponent( $value );

		$value = $this->normalizeDecimal( $value );

		if ( $value === '' ) {
			throw new ParseException( 'Decimal value must not be empty', $rawValue, self::FORMAT_NAME );
		}

		try {
			$decimal = new DecimalValue( $value );
			$decimal = $this->applyDecimalExponent( $decimal, $exponent );

			return $decimal;
		} catch ( IllegalValueException $ex ) {
			throw new ParseException( $ex->getMessage(), $rawValue, self::FORMAT_NAME );
		}
	}

	/**
	 * Normalize a decimal string.
	 *
	 * @param string $number
	 *
	 * @return string
	 */
	private function normalizeDecimal( $number ) {
		// strip fluff
		$number = preg_replace( '/[\s\'_,`]+/u', '', $number );

		// strip leading zeros
		$number = preg_replace( '/^([-+]?)0+([^0]|0$)/', '$1$2', $number );

		// fix leading decimal point
		$number = preg_replace( '/^([-+]?)\./', '${1}0.', $number );

		// strip trailing decimal point
		$number = preg_replace( '/\.$/', '', $number );

		// add leading sign
		$number = preg_replace( '/^(?=\d)/', '+', $number );

		// make "negative" zero positive
		$number = preg_replace( '/^-(0+(\.0+)?)$/', '+$1', $number );

		return $number;
	}

}
