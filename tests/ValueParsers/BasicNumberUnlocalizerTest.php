<?php

namespace ValueParsers\Test;

use PHPUnit_Framework_TestCase;
use ValueParsers\BasicNumberUnlocalizer;

/**
 * @covers ValueParsers\BasicNumberUnlocalizer
 *
 * @group DataValue
 * @group DataValueExtensions
 *
 * @license GPL-2.0+
 * @author Daniel Kinzler
 */
class BasicNumberUnlocalizerTest extends PHPUnit_Framework_TestCase {

	public function provideUnlocalizeNumber() {
		return [
			[ '5', '5' ],
			[ '+3', '+3' ],
			[ '-15', '-15' ],

			[ '5.3', '5.3' ],
			[ '+3.2', '+3.2' ],
			[ '-15.77', '-15.77' ],

			[ '.3', '.3' ],
			[ '+.2', '+.2' ],
			[ '-.77', '-.77' ],

			[ '5.3e4', '5.3e4' ],
			[ '+3.2E-4', '+3.2E-4' ],
			[ '-15.77e+4.2', '-15.77e+4.2' ],

			[ '0x20', '0x20' ],
			[ '0X20', '0X20' ],

			[ '1,335.3', '1335.3' ],
			[ '+1,333.2', '+1333.2' ],
			[ '-1,315.77', '-1315.77' ],

			[ ' 1,333.3', '1333.3' ],
			[ '1,333.3 ', '1333.3' ],
		];
	}

	/**
	 * @dataProvider provideUnlocalizeNumber
	 */
	public function testUnlocalizeNumber( $localized, $expected ) {
		$unlocalizer = new BasicNumberUnlocalizer();
		$unlocalized = $unlocalizer->unlocalizeNumber( $localized );

		$this->assertSame( $expected, $unlocalized );
	}

	public function provideGetNumberRegexMatch() {
		return [
			[ '5' ],
			[ '+3' ],
			[ '-15' ],

			[ '5.3' ],
			[ '+3.2' ],
			[ '-15.77' ],

			[ '.3' ],
			[ '+.2' ],
			[ '-.77' ],

			[ '5.3e4' ],
			[ '+3.2E-4' ],
			[ '-15.77e+2' ],

			[ '1,335.3' ],
			[ '+1,333.2' ],
			[ '-1,315.77' ],
		];
	}

	/**
	 * @dataProvider provideGetNumberRegexMatch
	 */
	public function testGetNumberRegexMatch( $value ) {
		$unlocalizer = new BasicNumberUnlocalizer();
		$regex = $unlocalizer->getNumberRegex();

		$this->assertTrue( (bool)preg_match( "/^($regex)$/u", $value ) );
	}

	public function provideGetNumberRegexMismatch() {
		return [
			[ '' ],
			[ ' ' ],
			[ '+' ],
			[ 'e' ],

			[ '+.' ],
			[ '.-' ],
			[ '...' ],

			[ '0x20' ],
			[ '2x2' ],
			[ 'x2' ],
			[ '2x' ],

			[ 'e.' ],
			[ '.e' ],
			[ '12e' ],
			[ 'E17' ],

			[ '+-3' ],
			[ '++7' ],
			[ '--5' ],
		];
	}

	/**
	 * @dataProvider provideGetNumberRegexMismatch
	 */
	public function testGetNumberRegexMismatch( $value ) {
		$unlocalizer = new BasicNumberUnlocalizer();
		$regex = $unlocalizer->getNumberRegex();

		$this->assertFalse( (bool)preg_match( "/^($regex)$/u", $value ) );
	}

	public function provideGetUnitRegexMatch() {
		return [
			[ '' ],
		];
	}

	/**
	 * @dataProvider provideGetUnitRegexMatch
	 */
	public function testGetUnitRegexMatch( $value ) {
		$unlocalizer = new BasicNumberUnlocalizer();
		$regex = $unlocalizer->getUnitRegex();

		$this->assertTrue( (bool)preg_match( "/^($regex)$/u", $value ) );
	}

	public function provideGetUnitRegexMismatch() {
		return [
			[ ' ' ],

			[ '^' ],
			[ '/' ],

			[ 'x^' ],
			[ 'x/' ],

			[ '2' ],
			[ '2b' ],

			[ '~' ],
			[ '#' ],
		];
	}

	/**
	 * @dataProvider provideGetUnitRegexMismatch
	 */
	public function testGetUnitRegexMismatch( $value ) {
		$unlocalizer = new BasicNumberUnlocalizer();
		$regex = $unlocalizer->getUnitRegex();

		$this->assertFalse( (bool)preg_match( "/^($regex)$/u", $value ) );
	}

}
