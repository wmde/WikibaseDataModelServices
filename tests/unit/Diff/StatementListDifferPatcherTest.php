<?php

namespace Wikibase\DataModel\Services\Tests\Diff;

use Wikibase\DataModel\Services\Diff\StatementListDiffer;
use Wikibase\DataModel\Services\Diff\StatementListPatcher;
use Wikibase\DataModel\Snak\PropertyNoValueSnak;
use Wikibase\DataModel\Snak\SnakList;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Statement\StatementList;

class StatementListDifferPatcherTest extends \PHPUnit_Framework_TestCase {

	public function testStatemetsAreMergedWhenPatched() {
		$guid = 'some-guid';
		$statementLatest = new Statement(
			new PropertyNoValueSnak( 1 ),
			new SnakList( [ new PropertyNoValueSnak( 3 ) ] ),
			null,
			$guid
		);
		$statementFrom = new Statement(
			new PropertyNoValueSnak( 1 ),
			new SnakList( [ new PropertyNoValueSnak( 1 ) ] ),
			null,
			$guid
		);
		$statementTo = new Statement(
			new PropertyNoValueSnak( 1 ),
			new SnakList( [ new PropertyNoValueSnak( 2 ) ] ),
			null,
			$guid
		);
		$latestStatementList = new StatementList( [ $statementLatest ] );

		$differ = new StatementListDiffer();
		$patcher = new StatementListPatcher();

		$diff = $differ->getDiff( new StatementList( [ $statementFrom ] ), new StatementList( [ $statementTo ] ) );
		$patcher->patchStatementList( $latestStatementList, $diff );

		$statement = $latestStatementList->getFirstStatementWithGuid( $guid );
		$this->assertEquals( 2, $statement->getQualifiers()->count() );
	}
}
