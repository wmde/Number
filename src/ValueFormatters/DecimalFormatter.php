<?php

namespace ValueFormatters;

use DataValues\DecimalValue;
use InvalidArgumentException;

/**
 * Formatter for decimal values
 *
 * @since 0.1
 *
 * @licence GNU GPL v2+
 * @author Daniel Kinzler
 */
class DecimalFormatter extends ValueFormatterBase {

	/**
	 * Option key for forcing the sign to be included in the
	 * formatter's output even if it's "+". The value must
	 * be a boolean.
	 */
	const OPT_FORCE_SIGN = 'forceSign';

	/**
	 * @var NumberLocalizer
	 */
	protected $localizer;

	/**
	 * @param FormatterOptions $options
	 * @param NumberLocalizer|null $localizer
	 */
	public function __construct( FormatterOptions $options, NumberLocalizer $localizer = null ) {
		$options->defaultOption( self::OPT_FORCE_SIGN, false );

		parent::__construct( $options );

		if ( !$localizer ) {
			$localizer = new BasicNumberLocalizer();
		}

		$this->localizer = $localizer;
	}

	/**
	 * Formats a QuantityValue data value
	 *
	 * @since 0.1
	 *
	 * @param mixed $dataValue value to format
	 *
	 * @return string
	 * @throws InvalidArgumentException
	 */
	public function format( $dataValue ) {
		if ( !( $dataValue instanceof DecimalValue ) ) {
			throw new InvalidArgumentException( 'DataValue is not a DecimalValue.' );
		}

		// TODO: Implement optional rounding/padding
		$decimal = $dataValue->getValue();

		if ( !$this->getOption( self::OPT_FORCE_SIGN ) ) {
			// strip leading +
			$decimal = ltrim( $decimal, '+' );
		}

		// apply number localization
		$decimal = $this->localizer->localizeNumber( $decimal );

		return $decimal;
	}

}
