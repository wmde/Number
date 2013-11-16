<?php

namespace ValueFormatters\Test;

use DataValues\QuantityValue;
use ValueFormatters\DecimalFormatter;
use ValueFormatters\FormatterOptions;
use ValueFormatters\QuantityFormatter;
use ValueFormatters\ValueFormatter;

/**
 * @covers ValueFormatters\QuantityFormatter
 *
 * @since 0.1
 *
 * @group ValueFormatters
 * @group DataValueExtensions
 *
 * @licence GNU GPL v2+
 * @author Daniel Kinzler
 */
class QuantityFormatterTest extends ValueFormatterTestBase {

	/**
	 * @see ValueFormatterTestBase::validProvider
	 *
	 * @since 0.1
	 *
	 * @return array
	 */
	public function validProvider() {
		$noMargin = new FormatterOptions( array(
			QuantityFormatter::OPT_SHOW_UNCERTAINTY_MARGIN => false
		) );

		$withMargin = new FormatterOptions( array(
			QuantityFormatter::OPT_SHOW_UNCERTAINTY_MARGIN => true
		) );

		$noRounding= new FormatterOptions( array(
			QuantityFormatter::OPT_SHOW_UNCERTAINTY_MARGIN => true,
			QuantityFormatter::OPT_APPLY_ROUNDING => false
		) );

		$exactRounding= new FormatterOptions( array(
			QuantityFormatter::OPT_SHOW_UNCERTAINTY_MARGIN => true,
			QuantityFormatter::OPT_APPLY_ROUNDING => -2
		) );

		return array(
			'+0/nm' => array( QuantityValue::newFromNumber( '+0', '1', '+0', '+0' ), '0', $noMargin ),
			'+0/wm' => array( QuantityValue::newFromNumber( '+0', '1', '+0', '+0' ), '0', $withMargin ),

			'+0.0/nm' => array( QuantityValue::newFromNumber( '+0.0', '°', '+0.1', '-0.1' ), '0.0°', $noMargin ),
			'+0.0/wm' => array( QuantityValue::newFromNumber( '+0.0', '°', '+0.1', '-0.1' ), '0.0±0.1°', $withMargin ),
			'+0.0/xr' => array( QuantityValue::newFromNumber( '+0.0', '°', '+0.1', '-0.1' ), '0.0±0.10°', $exactRounding ), //edge case: could be ±0.10 or just ±0.1

			'-1205/nm' => array( QuantityValue::newFromNumber( '-1205', 'm', '-1105', '-1305' ), '-1200m', $noMargin ),
			'-1205/wm' => array( QuantityValue::newFromNumber( '-1205', 'm', '-1105', '-1305' ), '-1200±100m', $withMargin ),
			'-1205/nr' => array( QuantityValue::newFromNumber( '-1205', 'm', '-1105', '-1305' ), '-1205±100.0m', $noRounding ), // edge case: could be ±100.0m or just ±100m
			'-1205/xr' => array( QuantityValue::newFromNumber( '-1205', 'm', '-1105', '-1305' ), '-1205±100.0m', $exactRounding ), // edge case: could be ±100.0m or just ±100m

			'+3.025/nm' => array( QuantityValue::newFromNumber( '+3.025', '1', '+3.02744', '+3.0211' ), '3.025', $noMargin ),
			'+3.025/wm' => array( QuantityValue::newFromNumber( '+3.025', '1', '+3.02744', '+3.0211' ), '3.025±0.004', $withMargin ),
			'+3.025/xr' => array( QuantityValue::newFromNumber( '+3.025', '1', '+3.02744', '+3.0211' ), '3.03', $exactRounding ), // TODO: never round to 0! See bug #56892

			'+3.125/nr' => array( QuantityValue::newFromNumber( '+3.125', '1', '+3.2', '+3.0' ), '3.125±0.125', $noRounding ),
			'+3.125/xr' => array( QuantityValue::newFromNumber( '+3.125', '1', '+3.2', '+3.0' ), '3.13±0.13', $exactRounding ),
		);
	}

	/**
	 * @see ValueFormatterTestBase::getFormatterClass
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	protected function getFormatterClass() {
		return 'ValueFormatters\QuantityFormatter';
	}

	/**
	 * @see ValueFormatterTestBase::getInstance
	 *
	 * @param FormatterOptions $options
	 *
	 * @return ValueFormatter
	 */
	protected function getInstance( FormatterOptions $options ) {
		$decimalFormatter = new DecimalFormatter( $options );
		$class = $this->getFormatterClass();
		return new $class( $decimalFormatter, $options );
	}
}
