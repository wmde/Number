<?php
namespace DataValues;
/**
 * Class representing a matrix.
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
	 * Constructs a new MatrixValue object, representing the given value.
	 *
	 * @var mixed $value An array of arrays of numbers.
	 *
	 * @throws IllegalValueException
	 */
	public function __construct( $value ) {
        if ( !is_array( $value ) ) {
			throw new IllegalValueException( '$value must be an array' );
        }

        if ( count( $value ) == 0 ) {
			throw new IllegalValueException( '$value can not be an empty array' );
        }

        $columns = null;
        foreach ( $value as $row ) {
            if( !is_array( $row ) ) {
		    	throw new IllegalValueException( '$value must be an array of arrays' );
            }
            if( $columns == null ) {
                $columns = count( $row );
            } else if( $columns != count( $row ) ) {
			    throw new IllegalValueException( 'all rows of $value must be of the same length' );
            }
        }

		$this->value = $value;
	}

	/**
	 * @see Serializable::serialize
	 *
	 * @return string
	 */
	public function serialize() {
		return serialize( $this->value );
	}

	/**
	 * @see Serializable::unserialize
	 *
	 * @param string $value
	 */
	public function unserialize( $data ) {
		$this->__construct( unserialize( $data ) );
	}

	/**
	 * @see DataValue::getType
	 *
	 * @return string
	 */
	public static function getType() {
		return 'matrix';
	}

	/**
	 * @see DataValue::getSortKey
	 *
	 * @return int Always 0 in this implementation.
	 */
	public function getSortKey() {
		return 0;
	}

	/**
	 * Returns the object.
	 * @see DataValue::getValue
	 *
	 * @return MatrixValue
	 */
	public function getValue() {
		return $this;
	}

}
