<?php
namespace DataValues;
/**
 * Class representing a matrix.
 *
 * @since 0.1
 *
 * @licence GNU GPL v2+
 * @author Andrius Merkys < andrius.merkys@gmail.com >
 */
class MatrixValue extends DataValueObject {
	/**
	 * @var int|float
	 */
	private $value;

	/**
	 * @var int
	 */
	private $rows;

	/**
	 * @var int
	 */
	private $columns;

	/**
	 * Constructs a new MatrixValue object, representing the given value.
	 *
	 * @var NumberValue $value An array of numbers.
	 * @var int $rows The number of rows.
	 * @var int $columns The number of columns.
	 *
	 * @throws IllegalValueException
	 */
	public function __construct( NumberValue $value, $rows, $columns ) {
		if ( $rows <= 0 ) {
			throw new IllegalValueException( '$rows must be > 0' );
		}

		if ( $columns <= 0 ) {
			throw new IllegalValueException( '$columns must be > 0' );
		}

        if ( count( $value ) != $rows * $columns ) {
            throw new IllegalValueException( '$value must be of length $rows * $columns' );
        }

		$this->value = $value;
		$this->rows = $rows;
		$this->columns = $columns;
	}

	/**
	 * @see Serializable::serialize
	 *
	 * @return string
	 */
	public function serialize() {
		return serialize( array(
            $this->value,
            $this->rows,
            $this->columns,
        ) );
	}

	/**
	 * @see Serializable::unserialize
	 *
	 * @param string $value
	 */
	public function unserialize( $value ) {
		list( $value, $rows, $columns ) = unserialize( $data );
		$this->__construct( $value, $rows, $columns );
	}

	/**
	 * @see DataValue::getType
	 *
	 * @return string
	 */
	public static function getType() {
		return 'matrix';
	}
}
