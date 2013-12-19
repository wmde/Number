<?php

if ( php_sapi_name() !== 'cli' ) {
	die( 'Not an entry point' );
}

$pwd = exec( 'pwd' );
chdir( __DIR__ . '/..' );
echo passthru( 'composer update' ) . "\n";
chdir( $pwd );

require_once( __DIR__ . '/../vendor/autoload.php' );
