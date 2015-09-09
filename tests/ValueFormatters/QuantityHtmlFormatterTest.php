<?php

namespace ValueFormatters\Test;

use DataValues\QuantityValue;
use ValueFormatters\DecimalFormatter;
use ValueFormatters\FormatterOptions;
use ValueFormatters\QuantityHtmlFormatter;

/**
 * @covers ValueFormatters\QuantityHtmlFormatter
 *
 * @licence GNU GPL v2+
 * @author Thiemo Mättig
 */
class QuantityHtmlFormatterTest extends ValueFormatterTestBase {

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
	 * @return QuantityHtmlFormatter
	 */
	protected function getInstance( FormatterOptions $options = null ) {
		return $this->getQuantityHtmlFormatter( $options );
	}

	/**
	 * @param FormatterOptions|null $options
	 * @param DecimalFormatter|null $decimalFormatter
	 * @param string|null $quantityWithUnitFormat
	 *
	 * @return QuantityHtmlFormatter
	 */
	private function getQuantityHtmlFormatter(
		FormatterOptions $options = null,
		DecimalFormatter $decimalFormatter = null,
		$quantityWithUnitFormat = null
	) {
		$vocabularyUriFormatter = $this->getMock( 'ValueFormatters\ValueFormatter' );
		$vocabularyUriFormatter->expects( $this->any() )
			->method( 'format' )
			->will( $this->returnCallback( function( $unit ) {
				return $unit === '1' ? null : $unit;
			} ) );

		return new QuantityHtmlFormatter(
			$options,
			$decimalFormatter,
			$vocabularyUriFormatter,
			$quantityWithUnitFormat
		);
	}

	/**
	 * @see ValueFormatterTestBase::validProvider
	 */
	public function validProvider() {
		return array(
			'Unit 1' => array(
				QuantityValue::newFromNumber( '+2', '1', '+3', '+1' ),
				'2±1'
			),
			'String unit' => array(
				QuantityValue::newFromNumber( '+2', 'Ultrameter', '+3', '+1' ),
				'2±1 <span class="wb-unit">Ultrameter</span>'
			),
			'HTML injection' => array(
				QuantityValue::newFromNumber( '+2', '<b>injection</b>', '+2', '+2' ),
				'2 <span class="wb-unit">&lt;b&gt;injection&lt;/b&gt;</span>'
			),
		);
	}

	public function testFormatWithFormatString() {
		$formatter = $this->getQuantityHtmlFormatter( null, null, '$2&thinsp;$1' );
		$value = QuantityValue::newFromNumber( '+5', 'USD' );
		$formatted = $formatter->format( $value );
		$this->assertSame( '<span class="wb-unit">USD</span>&thinsp;5', $formatted );
	}

	/**
	 * @dataProvider applyUnitOptionProvider
	 */
	public function testGivenHtmlCharacters_formatEscapesHtmlCharacters(
		FormatterOptions $options = null,
		$unit,
		$expected
	) {
		$decimalFormatter = $this->getMock( 'ValueFormatters\DecimalFormatter' );
		$decimalFormatter->expects( $this->any() )
			->method( 'format' )
			->will( $this->returnValue( '<b>+2</b>' ) );

		$formatter = $this->getQuantityHtmlFormatter( $options, $decimalFormatter );
		$formatted = $formatter->format( QuantityValue::newFromNumber( '+2', $unit ) );
		$this->assertSame( $expected, $formatted );
	}

	public function applyUnitOptionProvider() {
		$noUnit = new FormatterOptions();
		$noUnit->setOption( QuantityHtmlFormatter::OPT_APPLY_UNIT, false );

		return array(
			'Disabled without unit' => array(
				$noUnit,
				'1',
				'&lt;b&gt;+2&lt;/b&gt;'
			),
			'Disabled with unit' => array(
				$noUnit,
				'<b>m</b>',
				'&lt;b&gt;+2&lt;/b&gt;'
			),
			'Default without unit' => array(
				null,
				'1',
				'&lt;b&gt;+2&lt;/b&gt;'
			),
			'Default with unit' => array(
				null,
				'<b>m</b>',
				'&lt;b&gt;+2&lt;/b&gt; <span class="wb-unit">&lt;b&gt;m&lt;/b&gt;</span>'
			),
		);
	}

}
