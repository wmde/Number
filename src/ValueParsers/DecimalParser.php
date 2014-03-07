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
 * @licence GNU GPL v2+
 * @author Daniel Kinzler
 */
class DecimalParser extends StringValueParser {

	/**
	 * @var DecimalMath
	 */
	private $math;

	/**
	 * @var null|Unlocalizer
	 */
	protected $unlocalizer;

	/**
	 * @since 0.1
	 *
	 * @param ParserOptions|null $options
	 * @param Unlocalizer|null $unlocalizer
	 */
	public function __construct( ParserOptions $options = null, Unlocalizer $unlocalizer = null ) {
		parent::__construct( $options );

		if ( !$unlocalizer ) {
			$unlocalizer = new BasicUnlocalizer();
		}

		$this->unlocalizer = $unlocalizer;
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
	 * Creates a DecimalValue from a given string.
	 *
	 * The decimal notation for the value is based on ISO 31-0, with some modifications:
	 * - the decimal separator is '.' (period). Comma is not used anywhere.
	 * - leading and trailing as well as any internal whitespace is ignored
	 * - the following characters are ignored: comma (","), apostrophe ("'").
	 * - scientific (exponential) notation is supported using the pattern /e[-+][0-9]+/
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
		$value = $this->unlocalizer->unlocalizeNumber( $value );

		//handle scientific notation
		if ( preg_match( '/^(.*)([eE]|x10\^)([-+]?[,\d]+)$/', $value, $matches ) ) {
			$exponent = $this->normalizeDecimal( $matches[3] );
			$exponent = intval( $exponent );

			$value = $matches[1];
		} else {
			$exponent = 0;
		}

		$value = $this->normalizeDecimal( $value );

		if ( $value === '' ) {
			throw new ParseException( 'Decimal value must not be empty' );
		}

		try {
			$decimal = new DecimalValue( $value );

			if ( $exponent ) {
				$math = $this->getMath();
				$decimal = $math->shift( $decimal, $exponent );
			}

			return $decimal;
		} catch ( IllegalValueException $ex ) {
			throw new ParseException( $ex->getMessage() );
		}
	}

	/**
	 * Normalize a decimal string.
	 *
	 * @param string $number
	 *
	 * @return string
	 */
	protected function normalizeDecimal( $number ) {
		// strip fluff
		$number = preg_replace( '/[ \r\n\t\'_,`]/u', '', $number );

		// strip leading zeros
		$number = preg_replace( '/^([-+]?)0+([^0]|0$)/', '$1$2', $number );

		// fix leading decimal point
		$number = preg_replace( '/^([-+]?)\./', '${1}0.', $number );

		// strip trailing decimal point
		$number = preg_replace( '/\.$/', '', $number );

		// add leading sign
		$number = preg_replace( '/^([0-9])/', '+$1', $number );

		// make "negative" zero positive
		$number = preg_replace( '/^-(0+(\.0+)?)$/', '+$1', $number );

		return $number;
	}

}
