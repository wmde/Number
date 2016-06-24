<?php

namespace DataValues;

use InvalidArgumentException;

/**
 * Class representing a quantity with associated unit and uncertainty interval.
 * The amount is stored as a @see DecimalValue object.
 *
 * @see QuantityValue For quantities with no known uncertainty interval.
 * For simple numeric amounts use @see NumberValue.
 *
 * @note BoundedQuantityValue and plain QuantityValue both use the value type ID "quantity".
 * The fact that we use subclassing to model the bounded vs the unbounded case should be
 * considered an implementation detail.
 *
 * @since 0.1
 *
 * @license GPL-2.0+
 * @author Daniel Kinzler
 */
class BoundedQuantityValue extends QuantityValue {

	/**
	 * The quantity's upper bound
	 *
	 * @var DecimalValue
	 */
	private $upperBound;

	/**
	 * The quantity's lower bound
	 *
	 * @var DecimalValue
	 */
	private $lowerBound;

	/**
	 * Constructs a new QuantityValue object, representing the given value.
	 *
	 * @since 0.1
	 *
	 * @param DecimalValue $amount
	 * @param string $unit A unit identifier. Must not be empty, use "1" for unit-less quantities.
	 * @param DecimalValue $upperBound The upper bound of the quantity, inclusive.
	 * @param DecimalValue $lowerBound The lower bound of the quantity, inclusive.
	 *
	 * @throws IllegalValueException
	 */
	public function __construct( DecimalValue $amount, $unit, DecimalValue $upperBound, DecimalValue $lowerBound ) {
		parent::__construct( $amount, $unit );

		if ( $lowerBound->compare( $amount ) > 0 ) {
			throw new IllegalValueException( '$lowerBound ' . $lowerBound->getValue() . ' must be <= $amount ' . $amount->getValue() );
		}

		if ( $upperBound->compare( $amount ) < 0 ) {
			throw new IllegalValueException( '$upperBound ' . $upperBound->getValue() . ' must be >= $amount ' . $amount->getValue() );
		}

		$this->upperBound = $upperBound;
		$this->lowerBound = $lowerBound;
	}

	/**
	 * Returns a QuantityValue representing the given amount.
	 * If no upper or lower bound is given, the amount is assumed to be absolutely exact,
	 * that is, the amount itself will be used as the upper and lower bound.
	 *
	 * This is a convenience wrapper around the constructor that accepts native values
	 * instead of DecimalValue objects.
	 *
	 * @note: if the amount or a bound is given as a string, the string must conform
	 * to the rules defined by @see DecimalValue.
	 *
	 * @since 0.1
	 *
	 * @param string|int|float|DecimalValue|QuantityValue $number
	 * @param string $unit A unit identifier. Must not be empty, use "1" for unit-less quantities.
	 * @param string|int|float|DecimalValue|null $upperBound
	 * @param string|int|float|DecimalValue|null $lowerBound
	 *
	 * @return self
	 * @throws IllegalValueException
	 */
	public static function newFromNumber( $number, $unit = '1', $upperBound = null, $lowerBound = null ) {
		$number = self::asDecimalValue( 'amount', $number );
		$upperBound = self::asDecimalValue( 'upperBound', $upperBound, $number );
		$lowerBound = self::asDecimalValue( 'lowerBound', $lowerBound, $number );

		return new self( $number, $unit, $upperBound, $lowerBound );
	}

	/**
	 * @see newFromNumber
	 *
	 * @deprecated since 0.1, use newFromNumber instead
	 *
	 * @param string|int|float|DecimalValue|QuantityValue $number
	 * @param string $unit
	 * @param string|int|float|DecimalValue|null $upperBound
	 * @param string|int|float|DecimalValue|null $lowerBound
	 *
	 * @return self
	 */
	public static function newFromDecimal( $number, $unit = '1', $upperBound = null, $lowerBound = null ) {
		return self::newFromNumber( $number, $unit, $upperBound, $lowerBound );
	}

	/**
	 * @see Serializable::unserialize
	 *
	 * @since 0.1
	 *
	 * @param string $data
	 */
	public function unserialize( $data ) {
		list( $amount, $unit, $upperBound, $lowerBound ) = unserialize( $data );
		$amount = DecimalValue::newFromArray( $amount );
		$upperBound = DecimalValue::newFromArray( $upperBound );
		$lowerBound = DecimalValue::newFromArray( $lowerBound );
		$this->__construct( $amount, $unit, $upperBound, $lowerBound );
	}

	/**
	 * Returns this quantity's upper bound.
	 *
	 * @since 0.1
	 *
	 * @return DecimalValue
	 */
	public function getUpperBound() {
		return $this->upperBound;
	}

	/**
	 * Returns this quantity's lower bound.
	 *
	 * @since 0.1
	 *
	 * @return DecimalValue
	 */
	public function getLowerBound() {
		return $this->lowerBound;
	}

	/**
	 * Returns the size of the uncertainty interval.
	 * This can roughly be interpreted as "amount +/- uncertainty/2".
	 *
	 * The exact interpretation of the uncertainty interval is left to the concrete application or
	 * data point. For example, the uncertainty interval may be defined to be that part of a
	 * normal distribution that is required to cover the 95th percentile.
	 *
	 * @since 0.1
	 *
	 * @return float
	 */
	public function getUncertainty() {
		return $this->upperBound->getValueFloat() - $this->lowerBound->getValueFloat();
	}

	/**
	 * Returns a DecimalValue representing the symmetrical offset to be applied
	 * to the raw amount for a rough representation of the uncertainty interval,
	 * as in "amount +/- offset".
	 *
	 * The offset is calculated as max( amount - lowerBound, upperBound - amount ).
	 *
	 * @since 0.1
	 *
	 * @return DecimalValue
	 */
	public function getUncertaintyMargin() {
		$math = new DecimalMath();

		$lowerMargin = $math->sum( $this->amount, $this->lowerBound->computeComplement() );
		$upperMargin = $math->sum( $this->upperBound, $this->amount->computeComplement() );

		$margin = $math->max( $lowerMargin, $upperMargin );
		return $margin;
	}

	/**
	 * Returns the order of magnitude of the uncertainty as the exponent of
	 * last significant digit in the amount-string. The value returned by this
	 * is suitable for use with @see DecimalMath::roundToExponent().
	 *
	 * @example: if two digits after the decimal point are significant, this
	 * returns -2.
	 *
	 * @example: if the last two digits before the decimal point are insignificant,
	 * this returns 2.
	 *
	 * Note that this calculation assumes a symmetric uncertainty interval,
	 * and can be misleading.
	 *
	 * @since 0.1
	 *
	 * @return int
	 */
	public function getOrderOfUncertainty() {
		// the desired precision is given by the distance between the amount and
		// whatever is closer, the upper or lower bound.
		//TODO: use DecimalMath to avoid floating point errors!
		$amount = $this->amount->getValueFloat();
		$upperBound = $this->upperBound->getValueFloat();
		$lowerBound = $this->lowerBound->getValueFloat();
		$precision = min( $amount - $lowerBound, $upperBound - $amount );

		if ( $precision === 0.0 ) {
			// If there is no uncertainty, the order of uncertainty is a bit more than what we have digits for.
			return -strlen( $this->amount->getFractionalPart() );
		}

		// e.g. +/- 200 -> 2; +/- 0.02 -> -2
		// note: we really want floor( log10( $precision ) ), but have to account for
		// small errors made in the floating point operations above.
		// @todo: use bcmath (via DecimalMath) to avoid this if possible
		$orderOfUncertainty = floor( log10( $precision + 0.0000000005 ) );

		return (int)$orderOfUncertainty;
	}

	/**
	 * Returns a transformed value derived from this QuantityValue by applying
	 * the given transformation to the amount and the upper and lower bounds.
	 * The resulting amount and bounds are rounded to the significant number of
	 * digits. Note that for exact quantities (with at least one bound equal to
	 * the amount), no rounding is applied (since they are considered to have
	 * infinite precision).
	 *
	 * The transformation is provided as a callback, which must implement a
	 * monotonously increasing, fully differentiable function mapping a DecimalValue
	 * to a DecimalValue. Typically, it will be a linear transformation applying a
	 * factor and an offset.
	 *
	 * @param string $newUnit The unit of the transformed quantity.
	 *
	 * @param callable $transformation A callback that implements the desired transformation.
	 *        The transformation will be called three times, once for the amount, once
	 *        for the lower bound, and once for the upper bound. It must return a DecimalValue.
	 *        The first parameter passed to $transformation is the DecimalValue to transform
	 *        In addition, any extra parameters passed to transform() will be passed through
	 *        to the transformation callback.
	 *
	 * @param mixed ... Any extra parameters will be passed to the $transformation function.
	 *
	 * @throws InvalidArgumentException
	 * @return self
	 */
	public function transform( $newUnit, $transformation ) {
		if ( !is_callable( $transformation ) ) {
			throw new InvalidArgumentException( '$transformation must be callable.' );
		}

		if ( !is_string( $newUnit ) ) {
			throw new InvalidArgumentException( '$newUnit must be a string. Use "1" as the unit for unit-less quantities.' );
		}

		if ( $newUnit === '' ) {
			throw new InvalidArgumentException( '$newUnit must not be empty. Use "1" as the unit for unit-less quantities.' );
		}

		$oldUnit = $this->unit;

		if ( $newUnit === null ) {
			$newUnit = $oldUnit;
		}

		// Apply transformation by calling the $transform callback.
		// The first argument for the callback is the DataValue to transform. In addition,
		// any extra arguments given for transform() are passed through.
		$args = func_get_args();
		array_shift( $args );

		$args[0] = $this->amount;
		$amount = call_user_func_array( $transformation, $args );

		$args[0] = $this->upperBound;
		$upperBound = call_user_func_array( $transformation, $args );

		$args[0] = $this->lowerBound;
		$lowerBound = call_user_func_array( $transformation, $args );

		// use a preliminary QuantityValue to determine the significant number of digits
		$transformed = new self( $amount, $newUnit, $upperBound, $lowerBound );
		$roundingExponent = $transformed->getOrderOfUncertainty();

		// apply rounding to the significant digits
		$math = new DecimalMath();

		$amount = $math->roundToExponent( $amount, $roundingExponent );
		$upperBound = $math->roundToExponent( $upperBound, $roundingExponent );
		$lowerBound = $math->roundToExponent( $lowerBound, $roundingExponent );

		return new self( $amount, $newUnit, $upperBound, $lowerBound );
	}

	public function __toString() {
		return $this->amount->getValue()
			. '[' . $this->lowerBound->getValue()
			. '..' . $this->upperBound->getValue()
			. ']'
			. ( $this->unit === '1' ? '' : $this->unit );
	}

	/**
	 * @see DataValue::getArrayValue
	 *
	 * @since 0.1
	 *
	 * @return array
	 */
	public function getArrayValue() {
		return array(
			'amount' => $this->amount->getArrayValue(),
			'unit' => $this->unit,
			'upperBound' => $this->upperBound->getArrayValue(),
			'lowerBound' => $this->lowerBound->getArrayValue(),
		);
	}

	/**
	 * Constructs a new instance of the DataValue from the provided data.
	 * This can round-trip with @see getArrayValue
	 *
	 * @since 0.1
	 *
	 * @param mixed $data
	 *
	 * @return self
	 * @throws IllegalValueException
	 */
	public static function newFromArray( $data ) {
		if ( !isset( $data['upperBound'] ) && isset( $data['lowerBound'] ) ) {
			// No bounds given, so construct an unbounded QuantityValue.
			return parent::newFromArray( $data );
		}

		self::requireArrayFields( $data, array( 'amount', 'unit', 'upperBound', 'lowerBound' ) );

		return new static(
			DecimalValue::newFromArray( $data['amount'] ),
			$data['unit'],
			DecimalValue::newFromArray( $data['upperBound'] ),
			DecimalValue::newFromArray( $data['lowerBound'] )
		);
	}

}