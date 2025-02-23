<?php

namespace SMW\Tests\MediaWiki\Hooks;

use SMW\MediaWiki\Hooks\TitleIsAlwaysKnown;

/**
 * @covers \SMW\MediaWiki\Hooks\TitleIsAlwaysKnown
 * @group semantic-mediawiki
 *
 * @license GPL-2.0-or-later
 * @since 1.9
 *
 * @author mwjames
 */
class TitleIsAlwaysKnownTest extends \PHPUnit\Framework\TestCase {

	public function testCanConstruct() {
		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$result = '';

		$this->assertInstanceOf(
			'\SMW\MediaWiki\Hooks\TitleIsAlwaysKnown',
			new TitleIsAlwaysKnown( $title, $result )
		);
	}

	/**
	 * @dataProvider titleProvider
	 */
	public function testPerformUpdate( $namespace, $text, $expected ) {
		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->atLeastOnce() )
			->method( 'getNamespace' )
			->willReturn( $namespace );

		$title->expects( $this->any() )
			->method( 'getText' )
			->willReturn( $text );

		$result = '';

		$instance = new TitleIsAlwaysKnown( $title, $result );
		$this->assertTrue( $instance->process() );

		$this->assertEquals( $expected, $result );
	}

	public function titleProvider() {
		$provider = [
			[ SMW_NS_PROPERTY, 'Modification date', true ],
			[ SMW_NS_PROPERTY, 'Foo', false ],
			[ NS_MAIN, 'Modification date', false ],
		];

		return $provider;
	}

}
