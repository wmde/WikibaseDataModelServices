<?php

namespace Wikibase\DataModel\Services\Statement;

use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Statement\StatementList;

/**
 * A collection of higher level utility functions to manipulate StatementList objects.
 *
 * @since 3.7
 *
 * @license GPL-2.0+
 * @author Thiemo Mättig
 */
class StatementListChanger {

	/**
	 * @var StatementList
	 */
	private $statementList;

	public function __construct( StatementList $statementList ) {
		$this->statementList = $statementList;
	}

	/**
	 * Makes sure all statements with the same property are next to each other (forming a group),
	 * and reorders them if necessary. The position of the group in the list is determined by the
	 * first statement with the same property.
	 */
	public function groupByProperty() {
		$byId = [];

		foreach ( $this->statementList->toArray() as $statement ) {
			$id = $statement->getPropertyId()->getSerialization();
			$byId[$id][] = $statement;
		}

		// FIXME: Use StatementList::clear, see https://github.com/wmde/WikibaseDataModel/pull/649
		$this->clear();

		foreach ( $byId as $statements ) {
			foreach ( $statements as $statement ) {
				$this->statementList->addStatement( $statement );
			}
		}
	}

	private function clear() {
		foreach ( $this->statementList->toArray() as $statement ) {
			$this->statementList->removeStatementsWithGuid( $statement->getGuid() );
		}
	}

	/**
	 * Adds a new statement to the list while respecting existing PropertyId groups.
	 *
	 * In contrast to the previously used ByPropertyIdArray implementation this method never moves
	 * existing statements around. The provided index is only a hint. The new statement is either
	 * added to an existing group of statements with the same PropertyId, or at a group border close
	 * to the provided index.
	 *
	 * @param Statement $newStatement
	 * @param int|null $index An absolute index in the list. If the index is not next to a statement
	 *  with the same property, the closest possible position is used instead. Default is null,
	 *  which adds the new statement after the last statement with the same property, or to the end.
	 */
	public function addToGroup( Statement $newStatement, $index = null ) {
		$statements = $this->statementList->toArray();
		$id = $newStatement->getPropertyId();

		if ( $index === null ) {
			$index = $this->getLastIndexWithinGroup( $statements, $id );
		} else {
			// Limit search range to avoid looping non-existing positions
			$validIndex = min( max( 0, $index ), count( $statements ) );
			$index = $this->getClosestIndexWithinGroup( $statements, $id, $validIndex );
			if ( $index === null ) {
				$index = $this->getClosestIndexAtGroupBorder( $statements, $validIndex );
			}
		}

		$this->statementList->addStatement( $newStatement, $index );
	}

	/**
	 * @param Statement[] $statements
	 * @param PropertyId $id
	 *
	 * @return int|null
	 */
	private function getLastIndexWithinGroup( array $statements, PropertyId $id ) {
		// Start searching from the end and stop at the first match
		for ( $i = count( $statements ); $i > 0; $i-- ) {
			if ( $statements[$i - 1]->getPropertyId()->equals( $id ) ) {
				return $i;
			}
		}

		return null;
	}

	/**
	 * @param Statement[] $statements
	 * @param PropertyId $id
	 * @param int $index
	 *
	 * @return int|null
	 */
	private function getClosestIndexWithinGroup( array $statements, PropertyId $id, $index ) {
		$longestDistance = max( $index, count( $statements ) - $index );

		for ( $i = 0; $i <= $longestDistance; $i++ ) {
			if ( $this->isWithinGroup( $statements, $id, $index - $i ) ) {
				return $index - $i;
			} elseif ( $i && $this->isWithinGroup( $statements, $id, $index + $i ) ) {
				return $index + $i;
			}
		}

		return null;
	}

	/**
	 * @param Statement[] $statements
	 * @param int $index
	 *
	 * @return int|null
	 */
	private function getClosestIndexAtGroupBorder( array $statements, $index ) {
		$longestDistance = max( $index, count( $statements ) - $index );

		for ( $i = 0; $i <= $longestDistance; $i++ ) {
			if ( $this->isGroupBorder( $statements, $index - $i ) ) {
				return $index - $i;
			} elseif ( $i && $this->isGroupBorder( $statements, $index + $i ) ) {
				return $index + $i;
			}
		}

		return null;
	}

	/**
	 * @param Statement[] $statements
	 * @param PropertyId $id
	 * @param int $index
	 *
	 * @return bool
	 */
	private function isWithinGroup( array $statements, PropertyId $id, $index ) {
		$count = count( $statements );

		// Valid if the index either precedes or succeeds a statement with the same property
		return $index > 0 && $index <= $count && $statements[$index - 1]->getPropertyId()->equals( $id )
			|| $index >= 0 && $index < $count && $statements[$index]->getPropertyId()->equals( $id );
	}

	/**
	 * @param Statement[] $statements
	 * @param int $index
	 *
	 * @return bool
	 */
	private function isGroupBorder( array $statements, $index ) {
		// First and last possible position is always a border
		return $index <= 0
			|| $index >= count( $statements )
			|| !$statements[$index - 1]->getPropertyId()->equals( $statements[$index]->getPropertyId() );
	}

}
