<?php

namespace ValueFormatters\Test;

use DataValues\QuantityValue;
use DataValues\UnboundedQuantityValue;
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
			QuantityFormatter::OPT_SHOW_UNCERTAINTY_MARGIN => false,
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
			'+0.0/wm' => array( QuantityValue::newFromNumber( '+0.0', '°', '+0.1', '-0.1' ), '0±0.1 °', $withMargin ),
			'+0.0/xr' => array( QuantityValue::newFromNumber( '+0.0', '°', '+0.1', '-0.1' ), '0.0 °', $exactRounding ),

			'-1205/nm' => array( QuantityValue::newFromNumber( '-1205', 'm', '-1105', '-1305' ), '-1200 m', $noMargin ),
			'-1205/wm' => array( QuantityValue::newFromNumber( '-1205', 'm', '-1105', '-1305' ), '-1205±100 m', $withMargin ),
			'-1205/nr' => array( QuantityValue::newFromNumber( '-1205', 'm', '-1105', '-1305' ), '-1205±100 m', $noRounding ),
			'-1205/xr' => array( QuantityValue::newFromNumber( '-1205', 'm', '-1105', '-1305' ), '-1205 m', $exactRounding ),
			'-1205/nu' => array( QuantityValue::newFromNumber( '-1205', 'm', '-1105', '-1305' ), '-1205±100', $noUnit ),

			'+3.025/nm' => array( QuantityValue::newFromNumber( '+3.025', '1', '+3.02744', '+3.0211' ), '3.025', $noMargin ),
			'+3.025/wm' => array( QuantityValue::newFromNumber( '+3.025', '1', '+3.02744', '+3.0211' ), '3.025±0.0039', $withMargin ),
			'+3.025/xr' => array( QuantityValue::newFromNumber( '+3.025', '1', '+3.02744', '+3.0211' ), '3.03', $exactRounding ),

			'+3.125/nr' => array( QuantityValue::newFromNumber( '+3.125', '1', '+3.2', '+3.0' ), '3.125±0.125', $noRounding ),
			'+3.125/xr' => array( QuantityValue::newFromNumber( '+3.125', '1', '+3.2', '+3.0' ), '3.13', $exactRounding ),

			'+3.125/fs' => array( QuantityValue::newFromNumber( '+3.125', '1', '+3.2', '+3.0' ), '+3.13', $forceSign ),

			// Unbounded quantities with different options
			'UB: +0.0/nm' => array( UnboundedQuantityValue::newFromNumber( '+0.0', '°' ), '0.0 °', $noMargin ),
			'UB: +0.0/wm' => array( UnboundedQuantityValue::newFromNumber( '+0.0', '°' ), '0.0 °', $withMargin ),
			'UB: +0.0/xr' => array( UnboundedQuantityValue::newFromNumber( '+0.0', '°' ), '0.0 °', $exactRounding ),
			'UB: +5.021/nm' => array( UnboundedQuantityValue::newFromNumber( '+5.021', '°' ), '5.021 °', $noMargin ),
			'UB: +5.021/wm' => array( UnboundedQuantityValue::newFromNumber( '+5.021', '°' ), '5.021 °', $withMargin ),
			'UB: +5.021/xr' => array( UnboundedQuantityValue::newFromNumber( '+5.021', '°' ), '5.02 °', $exactRounding ),
			'UB: +3.125/fs' => array( UnboundedQuantityValue::newFromNumber( '+3.125', '1' ), '+3.125', $forceSign ),

			// Unbounded quantities with enforced, exact rounding
			array( UnboundedQuantityValue::newFromNumber( '+0.00155', '1' ), '0.00', $exactRounding ),
			array( UnboundedQuantityValue::newFromNumber( '+0.0155', '1' ), '0.02', $exactRounding ),
			array( UnboundedQuantityValue::newFromNumber( '+0.155', '1' ), '0.16', $exactRounding ),
			array( UnboundedQuantityValue::newFromNumber( '+1.55', '1' ), '1.55', $exactRounding ),
			array( UnboundedQuantityValue::newFromNumber( '+15.5', '1' ), '15.5', $exactRounding ),
			array( UnboundedQuantityValue::newFromNumber( '+155', '1' ), '155', $exactRounding ),

			// Default options with different margins
			'24+-000.01' => array( QuantityValue::newFromNumber( '+24', '1', '+24.01', '+23.99' ), '24±0.01' ),
			'24+-000.10' => array( QuantityValue::newFromNumber( '+24', '1', '+24.1', '+23.9' ), '24±0.1' ),
			'24+-001.00' => array( QuantityValue::newFromNumber( '+24', '1', '+25', '+23' ), '24±1' ),
			'24+-010.00' => array( QuantityValue::newFromNumber( '+24', '1', '+34', '+14' ), '24±10' ),
			'24+-100.00' => array( QuantityValue::newFromNumber( '+24', '1', '+124', '-76' ), '24±100' ),

			// Rounding with a fixed +/-1 margin
			array( QuantityValue::newFromNumber( '+1.44', '1', '+2.44', '+0.44' ), '1', $noMargin ),
			array( QuantityValue::newFromNumber( '+1.45', '1', '+2.45', '+0.45' ), '1', $noMargin ),
			array( QuantityValue::newFromNumber( '+1.49', '1', '+2.49', '+0.49' ), '1', $noMargin ),
			array( QuantityValue::newFromNumber( '+1.50', '1', '+2.50', '+0.50' ), '2', $noMargin ),
			array( QuantityValue::newFromNumber( '+2.50', '1', '+3.50', '+1.50' ), '3', $noMargin ),

			// Rounding with different margins
			'1.55+/-0.09' => array( QuantityValue::newFromNumber( '+1.55', '1', '+1.64', '+1.46' ), '1.55', $noMargin ),
			'1.55+/-0.1' => array( QuantityValue::newFromNumber( '+1.55', '1', '+1.65', '+1.45' ), '1.6', $noMargin ),
			'1.55+/-0.49' => array( QuantityValue::newFromNumber( '+1.55', '1', '+2.04', '+1.06' ), '1.6', $noMargin ),
			'1.55+/-0.5' => array( QuantityValue::newFromNumber( '+1.55', '1', '+2.05', '+1.05' ), '1.6', $noMargin ),
			'1.55+/-0.99' => array( QuantityValue::newFromNumber( '+1.55', '1', '+2.54', '+0.56' ), '1.6', $noMargin ),
			'1.55+/-1' => array( QuantityValue::newFromNumber( '+1.55', '1', '+2.55', '+0.55' ), '2', $noMargin ),
			// FIXME: We should probably never round to zero as it is confusing.
			'1.55+/-10' => array( QuantityValue::newFromNumber( '+1.55', '1', '+11.55', '-8.45' ), '0', $noMargin ),

			// Do not mess with the value when the margin is rendered
			array( QuantityValue::newFromNumber( '+1500', '1', '+2500', '+500' ), '1500±1000' ),
			array( QuantityValue::newFromNumber( '+2', '1', '+2.005', '+1.995' ), '2±0.005' ),
			array( QuantityValue::newFromNumber( '+1.5', '1', '+2.5', '+0.5' ), '1.5±1' ),
			array( QuantityValue::newFromNumber( '+1.0005', '1', '+1.0015', '+0.9995' ), '1.0005±0.001' ),
			array( QuantityValue::newFromNumber( '+0.0015', '1', '+0.0025', '+0.0005' ), '0.0015±0.001' ),

			/**
			 * Never mess with the margin
			 * @see https://phabricator.wikimedia.org/T58892
			 */
			array( QuantityValue::newFromNumber( '+2', '1', '+3.5', '+0.5' ), '2±1.5' ),
			array( QuantityValue::newFromNumber( '+2', '1', '+2.016', '+1.984' ), '2±0.016' ),
			array( QuantityValue::newFromNumber( '+2', '1', '+2.0015', '+1.9985' ), '2±0.0015' ),
			array( QuantityValue::newFromNumber( '+0.0015', '1', '+0.003', '+0' ), '0.0015±0.0015' ),
			array( QuantityValue::newFromNumber( '+2.0011', '1', '+2.0022', '+2' ), '2.0011±0.0011' ),
			array( QuantityValue::newFromNumber( '+2.0099', '1', '+2.0198', '+2' ), '2.0099±0.0099' ),

			// IEEE edge cases
			array(
				QuantityValue::newFromNumber(
					'+1.00000000000000015',
					'1',
					'+1.00000000000000025',
					'+1.00000000000000005'
				),
				'1.00000000000000015±0.0000000000000001'
			),
			'0.2 / 3 * 3' => array(
				QuantityValue::newFromNumber(
					'+0.2000000000000000111',
					'1',
					'+0.2000000000000000111',
					'+0.2000000000000000111'
				),
				'0.2000000000000000111'
			),
			'8 - 6.4' => array(
				QuantityValue::newFromNumber(
					'+1.59999999999999964473',
					'1',
					'+1.59999999999999964473',
					'+1.59999999999999964473'
				),
				'1.59999999999999964473'
			),
		);
	}

	public function testFormatWithFormatString() {
		$formatter = $this->getQuantityFormatter( null, '<$2>$1' );
		$value = QuantityValue::newFromNumber( '+5', 'USD' );
		$formatted = $formatter->format( $value );
		$this->assertSame( '<USD>5', $formatted );
	}

}
