<?php

namespace SMW\Tests\MediaWiki\Renderer;

use SMW\MediaWiki\Renderer\HtmlTableRenderer;
use SMW\Tests\Utils\UtilityFactory;

/**
 * @covers \SMW\MediaWiki\Renderer\HtmlTableRenderer
 * @group semantic-mediawiki
 *
 * @license GPL-2.0-or-later
 * @since   1.9
 *
 * @author mwjames
 */
class HtmlTableRendererTest extends \PHPUnit\Framework\TestCase {

	private $stringValidator;

	protected function setUp(): void {
		parent::setUp();

		$this->stringValidator = UtilityFactory::getInstance()->newValidatorFactory()->newStringValidator();
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			'\SMW\MediaWiki\Renderer\HtmlTableRenderer',
			new HtmlTableRenderer()
		);
	}

	public function testAddHeaderItem() {
		$instance = new HtmlTableRenderer();
		$instance->addHeaderItem( 'span', 'lala' );

		$this->stringValidator->assertThatStringContains(
			'<span>lala</span>',
			$instance->getHeaderItems()
		);
	}

	public function testAddTableHeader() {
		$instance = new HtmlTableRenderer();
		$instance->addHeader( 'lala' );

		$this->stringValidator->assertThatStringContains(
			'<th>lala</th>',
			$instance->getHtml()
		);

		$instance = new HtmlTableRenderer( true );
		$instance->addHeader( 'lila' );

		$this->stringValidator->assertThatStringContains(
			'<thead><tr><th>lila</th></tr></thead>',
			$instance->getHtml()
		);
	}

	public function testAddTableRow() {
		$instance = new HtmlTableRenderer();

		$instance
			->addCell( 'lala', [ 'class' => 'foo' ] )
			->addRow()
			->addCell( 'lula' )
			->addRow();

		$this->stringValidator->assertThatStringContains(
			'<tr class="row-odd"><td class="foo">lala</td></tr><tr class="row-even"><td>lula</td></tr>',
			$instance->getHtml()
		);

		$instance = new HtmlTableRenderer();

		$instance
			->setHtmlContext( true )
			->addCell( 'lila' )
			->addRow();

		$this->stringValidator->assertThatStringContains(
			'<tbody><tr class="row-odd"><td>lila</td></tr></tbody>',
			$instance->getHtml()
		);
	}

	public function testStandardTable() {
		$instance = new HtmlTableRenderer();

		$instance
			->addCell( 'lala', [ 'rel' => 'tuuu' ] )
			->addRow( [ 'class' => 'foo' ] );

		$this->stringValidator->assertThatStringContains(
			'<table><tr class="foo row-odd"><td rel="tuuu">lala</td></tr></table>',
			$instance->getHtml()
		);

		$instance = new HtmlTableRenderer();

		$instance
			->addHeader( 'lula' )
			->addCell( 'lala' )
			->addRow();

		$this->stringValidator->assertThatStringContains(
			'<table><tr><th>lula</th></tr><tr class="row-odd"><td>lala</td></tr></table>',
			$instance->getHtml()
		);

		$instance = new HtmlTableRenderer( true );

		$instance
			->addHeader( 'lula' )
			->addCell( 'lala' )
			->addRow();

		$this->stringValidator->assertThatStringContains(
			'<table><thead><tr><th>lula</th></tr></thead><tbody><tr class="row-odd"><td>lala</td></tr></tbody></table>',
			$instance->getHtml()
		);
	}

	public function testTransposedTable() {
		$instance = new HtmlTableRenderer();

		// We need a dedicated header definition to support a table transpose
		$instance
			->transpose( true )
			->addHeader( 'Foo' )->addHeader( 'Bar' )
			->addCell( 'lala', [ 'class' => 'foo' ] )
			->addRow()
			->addCell( 'lula', [ 'rel' => 'tuuu' ] )->addCell( 'lila' )
			->addRow();

		$this->stringValidator->assertThatStringContains(
			'<table data-transpose="1"><tr class="row-odd"><th>Foo</th><td class="foo">lala</td><td rel="tuuu">lula</td></tr><tr class="row-even"><th>Bar</th><td></td><td>lila</td></tr></table>',
			$instance->getHtml()
		);

		$instance = new HtmlTableRenderer( true );

		$instance
			->transpose( true )
			->addHeader( 'Foo' )->addHeader( 'Bar' )
			->addCell( 'lala', [ 'class' => 'foo' ] )
			->addRow()
			->addCell( 'lula' )->addCell( 'lila' )
			->addRow();

		$this->stringValidator->assertThatStringContains( // @codingStandardsIgnoreStart phpcs, ignore --sniffs=Generic.Files.LineLength
			'<table data-transpose="1"><thead></thead><tbody><tr class="row-odd"><th>Foo</th><td class="foo">lala</td><td>lula</td></tr><tr class="row-even"><th>Bar</th><td></td><td>lila</td></tr></tbody></table>', // @codingStandardsIgnoreEnd
			$instance->getHtml()
		);
	}

	public function testEmptyTable() {
		$instance = new HtmlTableRenderer();

		$instance
			->addCell()
			->addRow();

		$this->assertEmpty(
			$instance->getHtml()
		);
	}

}
