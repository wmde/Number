<?php

namespace ValueFormatters;

use DataValues\DecimalValue;
use InvalidArgumentException;

/**
 * Formatter for decimal values
 *
 * @since 0.1
 *
 * @license GPL-2.0-or-later
 * @author Daniel Kinzler
 */
class DecimalFormatter implements ValueFormatter {

	/**
	 * Option key for forcing the sign to be included in the
	 * formatter's output even if it's "+". The value must
	 * be a boolean.
	 */
	public const OPT_FORCE_SIGN = 'forceSign';

	/**
	 * @var FormatterOptions
	 */
	private $options;

	/**
	 * @var NumberLocalizer
	 */
	private $localizer;

	/**
	 * @param FormatterOptions|null $options
	 * @param NumberLocalizer|null $localizer
	 */
	public function __construct( FormatterOptions $options = null, NumberLocalizer $localizer = null ) {
		$this->options = $options ?: new FormatterOptions();

		$this->options->defaultOption( ValueFormatter::OPT_LANG, 'en' );
		$this->options->defaultOption( self::OPT_FORCE_SIGN, false );

		$this->localizer = $localizer ?: new BasicNumberLocalizer();
	}

	/**
	 * @see ValueFormatter::format
	 *
	 * @param DecimalValue $dataValue
	 *
	 * @throws InvalidArgumentException
	 * @return string Text
	 */
	public function format( $dataValue ) {
		if ( !( $dataValue instanceof DecimalValue ) ) {
			throw new InvalidArgumentException( 'Data value type mismatch. Expected a DecimalValue.' );
		}

		// TODO: Implement optional rounding/padding
		$decimal = $dataValue->getValue();

		if ( !$this->options->getOption( self::OPT_FORCE_SIGN ) ) {
			// strip leading +
			$decimal = ltrim( $decimal, '+' );
		}

		// apply number localization
		$decimal = $this->localizer->localizeNumber( $decimal );

		return $decimal;
	}

}
