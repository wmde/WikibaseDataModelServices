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
			'no lookup given for the local repository' => [
				[ 'foo' => $this->getMock( TermLookup::class ), ]
			],
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

	public function testGetLabelFromLocalRepo() {
		$localLookup = $this->getMock( TermLookup::class );
		$localLookup->expects( $this->once() )
			->method( 'getLabel' )
			->willReturn( 'label' );
		$dispatcher = new DispatchingTermLookup( [
			'' => $localLookup,
			'foo' => $this->getMock( TermLookup::class ),
		] );

		$this->assertSame(
			'label',
			$dispatcher->getLabel( new ItemId( 'Q123' ), 'en' )
		);
	}

	public function testGetLabelFromForeignRepo() {
		$foreignLookup = $this->getMock( TermLookup::class );
		$foreignLookup->expects( $this->once() )
			->method( 'getLabel' )
			->willReturn( 'label' );
		$dispatcher = new DispatchingTermLookup( [
			'' => $this->getMock( TermLookup::class ),
			'foo' => $foreignLookup,
		] );

		$this->assertSame(
			'label',
			$dispatcher->getLabel( new ItemId( 'foo:Q123' ), 'en' )
		);
	}

	public function testGetLabelsFromLocalRepo() {
		$labels = [ 'en' => 'enLabel', 'fr' => 'frLabel' ];
		$localLookup = $this->getMock( TermLookup::class );
		$localLookup->expects( $this->once() )
			->method( 'getLabels' )
			->willReturn( $labels );
		$dispatcher = new DispatchingTermLookup( [
			'' => $localLookup,
			'foo' => $this->getMock( TermLookup::class ),
		] );

		$this->assertSame(
			$labels,
			$dispatcher->getLabels( new ItemId( 'Q123' ), [ 'en', 'fr' ] )
		);
	}

	public function testGetLabelsFromForeignRepo() {
		$labels = [ 'en' => 'enLabel', 'fr' => 'frLabel' ];
		$foreignLookup = $this->getMock( TermLookup::class );
		$foreignLookup->expects( $this->once() )
			->method( 'getLabels' )
			->willReturn( $labels );
		$dispatcher = new DispatchingTermLookup( [
			'' => $this->getMock( TermLookup::class ),
			'foo' => $foreignLookup,
		] );

		$this->assertSame(
			$labels,
			$dispatcher->getLabels( new ItemId( 'foo:Q123' ), [ 'en', 'fr' ] )
		);
	}

	public function testGetDescriptionFromLocalRepo() {
		$localLookup = $this->getMock( TermLookup::class );
		$localLookup->expects( $this->once() )
			->method( 'getDescription' )
			->willReturn( 'description' );
		$dispatcher = new DispatchingTermLookup( [
			'' => $localLookup,
			'foo' => $this->getMock( TermLookup::class ),
		] );

		$this->assertSame(
			'description',
			$dispatcher->getDescription( new ItemId( 'Q123' ), 'en' )
		);
	}

	public function testGetDescriptionFromForeignRepo() {
		$foreignLookup = $this->getMock( TermLookup::class );
		$foreignLookup->expects( $this->once() )
			->method( 'getDescription' )
			->willReturn( 'description' );
		$dispatcher = new DispatchingTermLookup( [
			'' => $this->getMock( TermLookup::class ),
			'foo' => $foreignLookup,
		] );

		$this->assertSame(
			'description',
			$dispatcher->getDescription( new ItemId( 'foo:Q123' ), 'en' )
		);
	}

	public function testGetDescriptionsFromLocalRepo() {
		$descriptions = [ 'en' => 'description' ];
		$localLookup = $this->getMock( TermLookup::class );
		$localLookup->expects( $this->once() )
			->method( 'getDescriptions' )
			->willReturn( $descriptions );
		$dispatcher = new DispatchingTermLookup( [
			'' => $localLookup,
			'foo' => $this->getMock( TermLookup::class ),
		] );

		$this->assertSame(
			$descriptions,
			$dispatcher->getDescriptions( new ItemId( 'Q123' ), [ 'en' ] )
		);
	}

	public function testGetDescriptionsFromForeignRepo() {
		$descriptions = [ 'en' => 'description' ];
		$foreignLookup = $this->getMock( TermLookup::class );
		$foreignLookup->expects( $this->once() )
			->method( 'getDescriptions' )
			->willReturn( $descriptions );
		$dispatcher = new DispatchingTermLookup( [
			'' => $this->getMock( TermLookup::class ),
			'foo' => $foreignLookup,
		] );

		$this->assertSame(
			$descriptions,
			$dispatcher->getDescriptions( new ItemId( 'foo:Q123' ), [ 'en' ] )
		);
	}

}
