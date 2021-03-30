<?php

namespace DataValues;

use InvalidArgumentException;

/**
 * Class for performing basic arithmetic and other transformations
 * on DecimalValues.
 *
 * This uses the bcmath library if available. Otherwise, it falls back on
 * using floating point operations.
 *
 * @note: this is not a genuine decimal arithmetics implementation,
 * and should not be used for financial computations, physical simulations, etc.
 *
 * @see DecimalValue
 *
 * @since 0.1
 *
 * @license GPL-2.0-or-later
 * @author Daniel Kinzler
 */
class DecimalMath {

	/**
	 * Whether to use the bcmath library.
	 *
	 * @var bool
	 */
	private $useBC;

	/**
	 * @param bool|null $useBC Whether to use the bcmath library. If null,
	 *        bcmath will automatically be used if available.
	 */
	public function __construct( $useBC = null ) {
		if ( $useBC === null ) {
			$useBC = function_exists( 'bcscale' );
		}

		$this->useBC = $useBC;
	}

	/**
	 * Whether this is using the bcmath library.
	 *
	 * @return bool
	 */
	public function getUseBC() {
		return $this->useBC;
	}

	/**
	 * @param DecimalValue $a
	 * @param DecimalValue $b
	 *
	 * @return DecimalValue
	 */
	public function product( DecimalValue $a, DecimalValue $b ) {
		if ( $this->useBC ) {
			return $this->productWithBC( $a, $b );
		}
		return $this->productWithoutBC( $a, $b );
	}

	/**
	 * @param DecimalValue $a
	 * @param DecimalValue $b
	 *
	 * @return DecimalValue
	 */
	private function productWithBC( DecimalValue $a, DecimalValue $b ) {
		$scale = strlen( $a->getFractionalPart() ) + strlen( $b->getFractionalPart() );
		$product = bcmul( $a->getValue(), $b->getValue(), $scale );

		$sign = $product[0] === '-' ? '' : '+';

		// (Potentially) round so that the result fits into a DecimalValue
		// Note: Product might still be to long if a*b >= 10^126
		$product = $this->roundDigits( $sign . $product, 126 );

		return new DecimalValue( $product );
	}

	/**
	 * @param DecimalValue $a
	 * @param DecimalValue $b
	 *
	 * @return DecimalValue
	 */
	private function productWithoutBC( DecimalValue $a, DecimalValue $b ) {
		$product = $a->getValueFloat() * $b->getValueFloat();

		// Add a decimal digit (.0) for consistency, if the result is a whole number,
		// but $a or $b were specified with decimal places.
		if (
			$product === floor( $product ) &&
			$a->getFractionalPart() . $b->getFractionalPart() !== ''
		) {
			$product = ( new DecimalValue( $product ) )->getValue() . '.0';
		}

		return new DecimalValue( $product );
	}

	/**
	 * @param DecimalValue $a
	 * @param DecimalValue $b
	 *
	 * @return DecimalValue
	 */
	public function sum( DecimalValue $a, DecimalValue $b ) {
		if ( $this->useBC ) {
			$scale = max( strlen( $a->getFractionalPart() ), strlen( $b->getFractionalPart() ) );
			$sum = bcadd( $a->getValue(), $b->getValue(), $scale );
		} else {
			$sum = $a->getValueFloat() + $b->getValueFloat();
		}

		return new DecimalValue( $sum );
	}

	/**
	 * @param DecimalValue $a
	 * @param DecimalValue $b
	 *
	 * @return DecimalValue
	 */
	public function min( DecimalValue $a, DecimalValue $b ) {
		if ( $this->useBC ) {
			$scale = max( strlen( $a->getFractionalPart() ), strlen( $b->getFractionalPart() ) );
			$comp = bccomp( $a->getValue(), $b->getValue(), $scale );
			$min = $comp > 0 ? $b : $a;
		} else {
			$min = min( $a->getValueFloat(), $b->getValueFloat() );
			$min = new DecimalValue( $min );
		}

		return $min;
	}

	/**
	 * @param DecimalValue $a
	 * @param DecimalValue $b
	 *
	 * @return DecimalValue
	 */
	public function max( DecimalValue $a, DecimalValue $b ) {
		if ( $this->useBC ) {
			$scale = max( strlen( $a->getFractionalPart() ), strlen( $b->getFractionalPart() ) );
			$comp = bccomp( $a->getValue(), $b->getValue(), $scale );
			$max = $comp > 0 ? $a : $b;
		} else {
			$max = max( $a->getValueFloat(), $b->getValueFloat() );
			$max = new DecimalValue( $max );
		}

		return $max;
	}

	/**
	 * Returns the given value, with any insignificant digits removed or zeroed.
	 *
	 * Rounding is applied  using the "round half away from zero" rule (that is, +0.5 is
	 * rounded to +1 and -0.5 is rounded to -1).
	 *
	 * @since 0.1
	 *
	 * @param DecimalValue $decimal
	 * @param int $significantDigits The number of digits to retain, counting the decimal point,
	 *        but not counting the leading sign.
	 *
	 * @throws InvalidArgumentException
	 * @return DecimalValue
	 */
	public function roundToDigit( DecimalValue $decimal, $significantDigits ) {
		$value = $decimal->getValue();
		$rounded = $this->roundDigits( $value, $significantDigits );
		return new DecimalValue( $rounded );
	}

	/**
	 * Returns the given value, with any insignificant digits removed or zeroed.
	 *
	 * Rounding is applied  using the "round half away from zero" rule (that is, +0.5 is
	 * rounded to +1 and -0.5 is rounded to -1).
	 *
	 * @since 0.1
	 *
	 * @param DecimalValue $decimal
	 * @param int $significantExponent 	 The exponent of the last significant digit,
	 *        e.g. -1 for "keep the first digit after the decimal point", or 2 for
	 *        "zero the last two digits before the decimal point".
	 *
	 * @throws InvalidArgumentException
	 * @return DecimalValue
	 */
	public function roundToExponent( DecimalValue $decimal, $significantExponent ) {
		//NOTE: the number of digits to keep (without the leading sign)
		//      is the same as the exponent's offset (with the leaqding sign).
		$digits = $this->getPositionForExponent( $significantExponent, $decimal );
		return $this->roundToDigit( $decimal, $digits );
	}

	/**
	 * Returns the (zero based) position for the given exponent in
	 * the given decimal string, counting the decimal point and the leading sign.
	 *
	 * @example: the position of exponent 0 in "+10.03" is 2.
	 * @example: the position of exponent 1 in "+210.03" is 2.
	 * @example: the position of exponent -2 in "+1.037" is 4.
	 *
	 * @param int $exponent
	 * @param DecimalValue $decimal
	 *
	 * @return int
	 */
	public function getPositionForExponent( $exponent, DecimalValue $decimal ) {
		$decimal = $decimal->getValue();

		$pointPos = strpos( $decimal, '.' );
		if ( $pointPos === false ) {
			$pointPos = strlen( $decimal );
		}

		// account for leading sign
		$pointPos--;

		if ( $exponent < 0 ) {
			// account for decimal point
			$position = $pointPos + 1 - $exponent;
		} else {
			// make sure we don't remove more digits than are there
			$position = max( 0, $pointPos - $exponent );
		}

		return $position;
	}

	/**
	 * Returns the given value, with any insignificant digits removed or zeroed.
	 *
	 * Rounding is applied using the "round half away from zero" rule (that is, +0.5 is
	 * rounded to +1 and -0.5 is rounded to -1).
	 *
	 * @see round()
	 *
	 * @param string $value
	 * @param int $significantDigits
	 *
	 * @throws InvalidArgumentException if $significantDigits is smaller than 0
	 * @return string
	 */
	private function roundDigits( $value, $significantDigits ) {
		if ( !is_int( $significantDigits ) || $significantDigits < 0 ) {
			throw new InvalidArgumentException( '$significantDigits must be a non-negative integer' );
		}

		// keeping no digits results in zero.
		if ( $significantDigits === 0 ) {
			return '+0';
		}

		$len = strlen( $value );

		// keeping all digits means no rounding
		if ( $significantDigits >= $len - 1 ) {
			return $value;
		}

		// whether the last character is already part of the integer part of the decimal value
		$i = min( $significantDigits + 1, $len ); // account for the sign
		$ch = $i < $len ? $value[$i] : '0';

		if ( $ch === '.' ) {
			// NOTE: we expect the input to be well formed, so it cannot end with a '.'
			$i++;
			$ch = $i < $len ? $value[$i] : '0';
		}

		// split in significant and insignificant part
		$rounded = substr( $value, 0, $i );

		if ( strpos( $rounded, '.' ) === false ) {
			$suffix = substr( $value, $i );

			// strip insignificant digits after the decimal point
			$ppos = strpos( $suffix, '.' );
			if ( $ppos !== false ) {
				$suffix = substr( $suffix, 0, $ppos );
			}

			// zero out insignificant digits
			$suffix = strtr( $suffix, '123456789', '000000000' );
		} else {
			// decimal point is in $rounded, so $suffix is insignificant
			$suffix = '';
		}

		if ( $ch >= '5' ) {
			$rounded = $this->bumpDigits( $rounded );
		}

		$rounded .= $suffix;

		if ( $significantDigits > strlen( $rounded ) - 1 ) {
			if ( strpos( $rounded, '.' ) !== false ) {
				$rounded = str_pad( $rounded, $significantDigits + 1, '0', STR_PAD_RIGHT );
			}
		}

		// strip trailing decimal point
		$rounded = rtrim( $rounded, '.' );

		return $rounded;
	}

	/**
	 * Increment the least significant digit by one if it is less than 9, and
	 * set it to zero and continue to the next more significant digit if it is 9.
	 * Exception: bump( 0 ) == 1;
	 *
	 * E.g.: bump( 0.2 ) == 0.3, bump( -0.09 ) == -0.10, bump( 9.99 ) == 10.00
	 *
	 * This is the inverse of @see slump()
	 *
	 * @since 0.1
	 *
	 * @param DecimalValue $decimal
	 *
	 * @return DecimalValue
	 */
	public function bump( DecimalValue $decimal ) {
		$value = $decimal->getValue();
		$bumped = $this->bumpDigits( $value );
		return new DecimalValue( $bumped );
	}

	/**
	 * Increment the least significant digit by one if it is less than 9, and
	 * set it to zero and continue to the next more significant digit if it is 9.
	 *
	 * @see bump()
	 *
	 * @param string $value
	 * @return string
	 */
	private function bumpDigits( $value ) {
		if ( $value === '+0' ) {
			return '+1';
		}

		$bumped = '';

		for ( $i = strlen( $value ) - 1; $i >= 0; $i-- ) {
			$ch = $value[$i];

			if ( $ch === '.' ) {
				$bumped = '.' . $bumped;
				continue;
			} elseif ( $ch === '9' ) {
				$bumped = '0' . $bumped;
				continue;
			} elseif ( $ch === '+' || $ch === '-' ) {
				$bumped = $ch . '1' . $bumped;
				break;
			} else {
				$bumped = chr( ord( $ch ) + 1 ) . $bumped;
				break;
			}
		}

		$bumped = substr( $value, 0, $i ) . $bumped;
		return $bumped;
	}

	/**
	 * Decrement the least significant digit by one if it is more than 0, and
	 * set it to 9 and continue to the next more significant digit if it is 0.
	 * Exception: slump( 0 ) == -1;
	 *
	 * E.g.: slump( 0.2 ) == 0.1, slump( -0.10 ) == -0.01, slump( 0.0 ) == -1.0
	 *
	 * This is the inverse of @see bump()
	 *
	 * @since 0.1
	 *
	 * @param DecimalValue $decimal
	 *
	 * @return DecimalValue
	 */
	public function slump( DecimalValue $decimal ) {
		$value = $decimal->getValue();
		$slumped = $this->slumpDigits( $value );
		return new DecimalValue( $slumped );
	}

	/**
	 * Decrement the least significant digit by one if it is more than 0, and
	 * set it to 9 and continue to the next more significant digit if it is 0.
	 *
	 * @see slump()
	 *
	 * @param string $value
	 * @return string
	 */
	private function slumpDigits( $value ) {
		if ( $value === '+0' ) {
			return '-1';
		}

		// a "precise zero" will become negative
		if ( preg_match( '/^\+0\.(0*)0$/', $value, $m ) ) {
			return '-0.' . $m[1] . '1';
		}

		$slumped = '';

		for ( $i = strlen( $value ) - 1; $i >= 0; $i-- ) {
			$ch = substr( $value, $i, 1 );

			if ( $ch === '.' ) {
				$slumped = '.' . $slumped;
				continue;
			} elseif ( $ch === '0' ) {
				$slumped = '9' . $slumped;
				continue;
			} elseif ( $ch === '+' || $ch === '-' ) {
				$slumped = '0';
				break;
			} else {
				$slumped = chr( ord( $ch ) - 1 ) . $slumped;
				break;
			}
		}

		// preserve prefix
		$slumped = substr( $value, 0, $i ) . $slumped;
		$slumped = $this->stripLeadingZeros( $slumped );

		if ( $slumped === '-0' ) {
			$slumped = '+0';
		}

		return $slumped;
	}

	/**
	 * @param string $digits
	 *
	 * @return string
	 */
	private function stripLeadingZeros( $digits ) {
		$digits = preg_replace( '/^([-+])0+(?=\d)/', '\1', $digits );
		return $digits;
	}

	/**
	 * Shift the decimal point according to the given exponent.
	 *
	 * @param DecimalValue $decimal
	 * @param int $exponent The exponent to apply (digits to shift by). A Positive exponent
	 * shifts the decimal point to the right, a negative exponent shifts to the left.
	 *
	 * @throws InvalidArgumentException
	 * @return DecimalValue
	 */
	public function shift( DecimalValue $decimal, $exponent ) {
		if ( !is_int( $exponent ) ) {
			throw new InvalidArgumentException( '$exponent must be an integer' );
		}

		if ( $exponent == 0 ) {
			return $decimal;
		}

		$sign = $decimal->getSign();
		$intPart = $decimal->getIntegerPart();
		$fractPart = $decimal->getFractionalPart();

		if ( $exponent < 0 ) {
			$intPart = $this->shiftLeft( $intPart, $exponent );
		} else {
			$fractPart = $this->shiftRight( $fractPart, $exponent );
		}

		$digits = $sign . $intPart . $fractPart;
		$digits = $this->stripLeadingZeros( $digits );

		return new DecimalValue( $digits );
	}

	/**
	 * @param string $intPart
	 * @param int $exponent must be negative
	 *
	 * @return string
	 */
	private function shiftLeft( $intPart, $exponent ) {
		//note: $exponent is negative!
		if ( -$exponent < strlen( $intPart ) ) {
			$intPart = substr( $intPart, 0, $exponent ) . '.' . substr( $intPart, $exponent );
		} else {
			$intPart = '0.' . str_pad( $intPart, -$exponent, '0', STR_PAD_LEFT );
		}

		return $intPart;
	}

	/**
	 * @param string $fractPart
	 * @param int $exponent must be positive
	 *
	 * @return string
	 */
	private function shiftRight( $fractPart, $exponent ) {
		//note: $exponent is positive.
		if ( $exponent < strlen( $fractPart ) ) {
			$fractPart = substr( $fractPart, 0, $exponent ) . '.' . substr( $fractPart, $exponent );
		} else {
			$fractPart = str_pad( $fractPart, $exponent, '0', STR_PAD_RIGHT );
		}

		return $fractPart;
	}

}
