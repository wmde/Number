<?php
namespace DataValues\Tests;

use DataValues\NumberValue;
use DataValues\MatrixValue;
/**
 * @covers DataValues\MatrixValue
 *
 * @group DataValue
 * @group DataValueExtensions
 *
 * @licence GNU GPL v2+
 * @author Andrius Merkys < andrius.merkys@gmail.com >
 */
class MatrixValueTest extends DataValueTest {
	/**
	 * @see DataValueTest::getClass
	 *
	 * @return string
	 */
	public function getClass() {
		return 'DataValues\MatrixValue';
	}

	public function validConstructorArgumentsProvider() {
		$argLists = array();
		$argLists[] = array( array( new NumberValue( 1 ) ), 1, 1 );
		$argLists[] = array( array( new NumberValue( 1 ),
                                    new NumberValue( 2 ),
                                    new NumberValue( 3 ),
                                    new NumberValue( 4 ) ), 2, 2 );
		return $argLists;
	}
	public function invalidConstructorArgumentsProvider() {
		$argLists = array();
		$argLists[] = array( new NumberValue( 1 ), 2, 2 );
		return $argLists;
	}
}
