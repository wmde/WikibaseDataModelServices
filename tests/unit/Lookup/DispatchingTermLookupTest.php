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
		$localLookup->expects( $this->once() )->method( 'getLabel' );
		$dispatcher = new DispatchingTermLookup( [
			'' => $localLookup,
			'foo' => $this->getMock( TermLookup::class ),
		] );

		$dispatcher->getLabel( new ItemId( 'Q123' ), 'en' );
	}

	public function testGetLabelFromForeignRepo() {
		$foreignLookup = $this->getMock( TermLookup::class );
		$foreignLookup->expects( $this->once() )->method( 'getLabel' );
		$dispatcher = new DispatchingTermLookup( [
			'' => $this->getMock( TermLookup::class ),
			'foo' => $foreignLookup,
		] );

		$dispatcher->getLabel( new ItemId( 'foo:Q123' ), 'en' );
	}

	public function testGetLabelsFromLocalRepo() {
		$localLookup = $this->getMock( TermLookup::class );
		$localLookup->expects( $this->once() )->method( 'getLabels' );
		$dispatcher = new DispatchingTermLookup( [
			'' => $localLookup,
			'foo' => $this->getMock( TermLookup::class ),
		] );

		$dispatcher->getLabels( new ItemId( 'Q123' ), [ 'en' ] );
	}

	public function testGetLabelsFromForeignRepo() {
		$foreignLookup = $this->getMock( TermLookup::class );
		$foreignLookup->expects( $this->once() )->method( 'getLabels' );
		$dispatcher = new DispatchingTermLookup( [
			'' => $this->getMock( TermLookup::class ),
			'foo' => $foreignLookup,
		] );

		$dispatcher->getLabels( new ItemId( 'foo:Q123' ), [ 'en' ] );
	}

	public function testGetDescriptionFromLocalRepo() {
		$localLookup = $this->getMock( TermLookup::class );
		$localLookup->expects( $this->once() )->method( 'getDescription' );
		$dispatcher = new DispatchingTermLookup( [
			'' => $localLookup,
			'foo' => $this->getMock( TermLookup::class ),
		] );

		$dispatcher->getDescription( new ItemId( 'Q123' ), 'en' );
	}

	public function testGetDescriptionFromForeignRepo() {
		$foreignLookup = $this->getMock( TermLookup::class );
		$foreignLookup->expects( $this->once() )->method( 'getDescription' );
		$dispatcher = new DispatchingTermLookup( [
			'' => $this->getMock( TermLookup::class ),
			'foo' => $foreignLookup,
		] );

		$dispatcher->getDescription( new ItemId( 'foo:Q123' ), 'en' );
	}

	public function testGetDescriptionsFromLocalRepo() {
		$localLookup = $this->getMock( TermLookup::class );
		$localLookup->expects( $this->once() )->method( 'getDescriptions' );
		$dispatcher = new DispatchingTermLookup( [
			'' => $localLookup,
			'foo' => $this->getMock( TermLookup::class ),
		] );

		$dispatcher->getDescriptions( new ItemId( 'Q123' ), [ 'en' ] );
	}

	public function testGetDescriptionsFromForeignRepo() {
		$foreignLookup = $this->getMock( TermLookup::class );
		$foreignLookup->expects( $this->once() )->method( 'getDescriptions' );
		$dispatcher = new DispatchingTermLookup( [
			'' => $this->getMock( TermLookup::class ),
			'foo' => $foreignLookup,
		] );

		$dispatcher->getDescriptions( new ItemId( 'foo:Q123' ), [ 'en' ] );
	}

}
