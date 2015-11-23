<?php

namespace Wikibase\DataModel\Services\Statement\Grouper;

use Wikibase\DataModel\Statement\StatementList;

/**
 * @since 3.2
 *
 * @licence GNU GPL v2+
 * @author Thiemo Mättig
 */
interface StatementGrouper {

	/**
	 * @param StatementList $statements
	 *
	 * @return StatementList[] An associative array, mapping statement group identifiers to
	 *  StatementList objects. Implementations should use "statements" as the default group
	 *  identifier, if not requested otherwise.
	 */
	public function groupStatements( StatementList $statements );

}
