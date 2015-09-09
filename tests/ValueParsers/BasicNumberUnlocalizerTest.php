<?php

namespace ValueParsers\Test;

use ValueParsers\BasicNumberUnlocalizer;

/**
 * @covers ValueParsers\BasicNumberUnlocalizer
 *
 * @group DataValue
 * @group DataValueExtensions
 *
 * @licence GNU GPL v2+
 * @author Daniel Kinzler
 */
class BasicNumberUnlocalizerTest extends \PHPUnit_Framework_TestCase {

	public function provideUnlocalizeNumber() {
		return array(
			array( '5', '5' ),
			array( '+3', '+3' ),
			array( '-15', '-15' ),

			array( '5.3', '5.3' ),
			array( '+3.2', '+3.2' ),
			array( '-15.77', '-15.77' ),

			array( '.3', '.3' ),
			array( '+.2', '+.2' ),
			array( '-.77', '-.77' ),

			array( '5.3e4', '5.3e4' ),
			array( '+3.2E-4', '+3.2E-4' ),
			array( '-15.77e+4.2', '-15.77e+4.2' ),

			array( '0x20', '0x20' ),
			array( '0X20', '0X20' ),

			array( '1,335.3', '1335.3' ),
			array( '+1,333.2', '+1333.2' ),
			array( '-1,315.77', '-1315.77' ),

			array( ' 1,333.3', '1333.3' ),
			array( '1,333.3 ', '1333.3' ),
		);
	}

	/**
	 * @dataProvider provideUnlocalizeNumber
	 */
	public function testUnlocalizeNumber( $localized, $expected ) {
		$unlocalizer = new BasicNumberUnlocalizer();
		$unlocalized = $unlocalizer->unlocalizeNumber( $localized );

		$this->assertEquals( $expected, $unlocalized );
	}

	public function provideGetNumberRegexMatch() {
		return array(
			array( '5' ),
			array( '+3' ),
			array( '-15' ),

			array( '5.3' ),
			array( '+3.2' ),
			array( '-15.77' ),

			array( '.3' ),
			array( '+.2' ),
			array( '-.77' ),

			array( '5.3e4' ),
			array( '+3.2E-4' ),
			array( '-15.77e+2' ),

			array( '1,335.3' ),
			array( '+1,333.2' ),
			array( '-1,315.77' ),
		);
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
		return array(
			array( '' ),
			array( ' ' ),
			array( '+' ),
			array( 'e' ),

			array( '+.' ),
			array( '.-' ),
			array( '...' ),

			array( '0x20' ),
			array( '2x2' ),
			array( 'x2' ),
			array( '2x' ),

			array( 'e.' ),
			array( '.e' ),
			array( '12e' ),
			array( 'E17' ),

			array( '+-3' ),
			array( '++7' ),
			array( '--5' ),
		);
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
		return array(
			array( '' ),
		);
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
		return array(
			array( ' ' ),

			array( '^' ),
			array( '/' ),

			array( 'x^' ),
			array( 'x/' ),

			array( '2' ),
			array( '2b' ),

			array( '~' ),
			array( '#' ),
		);
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
