<?php

namespace Wikibase\DataModel\Services\Tests\Statement\Grouper;

use PHPUnit_Framework_TestCase;
use Wikibase\DataModel\Services\Statement\Grouper\FilteringStatementGrouper;
use Wikibase\DataModel\Snak\PropertyNoValueSnak;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Statement\StatementList;

/**
 * @covers Wikibase\DataModel\Services\Statement\Grouper\FilteringStatementGrouper
 *
 * @licence GNU GPL v2+
 * @author Thiemo Mättig
 */
class FilteringStatementGrouperTest extends PHPUnit_Framework_TestCase {

	private function newStatementFilter( $propertyId ) {
		$filter = $this->getMock( 'Wikibase\DataModel\Services\Statement\Filter\StatementFilter' );

		$filter->expects( $this->any() )
			->method( 'isMatch' )
			->will( $this->returnCallback( function( Statement $statement ) use ( $propertyId ) {
				return $statement->getPropertyId()->getSerialization() === $propertyId;
			} ) );

		return $filter;
	}

	public function testConstructorThrowsException() {
		$this->setExpectedException( 'InvalidArgumentException' );
		new FilteringStatementGrouper( array( 'filter' ) );
	}

	public function testDefaultGroupIsAlwaysThere() {
		$grouper = new FilteringStatementGrouper( array() );
		$groups = $grouper->groupStatements( new StatementList() );

		$this->assertArrayHasKey( 'statements', $groups );
	}

	public function testCanOverrideDefaultGroup() {
		$grouper = new FilteringStatementGrouper( array(
			'default' => null,
		) );
		$groups = $grouper->groupStatements( new StatementList() );

		$this->assertArrayHasKey( 'default', $groups );
		$this->assertArrayNotHasKey( 'statements', $groups );
	}

	public function testAllGroupsAreAlwaysThere() {
		$grouper = new FilteringStatementGrouper( array(
			'p1' => $this->newStatementFilter( 'P1' ),
		) );
		$groups = $grouper->groupStatements( new StatementList() );

		$this->assertArrayHasKey( 'p1', $groups );
	}

	public function testDefaultGroupIsLast() {
		$statements = new StatementList();
		$statements->addNewStatement( new PropertyNoValueSnak( 1 ) );

		$grouper = new FilteringStatementGrouper( array(
			'p1' => $this->newStatementFilter( 'P1' ),
		) );
		$groups = $grouper->groupStatements( $statements );

		$this->assertSame( array( 'p1', 'statements' ), array_keys( $groups ) );
	}

	public function testCanOverrideDefaultGroupPosition() {
		$statements = new StatementList();
		$statements->addNewStatement( new PropertyNoValueSnak( 1 ) );

		$grouper = new FilteringStatementGrouper( array(
			'statements' => null,
			'p1' => $this->newStatementFilter( 'P1' ),
		) );
		$groups = $grouper->groupStatements( $statements );

		$this->assertSame( array( 'statements', 'p1' ), array_keys( $groups ) );
	}

	public function testGroupStatements() {
		$statement1 = new Statement( new PropertyNoValueSnak( 1 ) );
		$statement2 = new Statement( new PropertyNoValueSnak( 2 ) );
		$statement3 = new Statement( new PropertyNoValueSnak( 1 ) );
		$statements = new StatementList( $statement1, $statement2, $statement3 );

		$grouper = new FilteringStatementGrouper( array(
			'p1' => $this->newStatementFilter( 'P1' ),
			'p2' => $this->newStatementFilter( 'P2' ),
			'p3' => $this->newStatementFilter( 'P3' ),
		) );
		$groups = $grouper->groupStatements( $statements );

		$expected = array(
			'p1' => new StatementList( $statement1, $statement3 ),
			'p2' => new StatementList( $statement2 ),
			'p3' => new StatementList(),
			'statements' => new StatementList(),
		);
		$this->assertEquals( $expected, $groups );
	}

}
