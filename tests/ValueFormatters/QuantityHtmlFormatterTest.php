<?php

namespace ValueFormatters\Test;

use DataValues\DecimalValue;
use DataValues\QuantityValue;
use DataValues\UnboundedQuantityValue;
use PHPUnit\Framework\TestCase;
use ValueFormatters\DecimalFormatter;
use ValueFormatters\FormatterOptions;
use ValueFormatters\QuantityHtmlFormatter;
use ValueFormatters\ValueFormatter;

/**
 * @covers ValueFormatters\QuantityHtmlFormatter
 *
 * @license GPL-2.0-or-later
 * @author Thiemo Kreuz
 */
class QuantityHtmlFormatterTest extends TestCase {

	/**
	 * @see ValueFormatterTestBase::getInstance
	 *
	 * @param FormatterOptions|null $options
	 *
	 * @return QuantityHtmlFormatter
	 */
	protected function getInstance( ?FormatterOptions $options = null ) {
		return $this->getQuantityHtmlFormatter( $options );
	}

	private function getQuantityHtmlFormatter(
		?FormatterOptions $options = null,
		?DecimalFormatter $decimalFormatter = null,
		?string $quantityWithUnitFormat = null
	): QuantityHtmlFormatter {
		$vocabularyUriFormatter = $this->createMock( ValueFormatter::class );
		$vocabularyUriFormatter->expects( $this->any() )
			->method( 'format' )
			->willReturnCallback( static function ( $unit ) {
				return $unit === '1' ? null : $unit;
			} );

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
			->willReturn( new DecimalValue( 2 ) );
		if ( $className === QuantityValue::class ) {
			$quantity->expects( $this->any() )
				->method( 'getUncertaintyMargin' )
				->willReturn( new DecimalValue( $uncertaintyMargin ) );
		}

		return $quantity;
	}

	/**
	 * @see ValueFormatterTestBase::validProvider
	 */
	public function validProvider() {
		return [
			'Unbounded, Unit 1' => [
				UnboundedQuantityValue::newFromNumber( '+2', '1' ),
				'2'
			],
			'Unbounded, String unit' => [
				UnboundedQuantityValue::newFromNumber( '+2', 'Ultrameter' ),
				'2 <span class="wb-unit">Ultrameter</span>'
			],
			'Unit 1' => [
				QuantityValue::newFromNumber( '+2', '1', '+3', '+1' ),
				'2±1'
			],
			'String unit' => [
				QuantityValue::newFromNumber( '+2', 'Ultrameter', '+3', '+1' ),
				'2±1 <span class="wb-unit">Ultrameter</span>'
			],
			'HTML injection' => [
				QuantityValue::newFromNumber( '+2', '<b>injection</b>', '+2', '+2' ),
				'2±0 <span class="wb-unit">&lt;b&gt;injection&lt;/b&gt;</span>'
			],
		];
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
		?FormatterOptions $options,
		UnboundedQuantityValue $value,
		$expected
	) {
		$decimalFormatter = $this->createMock( DecimalFormatter::class );
		$decimalFormatter->expects( $this->any() )
			->method( 'format' )
			->willReturn( '<b>+2</b>' );

		$formatter = $this->getQuantityHtmlFormatter( $options, $decimalFormatter );
		$formatted = $formatter->format( $value );
		$this->assertSame( $expected, $formatted );
	}

	public function applyUnitOptionProvider() {
		$noUnit = new FormatterOptions();
		$noUnit->setOption( QuantityHtmlFormatter::OPT_APPLY_UNIT, false );

		return [
			'Disabled without unit' => [
				$noUnit,
				UnboundedQuantityValue::newFromNumber( 2, '1' ),
				'&lt;b&gt;+2&lt;/b&gt;'
			],
			'Disabled with unit' => [
				$noUnit,
				UnboundedQuantityValue::newFromNumber( 2, '<b>m</b>' ),
				'&lt;b&gt;+2&lt;/b&gt;'
			],
			'Default without unit' => [
				null,
				UnboundedQuantityValue::newFromNumber( 2, '1' ),
				'&lt;b&gt;+2&lt;/b&gt;'
			],
			'Default with unit' => [
				null,
				UnboundedQuantityValue::newFromNumber( 2, '<b>m</b>' ),
				'&lt;b&gt;+2&lt;/b&gt; <span class="wb-unit">&lt;b&gt;m&lt;/b&gt;</span>'
			],
			'HTML escaping bounded' => [
				null,
				$this->newQuantityValue( QuantityValue::class ),
				'&lt;b&gt;+2&lt;/b&gt;±&lt;b&gt;+2&lt;/b&gt;'
			],
			'HTML escaping bounded with uncertainty' => [
				null,
				$this->newQuantityValue( QuantityValue::class, 1 ),
				'&lt;b&gt;+2&lt;/b&gt;±&lt;b&gt;+2&lt;/b&gt;'
			],
			'HTML escaping unbounded' => [
				null,
				$this->newQuantityValue( UnboundedQuantityValue::class ),
				'&lt;b&gt;+2&lt;/b&gt;'
			],
		];
	}

}
