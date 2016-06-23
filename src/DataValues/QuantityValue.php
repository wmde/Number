<?php

namespace DataValues;

use InvalidArgumentException;

/**
 * Class representing a quantity with associated unit.
 * The amount is stored as a @see DecimalValue object.
 *
 * For quantities with a known uncertainty interval, see BoundedQuantityValue.
 *
 * For simple numeric amounts use @see NumberValue.
 *
 * @since 0.1
 *
 * @license GPL-2.0+
 * @author Daniel Kinzler
 */
class QuantityValue extends DataValueObject {

	/**
	 * The quantity's amount
	 *
	 * @var DecimalValue
	 */
	protected $amount;

	/**
	 * The quantity's unit identifier (use "1" for unitless quantities).
	 *
	 * @var string
	 */
	protected $unit;

	/**
	 * Constructs a new QuantityValue object, representing the given value.
	 *
	 * @since 0.1
	 *
	 * @param DecimalValue $amount
	 * @param string $unit A unit identifier. Must not be empty, use "1" for unit-less quantities.
	 *
	 * @throws IllegalValueException
	 */
	public function __construct( DecimalValue $amount, $unit ) {
		if ( !is_string( $unit ) ) {
			throw new IllegalValueException( '$unit needs to be a string, not ' . gettype( $unit ) );
		}

		if ( $unit === '' ) {
			throw new IllegalValueException( '$unit can not be an empty string (use "1" for unit-less quantities)' );
		}

		$this->amount = $amount;
		$this->unit = $unit;
	}

	/**
	 * Returns a QuantityValue representing the given amount.
	 *
	 * This is a convenience wrapper around the constructor that accepts native values
	 * instead of DecimalValue objects.
	 *
	 * @note: if the amount or a bound is given as a string, the string must conform
	 * to the rules defined by @see DecimalValue.
	 *
	 * @since 0.1
	 *
	 * @param string|int|float|DecimalValue $number
	 * @param string $unit A unit identifier. Must not be empty, use "1" for unit-less quantities.
	 *
	 * @return self
	 * @throws IllegalValueException
	 */
	public static function newFromNumber( $number, $unit = '1' ) {
		$number = self::asDecimalValue( 'amount', $number );

		return new self( $number, $unit );
	}

	/**
	 * @see newFromNumber
	 *
	 * @deprecated since 0.1, use newFromNumber instead
	 *
	 * @param string|int|float|DecimalValue $number
	 * @param string $unit
	 * @param string|int|float|DecimalValue|null $upperBound
	 * @param string|int|float|DecimalValue|null $lowerBound
	 *
	 * @return self
	 */
	public static function newFromDecimal( $number, $unit = '1' ) {
		return self::newFromNumber( $number, $unit );
	}

	/**
	 * Converts $number to a DecimalValue if possible and necessary.
	 *
	 * @note: if the $number is given as a string, it must conform to the rules
	 *        defined by @see DecimalValue.
	 *
	 * @param string $name The variable name to use in exception messages
	 * @param string|int|float|DecimalValue|null $number
	 * @param DecimalValue|null $default
	 *
	 * @throws IllegalValueException
	 * @throws InvalidArgumentException
	 * @return DecimalValue
	 */
	protected static function asDecimalValue( $name, $number, DecimalValue $default = null ) {
		if ( !is_string( $name ) ) {
			throw new InvalidArgumentException( '$name must be a string' );
		}

		if ( $number === null ) {
			if ( $default === null ) {
				throw new InvalidArgumentException( '$' . $name . ' must not be null' );
			}

			$number = $default;
		}

		if ( $number instanceof DecimalValue ) {
			// nothing to do
		} elseif ( is_int( $number ) || is_float( $number ) || is_string( $number ) ) {
			$number = new DecimalValue( $number );
		} else {
			throw new IllegalValueException( '$' . $name . '  must be a string, int, or float' );
		}

		return $number;
	}

	/**
	 * @see Serializable::serialize
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function serialize() {
		return serialize( array_values( $this->getArrayValue() ) );
	}

	/**
	 * @see Serializable::unserialize
	 *
	 * @since 0.1
	 *
	 * @param string $data
	 */
	public function unserialize( $data ) {
		list( $amount, $unit ) = unserialize( $data );
		$this->__construct( $amount, $unit );
	}

	/**
	 * @see DataValue::getType
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public static function getType() {
		return 'quantity';
	}

	/**
	 * @see DataValue::getSortKey
	 *
	 * @since 0.1
	 *
	 * @return float
	 */
	public function getSortKey() {
		return $this->amount->getValueFloat();
	}

	/**
	 * Returns the quantity object.
	 * @see DataValue::getValue
	 *
	 * @since 0.1
	 *
	 * @return self
	 */
	public function getValue() {
		return $this;
	}

	/**
	 * Returns the amount represented by this quantity.
	 *
	 * @since 0.1
	 *
	 * @return DecimalValue
	 */
	public function getAmount() {
		return $this->amount;
	}

	/**
	 * Returns the unit held by this quantity.
	 * Unit-less quantities should use "1" as their unit.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getUnit() {
		return $this->unit;
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

		// use a preliminary QuantityValue to determine the significant number of digits
		return new self( $amount, $newUnit );
	}

	public function __toString() {
		return $this->amount->getValue()
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
		self::requireArrayFields( $data, array( 'amount', 'unit' ) );

		return new static(
			DecimalValue::newFromArray( $data['amount'] ),
			$data['unit']
		);
	}

	/**
	 * @see Comparable::equals
	 *
	 * @since 0.1
	 *
	 * @param mixed $target
	 *
	 * @return bool
	 */
	public function equals( $target ) {
		if ( $this === $target ) {
			return true;
		}

		return $target instanceof self
			&& $this->toArray() === $target->toArray();
	}

}
