<?php

namespace SMW\Tests\Localizer;

use SMW\Localizer\Message;
use SMW\Tests\TestEnvironment;

/**
 * @covers \SMW\Localizer\Message
 * @group semantic-mediawiki
 *
 * @license GPL-2.0-or-later
 * @since  2.4
 *
 * @author mwjames
 */
class MessageTest extends \PHPUnit\Framework\TestCase {

	private $testEnvironment;

	public function setUp(): void {
		$this->testEnvironment = new TestEnvironment();
		$this->testEnvironment->resetPoolCacheById( Message::POOLCACHE_ID );
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			Message::class,
			new Message()
		);
	}

	public function testEmptyStringOnUnregisteredHandler() {
		$instance = new Message();

		$this->assertEmpty(
			$instance->get( 'Foo', 'Foo' )
		);
	}

	public function testRegisteredHandler() {
		$instance = new Message();

		$instance->registerCallbackHandler( 'Foo', static function ( $parameters, $language ) {
			if ( $parameters[0] === 'Foo' && $language === Message::CONTENT_LANGUAGE ) {
				return 'Foobar';
			}

			if ( $parameters[0] === 'Foo' && is_string( $language ) ) {
				return $language;
			}

			return 'UNKNOWN';
		} );

		$this->assertEquals(
			'Foobar',
			$instance->get( 'Foo', 'Foo' )
		);

		$this->assertEquals(
			'en',
			$instance->get( 'Foo', 'Foo', 'en' )
		);

		$instance->deregisterHandlerFor( 'Foo' );
	}

	public function testRegisteredHandlerWithLanguage() {
		$language = $this->getMockBuilder( '\Language' )
			->disableOriginalConstructor()
			->getMock();

		$language->expects( $this->once() )
			->method( 'getCode' )
			->willReturn( 'en' );

		$instanceSpy = $this->getMockBuilder( '\stdClass' )
			->setMethods( [ 'hasLanguage' ] )
			->getMock();

		$instanceSpy->expects( $this->once() )
			->method( 'hasLanguage' )
			->with( $this->identicalTo( $language ) );

		$instance = new Message();
		$instance->clear();

		$instance->registerCallbackHandler( 'Foo', static function ( $parameters, $language ) use ( $instanceSpy ){
			$instanceSpy->hasLanguage( $language );
			return 'UNKNOWN';
		} );

		$instance->get( 'Bar', 'Foo', $language );
		$instance->deregisterHandlerFor( 'Foo' );
	}

	public function testFromCache() {
		$instance = new Message();
		$instance->clear();

		$instance->registerCallbackHandler( 'SimpleText', static function ( $parameters, $language ) {
			return 'Foo';
		} );

		$instance->get( 'Foo', 'SimpleText' );

		$this->assertEquals(
			[
				'inserts' => 1,
				'deletes' => 0,
				'max'     => 1000,
				'count'   => 1,
				'hits'    => 0,
				'misses'  => 1
			],
			$instance->getCache()->getStats()
		);

		$instance->get( 'Foo', 'SimpleText', 'ooo' );

		$this->assertEquals(
			[
				'inserts' => 2,
				'deletes' => 0,
				'max'     => 1000,
				'count'   => 2,
				'hits'    => 0,
				'misses'  => 2
			],
			$instance->getCache()->getStats()
		);

		// Repeated request
		$instance->get( 'Foo', 'SimpleText' );

		$this->assertEquals(
			[
				'inserts' => 2,
				'deletes' => 0,
				'max'     => 1000,
				'count'   => 2,
				'hits'    => 1,
				'misses'  => 2
			],
			$instance->getCache()->getStats()
		);

		$instance->deregisterHandlerFor( 'SimpleText' );
	}

	/**
	 * @dataProvider encodeProvider
	 */
	public function testEncode( $string, $expected ) {
		$this->assertEquals(
			$expected,
			Message::encode( $string )
		);
	}

	public function testDecode() {
		$this->assertFalse(
						Message::decode( 'Foo' )
		);

		$this->assertEquals(
			'Foo',
			Message::decode( '[2,"Foo"]' )
		);
	}

	public function encodeProvider() {
		$provider[] = [
			'Foo',
			'[2,"Foo"]'
		];

		$provider[] = [
			[ 'Foo' ],
			'[2,"Foo"]'
		];

		$provider[] = [
			'[2,"Foo"]',
			'[2,"Foo"]'
		];

		$provider[] = [
			[ 'Foo', '<strong>Expression error: Unrecognized word "yyyy".</strong>' ],
			'[2,"Foo","Expression error: Unrecognized word \"yyyy\"."]'
		];

		$provider[] = [
			[ 'eb0afd6194bab91b6d32d2db4bb30060' => '[2,"smw-datavalue-wikipage-invalid-title","Help:"]' ],
			'[2,"smw-datavalue-wikipage-invalid-title","Help:"]'
		];

		return $provider;
	}

}
