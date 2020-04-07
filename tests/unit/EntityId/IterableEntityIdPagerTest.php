<?php

namespace Wikibase\DataModel\Services\Tests\EntityId;

use ArrayIterator;
use IteratorAggregate;
use PHPUnit\Framework\TestCase;
use Wikibase\DataModel\Services\EntityId\IterableEntityIdPager;

/**
 * Note: this test has the pager iterate through numbers, not entity IDs.
 * It simplifies the test, and the pager doesn’t care.
 *
 * @covers \Wikibase\DataModel\Services\EntityId\IterableEntityIdPager
 *
 * @license GPL-2.0-or-later
 */
class IterableEntityIdPagerTest extends TestCase {

	private const ONE_THROUGH_TEN = [ 1, 2, 3, 4, 5, 6, 7, 8, 9, 10 ];

	private function yieldOneThroughTenIndividually() {
		foreach ( self::ONE_THROUGH_TEN as $number ) {
			yield $number;
		}
	}

	private function yieldOneThroughTenAsYieldFrom() {
		yield from self::ONE_THROUGH_TEN;
	}

	/** Various iterables which all yield the numbers one through ten (both inclusive). */
	public function provideIterables() {
		yield 'array' => [ self::ONE_THROUGH_TEN ];
		yield 'ArrayIterator' => [ new ArrayIterator( self::ONE_THROUGH_TEN ) ];
		yield 'generator I' => [ $this->yieldOneThroughTenIndividually() ];
		yield 'generator II' => [ $this->yieldOneThroughTenAsYieldFrom() ];
		$aggregate = $this->createMock( IteratorAggregate::class );
		$aggregate->method( 'getIterator' )->willReturn( new ArrayIterator( self::ONE_THROUGH_TEN ) );
		yield 'IteratorAggregate' => [ $aggregate ];
	}

	public function providePagers() {
		foreach ( $this->provideIterables() as $key => $iterable ) {
			yield $key => [ new IterableEntityIdPager( $iterable[0] ) ];
		}
	}

	/** @dataProvider providePagers */
	public function testOneBatchLimit10( IterableEntityIdPager $pager ) {
		$this->assertSame( self::ONE_THROUGH_TEN, $pager->fetchIds( 10 ) );
		$this->assertSame( [], $pager->fetchIds( 1 ) );
		$this->assertSame( [], $pager->fetchIds( 10 ) );
	}

	/** @dataProvider providePagers */
	public function testOneBatchLimit100( IterableEntityIdPager $pager ) {
		$this->assertSame( self::ONE_THROUGH_TEN, $pager->fetchIds( 100 ) );
		$this->assertSame( [], $pager->fetchIds( 1 ) );
		$this->assertSame( [], $pager->fetchIds( 10 ) );
	}

	/** @dataProvider providePagers */
	public function testTwoBatchesLimits5And5( IterableEntityIdPager $pager ) {
		$this->assertSame( [ 1, 2, 3, 4, 5 ], $pager->fetchIds( 5 ) );
		$this->assertSame( [ 6, 7, 8, 9, 10 ], $pager->fetchIds( 5 ) );
		$this->assertSame( [], $pager->fetchIds( 5 ) );
		$this->assertSame( [], $pager->fetchIds( 1 ) );
		$this->assertSame( [], $pager->fetchIds( 0 ) );
	}

	/** @dataProvider providePagers */
	public function testThreeBatchesLimits0And5And5( IterableEntityIdPager $pager ) {
		$this->assertSame( [], $pager->fetchIds( 0 ) );
		$this->assertSame( [ 1, 2, 3, 4, 5 ], $pager->fetchIds( 5 ) );
		$this->assertSame( [ 6, 7, 8, 9, 10 ], $pager->fetchIds( 5 ) );
		$this->assertSame( [], $pager->fetchIds( 5 ) );
		$this->assertSame( [], $pager->fetchIds( 1 ) );
		$this->assertSame( [], $pager->fetchIds( 0 ) );
	}

	/** @dataProvider providePagers */
	public function testFourBatchesLimits1And2And3And4( IterableEntityIdPager $pager ) {
		$this->assertSame( [ 1 ], $pager->fetchIds( 1 ) );
		$this->assertSame( [ 2, 3 ], $pager->fetchIds( 2 ) );
		$this->assertSame( [ 4, 5, 6 ], $pager->fetchIds( 3 ) );
		$this->assertSame( [ 7, 8, 9, 10 ], $pager->fetchIds( 4 ) );
		$this->assertSame( [], $pager->fetchIds( 5 ) );
	}

	/** @dataProvider providePagers */
	public function testTenBatchesEachLimit1( IterableEntityIdPager $pager ) {
		foreach ( self::ONE_THROUGH_TEN as $i ) {
			$this->assertSame( [ $i ], $pager->fetchIds( 1 ) );
		}
		$this->assertSame( [], $pager->fetchIds( 1 ) );
	}

}
