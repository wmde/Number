<?php

namespace ValueParsers;

use DataValues\DecimalValue;
use DataValues\IllegalValueException;
use DataValues\MatrixValue;
use InvalidArgumentException;

/**
 * ValueParser that parses the string representation of a matrix.
 *
 * @licence GNU GPL v2+
 * @author Andrius Merkys
 */
class MatrixParser extends StringValueParser {

    const FORMAT_NAME = 'matrix';

    /**
     * @var NumberUnlocalizer
     */
    private $unlocalizer;

    /**
     * @param ParserOptions|null $options
     * @param NumberUnlocalizer $unlocalizer
     */
    public function __construct( ParserOptions $options = null, NumberUnlocalizer $unlocalizer = null ) {
        parent::__construct( $options );

        $this->unlocalizer = $unlocalizer ?: new BasicNumberUnlocalizer();
        $this->decimalParser = new DecimalParser( $this->options, $this->unlocalizer );
    }

    /**
     * @see StringValueParser::stringParse
     *
     * @param string $value
     *
     * @return MatrixValue
     * @throws ParseException
     */
    protected function stringParse( $value ) {
        if ( !is_string( $value ) ) {
            throw new InvalidArgumentException( '$value must be a string' );
        }

        $decoded = json_decode( $value );
        if( $decoded == null ) {
            throw new ParseException( 'can not parse JSON' );
        }

        try {
            return new MatrixValue( $decoded );
        } catch ( IllegalValueException $ex ) {
            throw new ParseException( $ex->getMessage(), $value, self::FORMAT_NAME );
        }
    }
}
