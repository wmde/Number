<?php

namespace ValueFormatters\Test;

use DataValues\DecimalValue;
use DataValues\QuantityValue;
use DataValues\UnboundedQuantityValue;
use ValueFormatters\DecimalFormatter;
use ValueFormatters\FormatterOptions;
use ValueFormatters\QuantityHtmlFormatter;

/**
 * @covers ValueFormatters\QuantityHtmlFormatter
 *
 * @license GPL-2.0+
 * @author Thiemo Mättig
 */
class QuantityHtmlFormatterTest extends ValueFormatterTestBase {

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
	 * @param string $className
	 * @param int $uncertaintyMargin
	 *
	 * @return QuantityValue
	 */
	private function newQuantityValue( $className, $uncertaintyMargin = 0 ) {
		$quantity = $this->getMockBuilder( $className )
			->disableOriginalConstructor()
			->getMock();

		$quantity->expects( $this->any() )
			->method( 'getAmount' )
			->will( $this->returnValue( new DecimalValue( 2 ) ) );
		$quantity->expects( $this->any() )
			->method( 'getUncertaintyMargin' )
			->will( $this->returnValue( new DecimalValue( $uncertaintyMargin ) ) );

		return $quantity;
	}

	/**
	 * @see ValueFormatterTestBase::validProvider
	 */
	public function validProvider() {
		return array(
			'Unbounded, Unit 1' => array(
				UnboundedQuantityValue::newFromNumber( '+2', '1' ),
				'2'
			),
			'Unbounded, String unit' => array(
				UnboundedQuantityValue::newFromNumber( '+2', 'Ultrameter' ),
				'2 <span class="wb-unit">Ultrameter</span>'
			),
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
				'2±0 <span class="wb-unit">&lt;b&gt;injection&lt;/b&gt;</span>'
			),
		);
	}

	public function testFormatWithFormatString() {
		$formatter = $this->getQuantityHtmlFormatter( null, null, '$2&thinsp;$1' );
		$value = UnboundedQuantityValue::newFromNumber( '+5', 'USD' );
		$formatted = $formatter->format( $value );
		$this->assertSame( '<span class="wb-unit">USD</span>&thinsp;5', $formatted );
	}

	/**
	 * @dataProvider applyUnitOptionProvider
	 */
	public function testGivenHtmlCharacters_formatEscapesHtmlCharacters(
		FormatterOptions $options = null,
		UnboundedQuantityValue $value,
		$expected
	) {
		$decimalFormatter = $this->getMock( 'ValueFormatters\DecimalFormatter' );
		$decimalFormatter->expects( $this->any() )
			->method( 'format' )
			->will( $this->returnValue( '<b>+2</b>' ) );

		$formatter = $this->getQuantityHtmlFormatter( $options, $decimalFormatter );
		$formatted = $formatter->format( $value );
		$this->assertSame( $expected, $formatted );
	}

	public function applyUnitOptionProvider() {
		$noUnit = new FormatterOptions();
		$noUnit->setOption( QuantityHtmlFormatter::OPT_APPLY_UNIT, false );

		return array(
			'Disabled without unit' => array(
				$noUnit,
				UnboundedQuantityValue::newFromNumber( 2, '1' ),
				'&lt;b&gt;+2&lt;/b&gt;'
			),
			'Disabled with unit' => array(
				$noUnit,
				UnboundedQuantityValue::newFromNumber( 2, '<b>m</b>' ),
				'&lt;b&gt;+2&lt;/b&gt;'
			),
			'Default without unit' => array(
				null,
				UnboundedQuantityValue::newFromNumber( 2, '1' ),
				'&lt;b&gt;+2&lt;/b&gt;'
			),
			'Default with unit' => array(
				null,
				UnboundedQuantityValue::newFromNumber( 2, '<b>m</b>' ),
				'&lt;b&gt;+2&lt;/b&gt; <span class="wb-unit">&lt;b&gt;m&lt;/b&gt;</span>'
			),
			'HTML escaping bounded' => array(
				null,
				$this->newQuantityValue( 'DataValues\QuantityValue' ),
				'&lt;b&gt;+2&lt;/b&gt;±&lt;b&gt;+2&lt;/b&gt;'
			),
			'HTML escaping bounded with uncertainty' => array(
				null,
				$this->newQuantityValue( 'DataValues\QuantityValue', 1 ),
				'&lt;b&gt;+2&lt;/b&gt;±&lt;b&gt;+2&lt;/b&gt;'
			),
			'HTML escaping unbounded' => array(
				null,
				$this->newQuantityValue( 'DataValues\UnboundedQuantityValue' ),
				'&lt;b&gt;+2&lt;/b&gt;'
			),
		);
	}

}
