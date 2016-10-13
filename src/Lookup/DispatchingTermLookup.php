<?php

namespace Wikibase\DataModel\Services\Lookup;

use Wikibase\DataModel\Entity\EntityId;
use Wikimedia\Assert\Assert;
use Wikimedia\Assert\ParameterAssertionException;

/**
 * Wrapper around repository-specific TermLookups
 * picking up the right lookup object for the particular input.
 *
 * @since 3.7
 *
 * @license GPL-2.0+
 */
class DispatchingTermLookup implements TermLookup {

	/**
	 * @var TermLookup[]
	 */
	private $lookups;

	/**
	 * @param TermLookup[] $lookups associative array with repository names (strings) as keys
	 *                              and TermLookup objects as values. Empty-string key
	 *                              defines lookup for the local repository.
	 *
	 * @throws ParameterAssertionException
	 */
	public function __construct( array $lookups ) {
		Assert::parameter(
			!empty( $lookups ) && array_key_exists( '', $lookups ),
			'$lookups',
			'must must not be empty and must contain an empty-string key'
		);
		Assert::parameterElementType( TermLookup::class, $lookups, '$lookups' );
		Assert::parameterElementType( 'string', array_keys( $lookups ), 'array_keys( $lookups )' );
		foreach ( array_keys( $lookups ) as $repositoryName ) {
			Assert::parameter(
				strpos( $repositoryName, ':' ) === false,
				'array_keys( $lookups )',
				'must not contain strings including colons'
			);
		}
		$this->lookups = $lookups;
	}

	/**
	 * @see TermLookup::getLabel
	 *
	 * @param EntityId $entityId
	 * @param string $languageCode
	 *
	 * @throws TermLookupException
	 * @throws UnknownForeignRepositoryException
	 *
	 * @return null|string
	 */
	public function getLabel( EntityId $entityId, $languageCode ) {
		return $this->getLookupForEntityId( $entityId )->getLabel( $entityId, $languageCode );
	}

	/**
	 * @see TermLookup::getLabels
	 *
	 * @param EntityId $entityId
	 * @param string[] $languageCodes
	 *
	 * @throws TermLookupException
	 * @throws UnknownForeignRepositoryException
	 *
	 * @return string[]
	 */
	public function getLabels( EntityId $entityId, array $languageCodes ) {
		return $this->getLookupForEntityId( $entityId )->getLabels( $entityId, $languageCodes );
	}

	/**
	 * @see TermLookup::getDescription
	 *
	 * @param EntityId $entityId
	 * @param string $languageCode
	 *
	 * @throws TermLookupException
	 * @throws UnknownForeignRepositoryException
	 *
	 * @return null|string
	 */
	public function getDescription( EntityId $entityId, $languageCode ) {
		return $this->getLookupForEntityId( $entityId )->getDescription( $entityId, $languageCode );
	}

	/**
	 * @see TermLookup::getDescriptions
	 *
	 * @param EntityId $entityId
	 * @param string[] $languageCodes
	 *
	 * @throws TermLookupException
	 * @throws UnknownForeignRepositoryException
	 *
	 * @return string[]
	 */
	public function getDescriptions( EntityId $entityId, array $languageCodes ) {
		return $this->getLookupForEntityId( $entityId )->getDescriptions( $entityId, $languageCodes );
	}

	/**
	 * @param EntityId $entityId
	 * @return TermLookup
	 */
	private function getLookupForEntityId( EntityId $entityId ) {
		$repo = $entityId->getRepositoryName();
		if ( !isset( $this->lookups[$repo] ) ) {
			throw new UnknownForeignRepositoryException( $entityId->getRepositoryName() );
		}
		return $this->lookups[$repo];
	}

}
