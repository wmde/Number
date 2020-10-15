<?php

namespace ValueParsers;

/**
 * Interface for services that convert a string to canonical form.
 *
 * @see \ValueFormatters\NumberLocalizer
 *
 * @since 0.2
 *
 * @license GPL-2.0-or-later
 * @author Daniel Kinzler
 */
interface NumberUnlocalizer {

	/**
	 * Converts a localized number to canonical/internal representation.
	 *
	 * @since 0.3
	 *
	 * @param string $number string to process
	 *
	 * @return string unlocalized number, in a form suitable for floatval resp. intval.
	 */
	public function unlocalizeNumber( $number );

	/**
	 * Returns a PCRE style regular expression (without delimiters)
	 * that will match localized numbers, including sign and separators.
	 *
	 * @note The expression SHOULD only match valid numbers,
	 * but may be rather lenient.
	 *
	 * @note The expression MUST NOT may contain capturing groups. Callers
	 * MUST NOT expect it to be a group at all.
	 *
	 * @note The expression MAY contain multi-byte unicode characters
	 * and thus MUST be used with the PCRE "u" switch.
	 *
	 * @since 0.3
	 *
	 * @param string $delimiter The regex delimiter, used for escaping.
	 *
	 * @return string regular expression
	 */
	public function getNumberRegex( $delimiter = '/' );

	/**
	 * Returns a PCRE style regular expression (without delimiters)
	 * that will match localized unit identifiers.
	 *
	 * @note The expression SHOULD match most commonly used units,
	 * but can not be expected to cover every eventuality.
	 *
	 * @note The expression MUST NOT may contain capturing groups. Callers
	 * MUST NOT expect it to be a group at all.
	 *
	 * @note The expression MAY contain multi-byte unicode characters
	 * and thus MUST be used with the PCRE "u" switch.
	 *
	 * @since 0.3
	 *
	 * @param string $delimiter The regex delimiter, used for escaping.
	 *
	 * @return string regular expression
	 */
	public function getUnitRegex( $delimiter = '/' );

}
