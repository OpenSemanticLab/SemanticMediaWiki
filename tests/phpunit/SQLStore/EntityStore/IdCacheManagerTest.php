<?php

namespace SMW\Tests\SQLStore\EntityStore;

use Onoi\Cache\FixedInMemoryLruCache;
use SMW\SQLStore\EntityStore\IdCacheManager;
use SMW\Tests\PHPUnitCompat;

/**
 * @covers \SMW\SQLStore\EntityStore\IdCacheManager
 * @group semantic-mediawiki
 *
 * @license GPL-2.0-or-later
 * @since   3.0
 *
 * @author mwjames
 */
class IdCacheManagerTest extends \PHPUnit\Framework\TestCase {

	use PHPUnitCompat;

	private $caches;

	protected function setUp(): void {
		$this->caches = [
			'entity.id' => new FixedInMemoryLruCache(),
			'entity.sort' => new FixedInMemoryLruCache(),
			'entity.lookup' => new FixedInMemoryLruCache(),
			'propertytable.hash' => new FixedInMemoryLruCache()
		];
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			IdCacheManager::class,
			new IdCacheManager( $this->caches )
		);
	}

	public function testComputeSha1() {
		$this->assertIsString(

			IdCacheManager::computeSha1( [] )
		);
	}

	public function testGet() {
		$instance = new IdCacheManager( $this->caches );

		$this->assertInstanceOf(
			FixedInMemoryLruCache::class,
			$instance->get( 'entity.sort' )
		);
	}

	public function testGetThrowsException() {
		$instance = new IdCacheManager( $this->caches );

		$this->expectException( '\RuntimeException' );
		$instance->get( 'foo' );
	}

	public function testGetId() {
		$instance = new IdCacheManager( $this->caches );

		$instance->setCache( 'foo', 0, '', '', 42, 'bar' );

		$this->assertEquals(
			42,
			$instance->getId( new \SMW\DIWikiPage( 'foo', NS_MAIN ) )
		);

		$this->assertEquals(
			42,
			$instance->getId( [ 'foo', 0, '', '' ] )
		);

		$this->assertFalse(
						$instance->getId( [ 'foo', '0', '', '' ] )
		);

		$this->assertEquals(
			42,
			$instance->getId( $instance->computeSha1( [ 'foo', 0, '', '' ] ) )
		);
	}

	public function testGetSort() {
		$instance = new IdCacheManager( $this->caches );

		$instance->setCache( 'foo', 0, '', '', 42, 'bar' );

		$this->assertEquals(
			'bar',
			$instance->getSort( $instance->computeSha1( [ 'foo', 0, '', '' ] ) )
		);

		$this->assertEquals(
			'bar',
			$instance->getSort( [ 'foo', 0, '', '' ] )
		);
	}

	public function testDeleteCache() {
		$instance = new IdCacheManager( $this->caches );

		$instance->setCache( 'foo', 0, '', '', '42', 'bar' );

		$this->assertEquals(
			42,
			$instance->getId( [ 'foo', 0, '', '' ] )
		);

		$instance->deleteCache( 'foo', 0, '', '' );

		$this->assertFalse(
						$instance->getId( [ 'foo', '0', '', '' ] )
		);

		$this->assertFalse(
						$instance->getSort( [ 'foo', 0, '', '' ] )
		);
	}

	public function testHasCache() {
		$instance = new IdCacheManager( $this->caches );

		$instance->setCache( 'foo', 0, '', '', '42', 'bar' );

		$this->assertFalse(
						$instance->hasCache( [ 'foo', 0, '', '' ] )
		);

		$this->assertTrue(
						$instance->hasCache( $instance->computeSha1( [ 'foo', 0, '', '' ] ) )
		);
	}

	public function testDeleteCacheById() {
		$cache = $this->getMockBuilder( '\Onoi\Cache\Cache' )
			->disableOriginalConstructor()
			->getMock();

		$cache->expects( $this->once() )
			->method( 'delete' )
			->with( IdCacheManager::computeSha1( [ 'foo', 0, '', '' ] ) );

		$this->caches['entity.id'] = $cache;

		$instance = new IdCacheManager( $this->caches );
		$instance->setCache( 'foo', 0, '', '', '42', 'bar' );

		$instance->deleteCacheById( 42 );
	}

	public function testSetCacheOnTitleWithSpace_ThrowsException() {
		$instance = new IdCacheManager( $this->caches );

		$this->expectException( '\RuntimeException' );
		$instance->setCache( 'foo bar', '', '', '', '', '' );
	}

	public function testSetCacheOnTitleAsArray_ThrowsException() {
		$instance = new IdCacheManager( $this->caches );

		$this->expectException( '\RuntimeException' );
		$instance->setCache( [ 'foo bar' ], '', '', '', '', '' );
	}

}
