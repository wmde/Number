<?php

namespace ValueParsers;

/**
 * Interface for services that convert a string to canonical form.
 *
 * @since 0.2
 *
 * @license GPL 2+
 * @author Daniel Kinzler
 */
interface Unlocalizer {

	/**
	 * Converts a localized string to canonical/internal representation.
	 *
	 * @param string $string string to process
	 * @param string $language language code
	 * @param ParserOptions $options
	 *
	 * @return string unlocalized string
	 */
	public function unlocalize( $string, $language, ParserOptions $options );

}