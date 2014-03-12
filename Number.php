<?php

/**
 * Entry point of the DataValues Number library.
 *
 * @since 0.1
 * @codeCoverageIgnore
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */

if ( defined( 'DATAVALUES_NUMBER_VERSION' ) ) {
	// Do not initialize more then once.
	return 1;
}

define( 'DATAVALUES_NUMBER_VERSION', '0.3' );

if ( defined( 'MEDIAWIKI' ) ) {
	$GLOBALS['wgExtensionCredits']['datavalues'][] = array(
		'path' => __DIR__,
		'name' => 'DataValues Number',
		'version' => DATAVALUES_NUMBER_VERSION,
		'author' => array(
			'Daniel Kinzler',
		),
		'url' => 'https://github.com/DataValues/Number',
		'description' => 'Numerical value objects, parsers and formatters',
	);
}
