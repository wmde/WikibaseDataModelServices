<?php

namespace Wikibase\DataModel\Services\Tests\Diff;

use PHPUnit\Framework\TestCase;
use Wikibase\DataModel\Services\Diff\FingerprintDiffer;
use Wikibase\DataModel\Term\AliasGroup;
use Wikibase\DataModel\Term\AliasGroupList;
use Wikibase\DataModel\Term\Fingerprint;
use Wikibase\DataModel\Term\Term;
use Wikibase\DataModel\Term\TermList;

/**
 * @covers \Wikibase\DataModel\Services\Diff\FingerprintDiffer
 */
class FingerprintDifferTest extends TestCase {

	public function testEmptyFingerprints() {
		$this->assertEquals(
			new Fingerprint(),
			$this->diff( new Fingerprint(), new Fingerprint() )
		);
	}

	private function diff( Fingerprint $first, Fingerprint $second ): Fingerprint {
		return ( new FingerprintDiffer() )->diff( $first, $second );
	}

	public function testTermsNotInSecondAreReturned() {
		$first = new Fingerprint(
			new TermList( [ new Term( 'en', 'FirstLabel' ) ] ),
			new TermList( [ new Term( 'en', 'FirstDesc' ) ] ),
			new AliasGroupList( [
				new AliasGroup( 'en', [ 'FirstAliasOne', 'FirstAliasTwo' ] ),
				new AliasGroup( 'de', [ 'DeAliasOne', 'DeAliasTwo' ] ),
			] )
		);

		$this->assertEquals(
			$first,
			$this->diff(
				$first,
				new Fingerprint(
					new TermList( [ new Term( 'en', 'SecondLabel' ) ] ),
					new TermList( [ new Term( 'en', 'SecondDesc' ) ] ),
					new AliasGroupList( [
						new AliasGroup( 'en', [ 'SecondAliasOne', 'SecondAliasTwo' ] ),
						new AliasGroup( 'different', [ 'DeAliasOne', 'DeAliasTwo' ] ),
					] )
				)
			)
		);
	}

	public function testTermsInSecondAreNotReturned() {
		$fingerprint = new Fingerprint(
			new TermList( [ new Term( 'en', 'FirstLabel' ) ] ),
			new TermList( [ new Term( 'en', 'FirstDesc' ) ] ),
			new AliasGroupList( [
				new AliasGroup( 'en', [ 'FirstAliasOne', 'FirstAliasTwo' ] ),
			] )
		);

		$this->assertEquals(
			new Fingerprint(),
			$this->diff(
				$fingerprint,
				$fingerprint
			)
		);
	}

	public function testAllTheThings() {
		$this->assertEquals(
			new Fingerprint(
				new TermList( [
					new Term( 'fr', 'OnlyInFirst' )
				] ),
				new TermList( [
					new Term( 'de', 'MismatchLanguage' )
				] ),
				new AliasGroupList( [
					new AliasGroup( 'en', [ 'AliasOnlyInFirst' ] ),
					new AliasGroup( 'de', [ 'AliasesOnlyInFirst' ] )
				] )
			),
			$this->diff(
				new Fingerprint(
					new TermList( [
						new Term( 'en', 'MatchingTerm' ),
						new Term( 'fr', 'OnlyInFirst' )
					] ),
					new TermList( [
						new Term( 'de', 'MismatchLanguage' )
					] ),
					new AliasGroupList( [
						new AliasGroup( 'en', [ 'MatchingAlias', 'AliasOnlyInFirst' ] ),
						new AliasGroup( 'de', [ 'AliasesOnlyInFirst' ] ),
					] )
				),
				new Fingerprint(
					new TermList( [
						new Term( 'en', 'MatchingTerm' ),
						new Term( 'ru', 'OnlyInSecond' )
					] ),
					new TermList( [
						new Term( 'different', 'MismatchLanguage' )
					] ),
					new AliasGroupList( [
						new AliasGroup( 'en', [ 'MatchingAlias', 'AliasOnlyInSecond' ] ),
						new AliasGroup( 'ru', [ 'AliasesOnlyInSecond' ] ),
					] )
				)
			)
		);
	}

}
