<?php

namespace Wikibase\DataModel\Services\Tests\Statement;

use PHPUnit_Framework_TestCase;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Services\Statement\StatementListChanger;
use Wikibase\DataModel\Snak\PropertyNoValueSnak;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Statement\StatementList;

/**
 * @covers Wikibase\DataModel\Services\Statement\StatementListChanger
 *
 * @license GPL-2.0+
 * @author Thiemo Mättig
 */
class StatementListChangerTest extends PHPUnit_Framework_TestCase {

	public function groupByPropertyIdProvider() {
		return [
			[
				[],
				[]
			],
			[
				[ 'P1$a' ],
				[ 'P1$a' ]
			],
			[
				[ 'P1$a', 'P2$b', 'P1$c', 'P2$d' ],
				[ 'P1$a', 'P1$c', 'P2$b', 'P2$d' ]
			],
			[
				[ 'P1$a', 'P1$b', 'P2$c', 'P3$d', 'P1$e', 'P2$f' ],
				[ 'P1$a', 'P1$b', 'P1$e', 'P2$c', 'P2$f', 'P3$d' ]
			],
		];
	}

	/**
	 * @dataProvider groupByPropertyIdProvider
	 */
	public function testGroupByPropertyId( array $guids, array $expectedGuids ) {
		$statementList = $this->newStatementList( $guids );

		$instance = new StatementListChanger( $statementList );
		$instance->groupByProperty();

		$this->assertGuids( $expectedGuids, $statementList );
	}

	public function addToGroupProvider() {
		return [
			'add to empty list' => [
				[],
				'P1$new',
				[ 'P1$new' ]
			],
			'append' => [
				[ 'P1$a' ],
				'P2$new',
				[ 'P1$a', 'P2$new' ]
			],
			'insert' => [
				[ 'P1$a', 'P2$b' ],
				'P1$new',
				[ 'P1$a', 'P1$new', 'P2$b' ]
			],
			'prefer last group when not ordered' => [
				[ 'P1$a', 'P2$b', 'P1$c', 'P2$d' ],
				'P1$new',
				[ 'P1$a', 'P2$b', 'P1$c', 'P1$new', 'P2$d' ]
			],
		];
	}

	/**
	 * @dataProvider addToGroupProvider
	 */
	public function testAddToGroup( array $guids, $newGuid, array $expectedGuids ) {
		$statementList = $this->newStatementList( $guids );
		$statement = $this->newStatement( $newGuid );

		$instance = new StatementListChanger( $statementList );
		$instance->addToGroup( $statement );

		$this->assertGuids( $expectedGuids, $statementList );
	}

	public function addToGroupByIndexProvider() {
		return [
			// Add to an empty list
			'add to empty list with exact index' => [
				[],
				'P1$new',
				0,
				[ 'P1$new' ]
			],
			'add to empty list with extreme index' => [
				[],
				'P1$new',
				100,
				[ 'P1$new' ]
			],
			'add to empty list with negative index' => [
				[],
				'P1$new',
				-100,
				[ 'P1$new' ]
			],

			// Add the second statement with the same property
			'append with exact index' => [
				[ 'P1$a' ],
				'P1$new',
				1,
				[ 'P1$a', 'P1$new' ]
			],
			'prepend with exact index' => [
				[ 'P1$a' ],
				'P1$new',
				0,
				[ 'P1$new', 'P1$a' ]
			],

			// Add to a list with multiple properties
			'insert with exact index' => [
				[ 'P1$a', 'P2$b' ],
				'P1$new',
				1,
				[ 'P1$a', 'P1$new', 'P2$b' ]
			],
			'insert with extreme index' => [
				[ 'P1$a', 'P2$b' ],
				'P1$new',
				100,
				[ 'P1$a', 'P1$new', 'P2$b' ]
			],
			'prepend with negative index' => [
				[ 'P1$a', 'P2$b' ],
				'P1$new',
				-100,
				[ 'P1$new', 'P1$a', 'P2$b' ]
			],

			// Add to a list that has multiple groups with the same property
			'decrease index to closest match' => [
				[ 'P1$a', 'P2$b', 'P2$c', 'P2$d', 'P1$e' ],
				'P1$new',
				2,
				[ 'P1$a', 'P1$new', 'P2$b', 'P2$c', 'P2$d', 'P1$e' ],
			],
			'increase index to closest match' => [
				[ 'P1$a', 'P2$b', 'P2$c', 'P2$d', 'P1$e' ],
				'P1$new',
				3,
				[ 'P1$a', 'P2$b', 'P2$c', 'P2$d', 'P1$new', 'P1$e' ],
			],
			'prefer decreasing when no closer match' => [
				[ 'P1$a', 'P2$b', 'P2$c', 'P1$d' ],
				'P1$new',
				2,
				[ 'P1$a', 'P1$new', 'P2$b', 'P2$c', 'P1$d' ],
			],

			// Add a new property to a list that has internal group borders
			'decrease index to closest group border' => [
				[ 'P1$a', 'P2$b', 'P2$c', 'P2$d', 'P1$e' ],
				'P3$new',
				2,
				[ 'P1$a', 'P3$new', 'P2$b', 'P2$c', 'P2$d', 'P1$e' ],
			],
			'increase index to closest group border' => [
				[ 'P1$a', 'P2$b', 'P2$c', 'P2$d', 'P1$e' ],
				'P3$new',
				3,
				[ 'P1$a', 'P2$b', 'P2$c', 'P2$d', 'P3$new', 'P1$e' ],
			],
			'prefer decreasing when no closer group border' => [
				[ 'P1$a', 'P2$b', 'P2$c', 'P1$d' ],
				'P3$new',
				2,
				[ 'P1$a', 'P3$new', 'P2$b', 'P2$c', 'P1$d' ],
			],

			// Add a new property to a list that has no internal group borders
			'decrease index to closest list limit' => [
				[ 'P1$a', 'P1$b', 'P1$c' ],
				'P2$new',
				1,
				[ 'P2$new', 'P1$a', 'P1$b', 'P1$c' ],
			],
			'increase index to closest list limit' => [
				[ 'P1$a', 'P1$b', 'P1$c' ],
				'P2$new',
				2,
				[ 'P1$a', 'P1$b', 'P1$c', 'P2$new' ],
			],
			'prefer decreasing when no closer list limit' => [
				[ 'P1$a', 'P1$b' ],
				'P2$new',
				1,
				[ 'P2$new', 'P1$a', 'P1$b' ],
			],
		];
	}

	/**
	 * @dataProvider addToGroupByIndexProvider
	 */
	public function testAddToGroupByIndex( array $guids, $newGuid, $index, array $expectedGuids ) {
		$statementList = $this->newStatementList( $guids );
		$statement = $this->newStatement( $newGuid );

		$instance = new StatementListChanger( $statementList );
		$instance->addToGroup( $statement, $index );

		$this->assertGuids( $expectedGuids, $statementList );
	}

	/**
	 * @param string[] $guids
	 *
	 * @return StatementList
	 */
	private function newStatementList( array $guids ) {
		$statementList = new StatementList();

		foreach ( $guids as $guid ) {
			$statementList->addStatement( $this->newStatement( $guid ) );
		}

		return $statementList;
	}

	/**
	 * @param string $guid
	 *
	 * @return Statement
	 */
	private function newStatement( $guid ) {
		list( $propertyId, ) = explode( '$', $guid, 2 );

		return new Statement(
			new PropertyNoValueSnak( new PropertyId( $propertyId ) ),
			null,
			null,
			$guid
		);
	}

	/**
	 * @param string[] $expectedGuids
	 * @param StatementList $statementList
	 */
	private function assertGuids( array $expectedGuids, StatementList $statementList ) {
		$guids = [];

		foreach ( $statementList->toArray() as $statement ) {
			$guids[] = $statement->getGuid();
		}

		$this->assertSame( $expectedGuids, $guids );
	}

}
