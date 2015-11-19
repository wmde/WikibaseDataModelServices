<?php

namespace Wikibase\DataModel\Services\Lookup;

use Wikibase\DataModel\Entity\PropertyId;

/**
 * Lookup service for URL patterns associated with property IDs.
 *
 * @since 3.2
 *
 * @licence GNU GPL v2+
 * @author Daniel Kinzler
 */
interface PropertyFormatterUrlLookup {

	/**
	 * Returns the formatter URL pattern associated with the given property.
	 * The URL pattern is used to construct full URLs from a StringValue associated
	 * with the property. The placeholder "$1" in the URL pattern is intended to be
	 * replaced with a url-encoded version of the StringValue.
	 *
	 * If no formatter URL pattern is defined for the given URL, this method returns
	 * null.
	 *
	 * @param PropertyId $propertyId
	 *
	 * @return string|null
	 */
	public function getUrlPatternForProperty( PropertyId $propertyId );

}
