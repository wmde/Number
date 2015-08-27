<?php

namespace ValueParsers;

use DataValues\DecimalMath;
use DataValues\DecimalValue;
use DataValues\IllegalValueException;
use DataValues\MatrixValue;
use DataValues\NumberValue;
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

        $numberPattern = $this->unlocalizer->getNumberRegex( '@' );

        $pattern = '@'
                . '\[\s*((?:' . $numberPattern . ')(?:,' . $numberPattern . ')*)\s*\]'
                . '@u';

        preg_match_all( $pattern, $value, $groups );

        $data = array();

        foreach( $groups[1] as $group ) {
            $row = array();
            foreach( preg_split( '/\s*,\s*/', $group ) as $element ) {
                $row[] = new NumberValue( floatval( $element ) );
            }
            $data[] = $row;
        }

        try {
            return new MatrixValue( $data );
        } catch ( IllegalValueException $ex ) {
            throw new ParseException( $ex->getMessage(), $value, self::FORMAT_NAME );
        }
    }
}
