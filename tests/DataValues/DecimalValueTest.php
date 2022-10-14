<?php

namespace DataValues\Tests;

use DataValues\DecimalValue;
use PHPUnit\Framework\TestCase;

/**
 * @covers DataValues\DecimalValue
 *
 * @group DataValue
 * @group DataValueExtensions
 *
 * @license GPL-2.0-or-later
 * @author Daniel Kinzler
 */
class DecimalValueTest extends TestCase {

	/**
	 * @see DataValueTest::getClass
	 *
	 * @return string
	 */
	public function getClass() {
		return DecimalValue::class;
	}

	public function validConstructorArgumentsProvider() {
		$argLists = [];

		$argLists[] = [ 42 ];
		$argLists[] = [ -42 ];
		$argLists[] = [ '-42' ];
		$argLists[] = [ 4.2 ];
		$argLists[] = [ -4.2 ];
		$argLists[] = [ '+4.2' ];
		$argLists[] = [ " +4.2\n" ];
		$argLists[] = [ 0 ];
		$argLists[] = [ 0.2 ];
		$argLists[] = [ '-0.42' ];
		$argLists[] = [ '-0.0' ];
		$argLists[] = [ '-0' ];
		$argLists[] = [ '+0' ];
		$argLists[] = [ '+0.0' ];
		$argLists[] = [ '+0.000' ];
		$argLists[] = [ '+1.' . str_repeat( '0', 124 ) ];
		$argLists[] = [ '+1.0' . str_repeat( ' ', 124 ) ];
		$argLists[] = [ '4.2' ];
		$argLists[] = [ " 4.2\n" ];

		return $argLists;
	}

	public function invalidConstructorArgumentsProvider() {
		$argLists = [];

		$argLists[] = [ 'foo' ];
		$argLists[] = [ '' ];
		$argLists[] = [ '++4.2' ];
		$argLists[] = [ '--4.2' ];
		$argLists[] = [ '-+4.2' ];
		$argLists[] = [ '+-4.2' ];
		$argLists[] = [ '+/-0' ];
		$argLists[] = [ '-.42' ];
		$argLists[] = [ '+.42' ];
		$argLists[] = [ '.42' ];
		$argLists[] = [ '.0' ];
		$argLists[] = [ '-00' ];
		$argLists[] = [ '−1' ];
		$argLists[] = [ '+01.2' ];
		$argLists[] = [ 'x2' ];
		$argLists[] = [ '2x' ];
		$argLists[] = [ '+0100' ];
		$argLists[] = [ false ];
		$argLists[] = [ true ];
		$argLists[] = [ null ];
		$argLists[] = [ '0x20' ];
		$argLists[] = [ '+1.' . str_repeat( '0', 125 ) ];

		return $argLists;
	}

	/**
	 * @see https://phabricator.wikimedia.org/T110728
	 * @see http://www.regular-expressions.info/anchors.html#realend
	 */
	public function testTrailingNewlineRobustness() {
		$value = DecimalValue::newFromArray( "-0.0\n" );

		$this->assertTrue( $value->isZero() );
		$this->assertSame( '+0.0', $value->getValue(), 'getValue' );
		$this->assertSame( '+0.0', $value->getArrayValue(), 'getArrayValue' );
		$this->assertSame( '+0.0', $value->__toString(), '__toString' );
		$this->assertSame( '0', $value->getFractionalPart(), 'getFractionalPart' );

		$referenceValue = unserialize( 'C:23:"DataValues\DecimalValue":11:{s:4:"+0.0";}' );
		$this->assertTrue( $value->equals( $referenceValue ), 'equal to hard-coded serialization' );
	}

	/**
	 * @dataProvider provideFloats
	 */
	public function testFloatInputs( $float, $expectedPrefix ) {
		$originalLocale = setlocale( LC_NUMERIC, '0' );
		setlocale( LC_NUMERIC, 'de_DE.utf8' );
		$value = DecimalValue::newFromArray( $float );
		setlocale( LC_NUMERIC, $originalLocale );

		$this->assertStringStartsWith( $expectedPrefix, $value->getValue(), 'getValue' );
	}

	public function provideFloats() {
		return [
			[ 0.000000002, '+0.000000002' ],
			[ 0.000003, '+0.000003' ],
			[ 0.9, '+0.9' ],
			[ 1.2, '+1.2' ],
			[ 1.5, '+1.5' ],
			[ 123E-1, '+12.3' ],
			[ 123E+1, '+1230' ],
			[ 1234567890123456, '+1234567890123' ],
			[ 1234567890123456789, '+1234567890123' ],
		];
	}

	/**
	 * @dataProvider compareProvider
	 */
	public function testCompare( DecimalValue $a, DecimalValue $b, $expected ) {
		$actual = $a->compare( $b );
		$this->assertSame( $expected, $actual );

		$actual = $b->compare( $a );
		$this->assertSame( -$expected, $actual );
	}

	public function compareProvider() {
		return [
			'zero/equal' => [ new DecimalValue( 0 ), new DecimalValue( 0 ), 0 ],
			'zero-signs/equal' => [ new DecimalValue( '+0' ), new DecimalValue( '-0' ), 0 ],
			'zero-digits/equal' => [ new DecimalValue( '+0' ), new DecimalValue( '+0.000' ), 0 ],
			'digits/equal' => [ new DecimalValue( '+2.2' ), new DecimalValue( '+2.2000' ), 0 ],
			'conversion/equal' => [ new DecimalValue( 2.5 ), new DecimalValue( '+2.50' ), 0 ],
			'negative/equal' => [ new DecimalValue( '-1.33' ), new DecimalValue( '-1.33' ), 0 ],

			'simple/smaller' => [ new DecimalValue( '+1' ), new DecimalValue( '+2' ), -1 ],
			'simple/greater' => [ new DecimalValue( '+2' ), new DecimalValue( '+1' ), 1 ],
			'negative/greater' => [ new DecimalValue( '-1' ), new DecimalValue( '-2' ), 1 ],
			'negative/smaller' => [ new DecimalValue( '-2' ), new DecimalValue( '-1' ), -1 ],
			'negative-small/greater' => [ new DecimalValue( '-0.5' ), new DecimalValue( '-0.7' ), 1 ],
			'negative-small/smaller' => [ new DecimalValue( '-0.7' ), new DecimalValue( '-0.5' ), -1 ],

			'digits/greater' => [ new DecimalValue( '+11' ), new DecimalValue( '+8' ), 1 ],
			'digits-sub/greater' => [ new DecimalValue( '+11' ), new DecimalValue( '+8.0' ), 1 ],
			'negative-digits/greater' => [ new DecimalValue( '-11' ), new DecimalValue( '-80' ), 1 ],
			'small/greater' => [ new DecimalValue( '+0.050' ), new DecimalValue( '+0.005' ), 1 ],

			'signs/greater' => [ new DecimalValue( '+1' ), new DecimalValue( '-8' ), 1 ],
			'signs/less' => [ new DecimalValue( '-8' ), new DecimalValue( '+1' ), -1 ],

			'with-and-without-point' => [ new DecimalValue( '+100' ), new DecimalValue( '+100.01' ), -1 ],
		];
	}

	/**
	 * @dataProvider getSignProvider
	 */
	public function testGetSign( DecimalValue $value, $expected ) {
		$actual = $value->getSign();
		$this->assertSame( $expected, $actual );
	}

	public function getSignProvider() {
		return [
			'zero is positive' => [ new DecimalValue( 0 ), '+' ],
			'zero is always positive' => [ new DecimalValue( '-0' ), '+' ],
			'zero is ALWAYS positive' => [ new DecimalValue( '-0.00' ), '+' ],
			'+1 is positive' => [ new DecimalValue( '+1' ), '+' ],
			'-1 is negative' => [ new DecimalValue( '-1' ), '-' ],
			'+0.01 is positive' => [ new DecimalValue( '+0.01' ), '+' ],
			'-0.01 is negative' => [ new DecimalValue( '-0.01' ), '-' ],
		];
	}

	/**
	 * @dataProvider getValueProvider
	 */
	public function testGetValue( $value, $expected ) {
		$precision = ini_set( 'serialize_precision', '2' );
		$actual = ( new DecimalValue( $value ) )->getValue();
		ini_set( 'serialize_precision', $precision );

		$this->assertSame( $expected, $actual );
	}

	public function getValueProvider() {
		$argLists = [];

		$argLists[] = [ 42, '+42' ];
		$argLists[] = [ -42, '-42' ];
		$argLists[] = [ -42.0, '-42' ];
		$argLists[] = [ '-42', '-42' ];
		$argLists[] = [ 4.5, '+4.5' ];
		$argLists[] = [ -4.5, '-4.5' ];
		$argLists[] = [ '+4.2', '+4.2' ];
		$argLists[] = [ 0, '+0' ];
		$argLists[] = [ 0.0, '+0' ];
		$argLists[] = [ 1.0, '+1' ];
		$argLists[] = [ 0.5, '+0.5' ];
		$argLists[] = [ '-0.42', '-0.42' ];
		$argLists[] = [ '-0.0', '+0.0' ];
		$argLists[] = [ '-0', '+0' ];
		$argLists[] = [ '+0.0', '+0.0' ];
		$argLists[] = [ '+0', '+0' ];
		$argLists[] = [ 2147483649, '+2147483649' ];
		$argLists[] = [ 1000000000000000, '+1000000000000000' ];
		$argLists[] = [
			1 + 1e-12 / 3,
			'+1.0000000000003333'
		];
		$argLists[] = [
			1 + 1e-13 / 3,
			'+1.0000000000000333'
		];
		$argLists[] = [
			1 + 1e-14 / 3,
			'+1.0000000000000033'
		];
		$argLists[] = [
			1 + 1e-15 / 3,
			'+1.0000000000000004'
		];
		$argLists[] = [
			1 + 1e-16 / 3,
			'+1'
		];
		$argLists[] = [
			1 - 1e-16,
			'+0.99999999999999989'
		];
		$argLists[] = [
			1 - 1e-17,
			'+1'
		];

		return $argLists;
	}

	/**
	 * @dataProvider getValueFloatProvider
	 */
	public function testGetValueFloat( DecimalValue $value, $expected ) {
		$actual = $value->getValueFloat();
		$this->assertSame( $expected, $actual );
	}

	public function getValueFloatProvider() {
		$argLists = [];

		$argLists[] = [ new DecimalValue( 42 ), 42.0 ];
		$argLists[] = [ new DecimalValue( -42 ), -42.0 ];
		$argLists[] = [ new DecimalValue( '-42' ), -42.0 ];
		$argLists[] = [ new DecimalValue( 4.5 ), 4.5 ];
		$argLists[] = [ new DecimalValue( -4.5 ), -4.5 ];
		$argLists[] = [ new DecimalValue( '+4.2' ), 4.2 ];
		$argLists[] = [ new DecimalValue( 0 ), 0.0 ];
		$argLists[] = [ new DecimalValue( 0.5 ), 0.5 ];
		$argLists[] = [ new DecimalValue( '-0.42' ), -0.42 ];
		$argLists[] = [ new DecimalValue( '-0.0' ), 0.0 ];
		$argLists[] = [ new DecimalValue( '-0' ), 0.0 ];
		$argLists[] = [ new DecimalValue( '+0.0' ), 0.0 ];
		$argLists[] = [ new DecimalValue( '+0' ), 0.0 ];

		return $argLists;
	}

	/**
	 * @dataProvider getGetIntegerPartProvider
	 */
	public function testGetIntegerPart( DecimalValue $value, $expected ) {
		$actual = $value->getIntegerPart();
		$this->assertSame( $expected, $actual );
	}

	public function getGetIntegerPartProvider() {
		return [
			[ new DecimalValue( '+0' ), '0' ],
			[ new DecimalValue( '-0.0' ), '0' ],
			[ new DecimalValue( '+10' ), '10' ],
			[ new DecimalValue( '-10' ), '10' ],
			[ new DecimalValue( '+10.663' ), '10' ],
			[ new DecimalValue( '-10.001' ), '10' ],
			[ new DecimalValue( '+0.01' ), '0' ],
		];
	}

	/**
	 * @dataProvider getGetIntegerPartProvider
	 */
	public function testGetFractionalPart( DecimalValue $value, $expected ) {
		$actual = $value->getIntegerPart();
		$this->assertSame( $expected, $actual );
	}

	public function getGetFractionalPartProvider() {
		return [
			[ new DecimalValue( '+0' ), '' ],
			[ new DecimalValue( '-0.0' ), '0' ],
			[ new DecimalValue( '+10' ), '' ],
			[ new DecimalValue( '+10.663' ), '663' ],
			[ new DecimalValue( '-10.001' ), '001' ],
			[ new DecimalValue( '+0.01' ), '01' ],
		];
	}

	/**
	 * @dataProvider computeComplementProvider
	 */
	public function testComputeComplement( DecimalValue $value, $expected ) {
		$complement = $value->computeComplement();
		$this->assertSame( $expected, $complement->getValue() );

		$actual = $complement->computeComplement();
		$this->assertSame( $value->getValue(), $actual->getValue() );
	}

	public function computeComplementProvider() {
		return [
			[ new DecimalValue( '+0' ), '+0' ],
			[ new DecimalValue( '+0.00' ), '+0.00' ],
			[ new DecimalValue( '+1' ), '-1' ],
			[ new DecimalValue( '+100.663' ), '-100.663' ],
			[ new DecimalValue( '-0.001' ), '+0.001' ],
		];
	}

	/**
	 * @dataProvider computeComputeAbsolute
	 */
	public function testComputeAbsolute( DecimalValue $value, $expected ) {
		$absolute = $value->computeAbsolute();
		$this->assertSame( $expected, $absolute->getValue() );

		$actual = $absolute->computeAbsolute();
		$this->assertSame( $absolute->getValue(), $actual->getValue() );
	}

	public function computeComputeAbsolute() {
		return [
			[ new DecimalValue( '+0' ), '+0' ],
			[ new DecimalValue( '+1' ), '+1' ],
			[ new DecimalValue( '-1' ), '+1' ],
			[ new DecimalValue( '+100.663' ), '+100.663' ],
			[ new DecimalValue( '-100.663' ), '+100.663' ],
			[ new DecimalValue( '+0.001' ), '+0.001' ],
			[ new DecimalValue( '-0.001' ), '+0.001' ],
		];
	}

	/**
	 * @dataProvider isZeroProvider
	 */
	public function testIsZero( DecimalValue $value, $expected ) {
		$actual = $value->isZero();
		$this->assertSame( $expected, $actual );
	}

	public function isZeroProvider() {
		return [
			[ new DecimalValue( '+0' ), true ],
			[ new DecimalValue( '-0.00' ), true ],

			[ new DecimalValue( '+1' ),       false ],
			[ new DecimalValue( '+100.663' ), false ],
			[ new DecimalValue( '-0.001' ),   false ],
		];
	}

	/**
	 * @dataProvider provideGetTrim
	 */
	public function testGetTrim( DecimalValue $value, DecimalValue $expected ) {
		$actual = $value->getTrimmed();
		$this->assertSame( $expected->getValue(), $actual->getValue() );
	}

	public function provideGetTrim() {
		return [
			[ new DecimalValue( '+8' ),     new DecimalValue( '+8' ) ],
			[ new DecimalValue( '+80' ),    new DecimalValue( '+80' ) ],
			[ new DecimalValue( '+800' ),   new DecimalValue( '+800' ) ],
			[ new DecimalValue( '+0' ),     new DecimalValue( '+0' ) ],
			[ new DecimalValue( '+0.0' ),   new DecimalValue( '+0' ) ],
			[ new DecimalValue( '+10.00' ), new DecimalValue( '+10' ) ],
			[ new DecimalValue( '-0.1' ),   new DecimalValue( '-0.1' ) ],
			[ new DecimalValue( '-0.10' ),  new DecimalValue( '-0.1' ) ],
			[ new DecimalValue( '-0.010' ), new DecimalValue( '-0.01' ) ],
			[ new DecimalValue( '-0.001' ), new DecimalValue( '-0.001' ) ],
		];
	}

}
