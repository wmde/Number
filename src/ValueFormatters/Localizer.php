<?php

namespace ValueFormatters;
use InvalidArgumentException;

/**
 * Interface defining a service for localizing a string based on a language code.
 * This may for instance be used to re-format a numeric string according to
 * the rules of a given locale.
 *
 * @since 0.2
 *
 * @licence GNU GPL v2+
 * @author Daniel Kinzler
 */
interface Localizer {

	/**
	 * Localizes a given string.
	 *
	 * Implementations are free to specify expectations
	 * as to for format of the string provided, and throw an exception if the
	 * string does not conform to these expectations.
	 *
	 * @since 0.1
	 *
	 * @param string $string
	 * @param string $language
	 * @param FormatterOptions $options
	 *
	 * @return string
	 * @throws InvalidArgumentException
	 */
	public function localize( $string, $language, FormatterOptions $options );
}