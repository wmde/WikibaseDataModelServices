<?php
namespace Wikibase\DataModel\Services\Tests\Lookup;

use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Services\Lookup\DispatchingTermLookup;
use Wikibase\DataModel\Services\Lookup\TermLookup;
use Wikibase\DataModel\Services\Lookup\UnknownForeignRepositoryException;
use Wikimedia\Assert\ParameterAssertionException;

/**
 * @covers Wikibase\DataModel\Services\Lookup\DispatchingTermLookup
 *
 * @license GPL-2.0+
 */
class DispatchingTermLookupTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider provideInvalidForeignLookups
	 */
	public function testGivenInvalidForeignLookups_exceptionIsThrown( array $lookups ) {
		$this->setExpectedException( ParameterAssertionException::class );
		new DispatchingTermLookup( $lookups );
	}

	public function provideInvalidForeignLookups() {
		return [
			'no lookups given' => [[]],
			'not an implementation of TermLookup given as a lookup' => [
				[ '' => new ItemId( 'Q123' ) ],
			],
			'non-string keys' => [
				[
					'' => $this->getMock( TermLookup::class ),
					100 => $this->getMock( TermLookup::class ),
				],
			],
			'repo name containing colon' => [
				[
					'' => $this->getMock( TermLookup::class ),
					'fo:oo' => $this->getMock( TermLookup::class ),
				],
			],
		];
	}

	/**
	 * @dataProvider publicMethodsProvider
	 */
	public function testGivenEntityFromUnknownRepo_throwsException( $method, $language ) {
		$item = new ItemId( 'fooo:Q123' );
		$lookup = new DispatchingTermLookup( [
			'' => $this->getMock( TermLookup::class ),
			'foo' => $this->getMock( TermLookup::class ),
		] );

		$this->setExpectedException( UnknownForeignRepositoryException::class );
		$lookup->$method( $item, $language );
	}

	public function publicMethodsProvider() {
		return [
			[ 'getLabel', 'en' ],
			[ 'getLabels', [ 'en', 'fr' ] ],
			[ 'getDescription', 'en' ],
			[ 'getDescription', [ 'en', 'de' ] ],
		];
	}

	public function testGetLabel() {
		$lookup1 = $this->getMock( TermLookup::class );
		$lookup1->expects( $this->once() )
			->method( 'getLabel' )
			->willReturn( 'label1' );

		$lookup2 = $this->getMock( TermLookup::class );
		$lookup2->expects( $this->once() )
			->method( 'getLabel' )
			->willReturn( 'label2' );

		$dispatcher = new DispatchingTermLookup( [
			'' => $lookup1,
			'foo' => $lookup2,
		] );

		$this->assertSame(
			'label1',
			$dispatcher->getLabel( new ItemId( 'Q123' ), 'en' )
		);
		$this->assertSame(
			'label2',
			$dispatcher->getLabel( new ItemId( 'foo:Q123' ), 'en' )
		);
	}

	public function testGetLabels() {
		$labels1 = [ 'en' => 'enLabel1', 'fr' => 'frLabel1' ];
		$lookup1 = $this->getMock( TermLookup::class );
		$lookup1->expects( $this->once() )
			->method( 'getLabels' )
			->willReturn( $labels1 );

		$labels2 = [ 'en' => 'enLabel2', 'fr' => 'frLabel2' ];
		$lookup2 = $this->getMock( TermLookup::class );
		$lookup2->expects( $this->once() )
			->method( 'getLabels' )
			->willReturn( $labels2 );

		$dispatcher = new DispatchingTermLookup( [
			'' => $lookup1,
			'foo' => $lookup2,
		] );

		$this->assertSame(
			$labels1,
			$dispatcher->getLabels( new ItemId( 'Q123' ), [ 'en', 'fr' ] )
		);
		$this->assertSame(
			$labels2,
			$dispatcher->getLabels( new ItemId( 'foo:Q123' ), [ 'en', 'fr' ] )
		);
	}

	public function testGetDescription() {
		$lookup1 = $this->getMock( TermLookup::class );
		$lookup1->expects( $this->once() )
			->method( 'getDescription' )
			->willReturn( 'description1' );

		$lookup2 = $this->getMock( TermLookup::class );
		$lookup2->expects( $this->once() )
			->method( 'getDescription' )
			->willReturn( 'description2' );

		$dispatcher = new DispatchingTermLookup( [
			'' => $lookup1,
			'foo' => $lookup2,
		] );

		$this->assertSame(
			'description1',
			$dispatcher->getDescription( new ItemId( 'Q123' ), 'en' )
		);
		$this->assertSame(
			'description2',
			$dispatcher->getDescription( new ItemId( 'foo:Q123' ), 'en' )
		);
	}

	public function testGetDescriptions() {
		$descriptions1 = [ 'en' => 'description1' ];
		$lookup1 = $this->getMock( TermLookup::class );
		$lookup1->expects( $this->once() )
			->method( 'getDescriptions' )
			->willReturn( $descriptions1 );

		$descriptions2 = [ 'en' => 'description2' ];
		$lookup2 = $this->getMock( TermLookup::class );
		$lookup2->expects( $this->once() )
			->method( 'getDescriptions' )
			->willReturn( $descriptions2 );

		$dispatcher = new DispatchingTermLookup( [
			'' => $lookup1,
			'foo' => $lookup2,
		] );

		$this->assertSame(
			$descriptions1,
			$dispatcher->getDescriptions( new ItemId( 'Q123' ), [ 'en' ] )
		);
		$this->assertSame(
			$descriptions2,
			$dispatcher->getDescriptions( new ItemId( 'foo:Q123' ), [ 'en' ] )
		);
	}

}
