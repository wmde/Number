<?php

namespace DataValues;

use InvalidArgumentException;

/**
 * Class representing a quantity with associated unit.
 * The amount is stored as a @see DecimalValue object.
 *
 * @see QuantityValue for quantities with a known uncertainty interval.
 * For simple numeric amounts use @see NumberValue.
 *
 * @note UnboundedQuantityValue and QuantityValue both use the value type ID "quantity".
 * The fact that we use subclassing to model the bounded vs the unbounded case should be
 * considered an implementation detail.
 *
 * @since 0.8
 *
 * @license GPL-2.0-or-later
 * @author Daniel Kinzler
 */
class UnboundedQuantityValue extends DataValueObject {

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
	 * @param DecimalValue $amount
	 * @param string $unit A unit identifier. Must not be empty, use "1" for unit-less quantities.
	 *
	 * @throws IllegalValueException
	 */
	public function __construct( DecimalValue $amount, $unit ) {
		if ( !is_string( $unit ) || $unit === '' ) {
			throw new IllegalValueException( '$unit must be a non-empty string. Use "1" for unit-less quantities.' );
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
	 * @note if the amount or a bound is given as a string, the string must conform
	 * to the rules defined by @see DecimalValue.
	 *
	 * @param string|int|float|DecimalValue $amount
	 * @param string $unit A unit identifier. Must not be empty, use "1" for unit-less quantities.
	 *
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public static function newFromNumber( $amount, $unit = '1' ) {
		$amount = self::asDecimalValue( 'amount', $amount );

		return new self( $amount, $unit );
	}

	/**
	 * Converts $number to a DecimalValue if possible and necessary.
	 *
	 * @note if the $number is given as a string, it must conform to the rules
	 *        defined by @see DecimalValue.
	 *
	 * @param string $name The variable name to use in exception messages
	 * @param string|int|float|DecimalValue|null $number
	 * @param DecimalValue|null $default
	 *
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
	 * @return string
	 */
	public function serialize() {
		return serialize( $this->__serialize() );
	}

	public function __serialize(): array {
		return [
			$this->amount,
			$this->unit
		];
	}

	/**
	 * @see Serializable::unserialize
	 *
	 * @param string $data
	 */
	public function unserialize( $data ) {
		$this->__unserialize( unserialize( $data ) );
	}

	public function __unserialize( array $data ): void {
		list( $amount, $unit ) = $data;
		$this->__construct( $amount, $unit );
	}

	public function getSerializationForHash(): string {
		// mimic a legacy serialization of __serialize() (amount + unit)
		$amountSerialization = method_exists( $this->amount, 'getSerializationForHash' )
			? $this->amount->getSerializationForHash()
			: serialize( $this->amount );
		$unitSerialization = serialize( $this->unit );

		$data = 'a:2:{i:0;' . $amountSerialization . 'i:1;' . $unitSerialization . '}';

		return 'C:' . strlen( static::class ) . ':"' . static::class .
			'":' . strlen( $data ) . ':{' . $data . '}';
	}

	/**
	 * @see DataValue::getType
	 *
	 * @return string
	 */
	public static function getType() {
		return 'quantity';
	}

	/**
	 * @deprecated Kept for compatibility with older DataValues versions.
	 * Do not use.
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
	 * @return self
	 */
	public function getValue() {
		return $this;
	}

	/**
	 * Returns the amount represented by this quantity.
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
	 * @todo Should be factored out into a separate QuantityMath class.
	 *
	 * @throws InvalidArgumentException
	 * @return self
	 */
	public function transform( $newUnit, $transformation ) {
		if ( !is_callable( $transformation ) ) {
			throw new InvalidArgumentException( '$transformation must be callable.' );
		}

		if ( !is_string( $newUnit ) || $newUnit === '' ) {
			throw new InvalidArgumentException(
				'$newUnit must be a non-empty string. Use "1" for unit-less quantities.'
			);
		}

		// Apply transformation by calling the $transform callback.
		// The first argument for the callback is the DataValue to transform. In addition,
		// any extra arguments given for transform() are passed through.
		$args = func_get_args();
		array_shift( $args );

		$args[0] = $this->amount;
		$amount = call_user_func_array( $transformation, $args );

		return new self( $amount, $newUnit );
	}

	public function __toString() {
		return $this->amount->getValue()
			. ( $this->unit === '1' ? '' : $this->unit );
	}

	/**
	 * @see DataValue::getArrayValue
	 *
	 * @return array
	 */
	public function getArrayValue() {
		return [
			'amount' => $this->amount->getArrayValue(),
			'unit' => $this->unit,
		];
	}

	/**
	 * Static helper capable of constructing both unbounded and bounded quantity value objects,
	 * depending on the serialization provided. Required for @see DataValueDeserializer.
	 * This is expected to round-trip with both @see getArrayValue as well as
	 * @see QuantityValue::getArrayValue.
	 *
	 * @deprecated since 0.8.3. Static DataValue::newFromArray constructors like this are
	 *  underspecified (not in the DataValue interface), and misleadingly named (should be named
	 *  newFromArrayValue). Instead, use DataValue builder callbacks in @see DataValueDeserializer.
	 *
	 * @param mixed $data Warning! Even if this is expected to be a value as returned by
	 *  @see getArrayValue, callers of this specific newFromArray implementation can not guarantee
	 *  this. This is not even guaranteed to be an array!
	 *
	 * @throws IllegalValueException if $data is not in the expected format. Subclasses of
	 *  InvalidArgumentException are expected and properly handled by @see DataValueDeserializer.
	 * @return self|QuantityValue Either an unbounded or bounded quantity value object.
	 */
	public static function newFromArray( $data ) {
		self::requireArrayFields( $data, [ 'amount', 'unit' ] );

		if ( !isset( $data['upperBound'] ) && !isset( $data['lowerBound'] ) ) {
			return new self(
				DecimalValue::newFromArray( $data['amount'] ),
				$data['unit']
			);
		} else {
			self::requireArrayFields( $data, [ 'upperBound', 'lowerBound' ] );

			return new QuantityValue(
				DecimalValue::newFromArray( $data['amount'] ),
				$data['unit'],
				DecimalValue::newFromArray( $data['upperBound'] ),
				DecimalValue::newFromArray( $data['lowerBound'] )
			);
		}
	}

	/**
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
