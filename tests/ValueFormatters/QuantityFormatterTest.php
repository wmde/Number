<?php

namespace ValueFormatters\Test;

use DataValues\QuantityValue;
use ValueFormatters\DecimalFormatter;
use ValueFormatters\FormatterOptions;
use ValueFormatters\QuantityFormatter;

/**
 * @covers ValueFormatters\QuantityFormatter
 *
 * @group ValueFormatters
 * @group DataValueExtensions
 *
 * @licence GNU GPL v2+
 * @author Daniel Kinzler
 */
class QuantityFormatterTest extends ValueFormatterTestBase {

	/**
	 * @deprecated since 0.2, just use getInstance.
	 */
	protected function getFormatterClass() {
		throw new \LogicException( 'Should not be called, use getInstance' );
	}

	/**
	 * @see ValueFormatterTestBase::getInstance
	 *
	 * @param FormatterOptions|null $options
	 *
	 * @return QuantityFormatter
	 */
	protected function getInstance( FormatterOptions $options = null ) {
		return $this->getQuantityFormatter( $options );
	}

	/**
	 * @param FormatterOptions|null $options
	 * @param string|null $quantityWithUnitFormat
	 *
	 * @return QuantityFormatter
	 */
	private function getQuantityFormatter(
		FormatterOptions $options = null,
		$quantityWithUnitFormat = null
	) {
		$vocabularyUriFormatter = $this->getMock( 'ValueFormatters\ValueFormatter' );
		$vocabularyUriFormatter->expects( $this->any() )
			->method( 'format' )
			->will( $this->returnCallback( function( $unit ) {
				return $unit === '1' ? null : $unit;
			} ) );

		return new QuantityFormatter(
			$options,
			new DecimalFormatter( $options ),
			$vocabularyUriFormatter,
			$quantityWithUnitFormat
		);
	}

	/**
	 * @see ValueFormatterTestBase::validProvider
	 */
	public function validProvider() {
		$noMargin = new FormatterOptions( array(
			QuantityFormatter::OPT_SHOW_UNCERTAINTY_MARGIN => false
		) );

		$withMargin = new FormatterOptions( array(
			QuantityFormatter::OPT_SHOW_UNCERTAINTY_MARGIN => true
		) );

		$noRounding = new FormatterOptions( array(
			QuantityFormatter::OPT_SHOW_UNCERTAINTY_MARGIN => true,
			QuantityFormatter::OPT_APPLY_ROUNDING => false
		) );

		$exactRounding = new FormatterOptions( array(
			QuantityFormatter::OPT_SHOW_UNCERTAINTY_MARGIN => true,
			QuantityFormatter::OPT_APPLY_ROUNDING => -2
		) );

		$forceSign = new FormatterOptions( array(
			QuantityFormatter::OPT_SHOW_UNCERTAINTY_MARGIN => false,
			DecimalFormatter::OPT_FORCE_SIGN => true,
		) );

		$noUnit = new FormatterOptions( array(
			QuantityFormatter::OPT_APPLY_UNIT => false,
		) );

		return array(
			'+0/nm' => array( QuantityValue::newFromNumber( '+0', '1', '+0', '+0' ), '0', $noMargin ),
			'+0/wm' => array( QuantityValue::newFromNumber( '+0', '1', '+0', '+0' ), '0', $withMargin ),

			'+0.0/nm' => array( QuantityValue::newFromNumber( '+0.0', '°', '+0.1', '-0.1' ), '0.0 °', $noMargin ),
			'+0.0/wm' => array( QuantityValue::newFromNumber( '+0.0', '°', '+0.1', '-0.1' ), '0.0±0.1 °', $withMargin ),
			'+0.0/xr' => array( QuantityValue::newFromNumber( '+0.0', '°', '+0.1', '-0.1' ), '0.00±0.10 °', $exactRounding ),

			'-1205/nm' => array( QuantityValue::newFromNumber( '-1205', 'm', '-1105', '-1305' ), '-1200 m', $noMargin ),
			'-1205/wm' => array( QuantityValue::newFromNumber( '-1205', 'm', '-1105', '-1305' ), '-1200±100 m', $withMargin ),
			'-1205/nr' => array( QuantityValue::newFromNumber( '-1205', 'm', '-1105', '-1305' ), '-1205±100 m', $noRounding ),
			'-1205/xr' => array( QuantityValue::newFromNumber( '-1205', 'm', '-1105', '-1305' ), '-1205.00±100.00 m', $exactRounding ),
			'-1205/nu' => array( QuantityValue::newFromNumber( '-1205', 'm', '-1105', '-1305' ), '-1200±100', $noUnit ),

			'+3.025/nm' => array( QuantityValue::newFromNumber( '+3.025', '1', '+3.02744', '+3.0211' ), '3.025', $noMargin ),
			'+3.025/wm' => array( QuantityValue::newFromNumber( '+3.025', '1', '+3.02744', '+3.0211' ), '3.025±0.004', $withMargin ),
			'+3.025/xr' => array( QuantityValue::newFromNumber( '+3.025', '1', '+3.02744', '+3.0211' ), '3.03', $exactRounding ), // TODO: never round to 0! See bug #56892

			'+3.125/nr' => array( QuantityValue::newFromNumber( '+3.125', '1', '+3.2', '+3.0' ), '3.125±0.125', $noRounding ),
			'+3.125/xr' => array( QuantityValue::newFromNumber( '+3.125', '1', '+3.2', '+3.0' ), '3.13±0.13', $exactRounding ),

			'+3.125/fs' => array( QuantityValue::newFromNumber( '+3.125', '1', '+3.2', '+3.0' ), '+3.13', $forceSign ),
		);
	}

	public function testFormatWithFormatString() {
		$formatter = $this->getQuantityFormatter( null, '<$2>$1' );
		$value = QuantityValue::newFromNumber( '+5', 'USD' );
		$formatted = $formatter->format( $value );
		$this->assertSame( '<USD>5', $formatted );
	}

}
