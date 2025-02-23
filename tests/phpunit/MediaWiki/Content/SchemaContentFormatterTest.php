<?php

namespace SMW\Tests\MediaWiki\Content;

use SMW\MediaWiki\Content\SchemaContentFormatter;
use SMW\Schema\Schema;
use SMW\Tests\PHPUnitCompat;

/**
 * @covers \SMW\MediaWiki\Content\SchemaContentFormatter
 * @group semantic-mediawiki
 *
 * @license GPL-2.0-or-later
 * @since 3.0
 *
 * @author mwjames
 */
class SchemaContentFormatterTest extends \PHPUnit\Framework\TestCase {

	use PHPUnitCompat;

	private $store;

	protected function setUp(): void {
		parent::setUp();

		$this->store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->setMethods( [ 'service' ] )
			->getMockForAbstractClass();
	}

	public function testCanConstruct() {
		$this->assertInstanceof(
			SchemaContentFormatter::class,
			new SchemaContentFormatter( $this->store )
		);
	}

	public function testGetHelpLink() {
		$schema = $this->getMockBuilder( '\SMW\Schema\Schema' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new SchemaContentFormatter(
			$this->store
		);

		$this->assertIsString(

			$instance->getHelpLink( $schema )
		);
	}

	public function testGetText() {
		$schema = $this->getMockBuilder( '\SMW\Schema\Schema' )
			->disableOriginalConstructor()
			->getMock();

		$schema->expects( $this->any() )
			->method( 'get' )
			->willReturnCallback( [ $this, 'schema_get' ] );

		$text = '...';
		$isYaml = false;
		$errors = [];

		$instance = new SchemaContentFormatter(
			$this->store
		);

		$this->assertIsString(

			$instance->getText( $text, $schema, $errors )
		);
	}

	public function testGetText_Errors() {
		$schema = $this->getMockBuilder( '\SMW\Schema\Schema' )
			->disableOriginalConstructor()
			->getMock();

		$schema->expects( $this->any() )
			->method( 'get' )
			->willReturnCallback( [ $this, 'schema_get' ] );

		$text = '...';
		$isYaml = false;

		$errors = [
			[ 'property' => 'foo', 'message' => '---' ]
		];

		$instance = new SchemaContentFormatter(
			$this->store
		);

		$this->assertIsString(

			$instance->getText( $text, $schema, $errors )
		);
	}

	public function testGetUsage_Empty() {
		$schema = $this->getMockBuilder( '\SMW\Schema\Schema' )
			->disableOriginalConstructor()
			->getMock();

		$this->store->expects( $this->any() )
			->method( 'getPropertySubjects' )
			->willReturn( [] );

		$instance = new SchemaContentFormatter(
			$this->store
		);

		$instance->setType( [ 'usage_lookup' => 'Foo' ] );

		$this->assertEquals(
			[ '', 0 ],
			$instance->getUsage( $schema )
		);
	}

	public function testGetUsage() {
		$sortLetter = $this->getMockBuilder( '\SMW\SortLetter' )
			->disableOriginalConstructor()
			->getMock();

		$dataItem = $this->getMockBuilder( '\SMW\DIWikiPage' )
			->disableOriginalConstructor()
			->getMock();

		$schema = $this->getMockBuilder( '\SMW\Schema\Schema' )
			->disableOriginalConstructor()
			->getMock();

		$this->store->expects( $this->any() )
			->method( 'getPropertySubjects' )
			->willReturn( [ $dataItem ] );

		$this->store->expects( $this->any() )
			->method( 'service' )
			->willReturn( $sortLetter );

		$instance = new SchemaContentFormatter(
			$this->store
		);

		$instance->setType( [ 'usage_lookup' => 'Foo' ] );

		[ $usage, $count ] = $instance->getUsage( $schema );

		$this->assertContains(
			'smw-columnlist-container',
			$usage
		);
	}

	public function testGetUsage_MultipleProperties() {
		$sortLetter = $this->getMockBuilder( '\SMW\SortLetter' )
			->disableOriginalConstructor()
			->getMock();

		$dataItem = $this->getMockBuilder( '\SMW\DIWikiPage' )
			->disableOriginalConstructor()
			->getMock();

		$schema = $this->getMockBuilder( '\SMW\Schema\Schema' )
			->disableOriginalConstructor()
			->getMock();

		$this->store->expects( $this->any() )
			->method( 'getPropertySubjects' )
			->willReturn( [ $dataItem ] );

		$this->store->expects( $this->any() )
			->method( 'service' )
			->willReturn( $sortLetter );

		$instance = new SchemaContentFormatter(
			$this->store
		);

		$instance->setType( [ 'usage_lookup' => [ 'Foo', 'Bar' ] ] );

		[ $usage, $count ] = $instance->getUsage( $schema );

		$this->assertContains(
			'smw-columnlist-container',
			$usage
		);
	}

	public function schema_get( $key ) {
		return $key === Schema::SCHEMA_TAG ? [] : '';
	}

}
