<?php

namespace ValueFormatters\Test;

use DataValues\BoundedQuantityValue;
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
 * @license GPL-2.0+
 * @author Daniel Kinzler
 */
class QuantityFormatterTest extends ValueFormatterTestBase {

	/**
	 * @deprecated since DataValues Interfaces 0.2, just use getInstance.
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
			QuantityFormatter::OPT_SHOW_UNCERTAINTY_MARGIN
				=> QuantityFormatter::SHOW_UNCERTAINTY_MARGIN_NEVER
		) );

		$withMargin = new FormatterOptions( array(
			QuantityFormatter::OPT_SHOW_UNCERTAINTY_MARGIN
				=> QuantityFormatter::SHOW_UNCERTAINTY_MARGIN_IF_KNOWN
		) );

		$withNonZeroMargin = new FormatterOptions( array(
			QuantityFormatter::OPT_SHOW_UNCERTAINTY_MARGIN
			=> QuantityFormatter::SHOW_UNCERTAINTY_MARGIN_IF_NOT_ZERO
		) );

		$noRounding = new FormatterOptions( array(
			QuantityFormatter::OPT_SHOW_UNCERTAINTY_MARGIN
				=> QuantityFormatter::SHOW_UNCERTAINTY_MARGIN_IF_KNOWN,
			QuantityFormatter::OPT_APPLY_ROUNDING => false
		) );

		$exactRounding = new FormatterOptions( array(
			QuantityFormatter::OPT_SHOW_UNCERTAINTY_MARGIN
				=> QuantityFormatter::SHOW_UNCERTAINTY_MARGIN_IF_KNOWN,
			QuantityFormatter::OPT_APPLY_ROUNDING => -2
		) );

		$forceSign = new FormatterOptions( array(
			QuantityFormatter::OPT_SHOW_UNCERTAINTY_MARGIN
				=> QuantityFormatter::SHOW_UNCERTAINTY_MARGIN_NEVER,
			DecimalFormatter::OPT_FORCE_SIGN => true,
		) );

		$noUnit = new FormatterOptions( array(
			QuantityFormatter::OPT_APPLY_UNIT => false,
		) );

		return array(
			'+0/nm' => array( BoundedQuantityValue::newFromNumber( '+0', '1', '+0', '+0' ), '0', $noMargin ),
			'+0/wm' => array( BoundedQuantityValue::newFromNumber( '+0', '1', '+0', '+0' ), '0±0', $withMargin ),
			'+0/zm' => array( BoundedQuantityValue::newFromNumber( '+0', '1', '+0', '+0' ), '0', $withNonZeroMargin ),

			'+0.0/nm' => array( BoundedQuantityValue::newFromNumber( '+0.0', '°', '+0.1', '-0.1' ), '0.0 °', $noMargin ),
			'+0.0/wm' => array( BoundedQuantityValue::newFromNumber( '+0.0', '°', '+0.1', '-0.1' ), '0.0±0.1 °', $withMargin ),
			'+0.0/xr' => array( BoundedQuantityValue::newFromNumber( '+0.0', '°', '+0.1', '-0.1' ), '0.00±0.10 °', $exactRounding ),

			'-1205/nm' => array( BoundedQuantityValue::newFromNumber( '-1205', 'm', '-1105', '-1305' ), '-1200 m', $noMargin ),
			'-1205/wm' => array( BoundedQuantityValue::newFromNumber( '-1205', 'm', '-1105', '-1305' ), '-1200±100 m', $withMargin ),
			'-1205/nr' => array( BoundedQuantityValue::newFromNumber( '-1205', 'm', '-1105', '-1305' ), '-1205±100 m', $noRounding ),
			'-1205/xr' => array( BoundedQuantityValue::newFromNumber( '-1205', 'm', '-1105', '-1305' ), '-1205.00±100.00 m', $exactRounding ),
			'-1205/nu' => array( BoundedQuantityValue::newFromNumber( '-1205', 'm', '-1105', '-1305' ), '-1200±100', $noUnit ),

			'+3.025/nm' => array( BoundedQuantityValue::newFromNumber( '+3.025', '1', '+3.02744', '+3.0211' ), '3.025', $noMargin ),
			'+3.025/wm' => array( BoundedQuantityValue::newFromNumber( '+3.025', '1', '+3.02744', '+3.0211' ), '3.025±0.004', $withMargin ),
			'+3.025/zm' => array( BoundedQuantityValue::newFromNumber( '+3.025', '1', '+3.02744', '+3.0211' ), '3.025±0.004', $withNonZeroMargin ),
			'+3.025/xr' => array( BoundedQuantityValue::newFromNumber( '+3.025', '1', '+3.02744', '+3.0211' ), '3.03±0.00', $exactRounding ), // TODO: never round to 0! See bug #56892

			'+3.125/nr' => array( BoundedQuantityValue::newFromNumber( '+3.125', '1', '+3.2', '+3.0' ), '3.125±0.125', $noRounding ),
			'+3.125/xr' => array( BoundedQuantityValue::newFromNumber( '+3.125', '1', '+3.2', '+3.0' ), '3.13±0.13', $exactRounding ),

			'+3.125/fs' => array( BoundedQuantityValue::newFromNumber( '+3.125', '1', '+3.2', '+3.0' ), '+3.13', $forceSign ),

			'UB: +0.0/nm' => array( QuantityValue::newFromNumber( '+0.0', '°' ), '0.0 °', $noMargin ),
			'UB: +0.0/wm' => array( QuantityValue::newFromNumber( '+0.0', '°' ), '0.0 °', $withMargin ),
			'UB: +0.0/zm' => array( QuantityValue::newFromNumber( '+0.0', '°' ), '0.0 °', $withNonZeroMargin ),
			'UB: +0.0/xr' => array( QuantityValue::newFromNumber( '+0.0', '°' ), '0.00 °', $exactRounding ),
			'UB: +5.020/nm' => array( QuantityValue::newFromNumber( '+5.020', '°' ), '5.020 °', $noMargin ),
			'UB: +5.020/wm' => array( QuantityValue::newFromNumber( '+5.020', '°' ), '5.020 °', $withMargin ),
			'UB: +5.020/zm' => array( QuantityValue::newFromNumber( '+5.020', '°' ), '5.020 °', $withNonZeroMargin ),
			'UB: +5.020/xr' => array( QuantityValue::newFromNumber( '+5.020', '°' ), '5.02 °', $exactRounding ),
			'UB: +3.125/fs' => array( QuantityValue::newFromNumber( '+3.125', '1' ), '+3.125', $forceSign ),
		);
	}

	public function testFormatWithFormatString() {
		$formatter = $this->getQuantityFormatter( null, '<$2>$1' );
		$value = QuantityValue::newFromNumber( '+5', 'USD' );
		$formatted = $formatter->format( $value );
		$this->assertSame( '<USD>5', $formatted );
	}

}
