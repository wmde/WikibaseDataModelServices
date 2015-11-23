<?php

namespace Wikibase\DataModel\Services\Statement\Grouper;

use Wikibase\DataModel\Statement\StatementList;

/**
 * @since 3.2
 *
 * @licence GNU GPL v2+
 * @author Thiemo Mättig
 */
class ByPropertyIdStatementGrouper implements StatementGrouper {

	/**
	 * @param StatementList $statements
	 *
	 * @return StatementList[] An associative array, mapping property id serializations to
	 *  StatementList objects.
	 */
	public function groupStatements( StatementList $statements ) {
		/** @var StatementList[] $groups */
		$groups = array();

		foreach ( $statements->toArray() as $statement ) {
			$id = $statement->getPropertyId()->getSerialization();

			if ( !isset( $groups[$id] ) ) {
				$groups[$id] = new StatementList();
			}

			$groups[$id]->addStatement( $statement );
		}

		return $groups;
	}

}
