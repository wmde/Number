<?php
namespace DataValues\Tests;

use DataValues\MatrixValue;
use DataValues\NumberValue;
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
        $row1 = array( new NumberValue( 1 ) );
        $row2 = array( new NumberValue( 1 ), new NumberValue( 2 ) );
		$argLists = array();
		$argLists[] = array( array( $row1 ) );
		$argLists[] = array( array( $row1, $row1 ) );
		$argLists[] = array( array( $row2, $row2 ) );
		$argLists[] = array( array( $row1, $row1, $row1 ) );
		return $argLists;
	}

	public function invalidConstructorArgumentsProvider() {
        $row1 = array( new NumberValue( 1 ) );
        $row2 = array( new NumberValue( 1 ), new NumberValue( 2 ) );
		$argLists = array();
		$argLists[] = array( array() );
		$argLists[] = array( array( $row1, $row2 ) );
		return $argLists;
	}
}
