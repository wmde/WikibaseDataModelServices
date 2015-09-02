<?php

namespace Wikibase\DataModel\Services\Diff;

use Diff\Differ\MapDiffer;
use InvalidArgumentException;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Services\Diff\Internal\StatementListDiffer;
use Wikibase\DataModel\SiteLink;
use Wikibase\DataModel\SiteLinkList;
use Wikibase\DataModel\Statement\StatementList;
use Wikibase\DataModel\Statement\StatementListProvider;
use Wikibase\DataModel\Term\FingerprintProvider;

/**
 * @since 4.0
 *
 * @licence GNU GPL v2+
 * @author Thiemo Mättig
 */
class ItemAndPropertyDiffer implements EntityDifferStrategy {

	/**
	 * @var MapDiffer
	 */
	private $recursiveMapDiffer;

	/**
	 * @var StatementListDiffer
	 */
	private $statementListDiffer;

	public function __construct() {
		$this->recursiveMapDiffer = new MapDiffer( true );
		$this->statementListDiffer = new StatementListDiffer();
	}

	/**
	 * @since 4.0
	 *
	 * @param string $fromType
	 * @param string $toType
	 *
	 * @return bool
	 */
	public function canDiffEntityTypes( $fromType, $toType ) {
		return ( $fromType === 'item' || $fromType === 'property' )
			&& ( $toType === 'item' || $toType === 'property' );
	}

	/**
	 * @param EntityDocument $from
	 * @param EntityDocument $to
	 *
	 * @throws InvalidArgumentException
	 * @return EntityDiff
	 */
	public function diffEntities( EntityDocument $from, EntityDocument $to ) {
		if ( !( $from instanceof Item ) && !( $from instanceof Property ) ) {
			throw new InvalidArgumentException( '$from must be an instance of Item or Property' );
		}

		if ( !( $to instanceof Item ) && !( $to instanceof Property ) ) {
			throw new InvalidArgumentException( '$to must be an instance of Item or Property' );
		}

		return $this->diffItemAndProperty( $from, $to );
	}

	public function diffItemAndProperty( EntityDocument $from, EntityDocument $to ) {
		$diffOps = $this->recursiveMapDiffer->doDiff(
			$this->toDiffArray( $from ),
			$this->toDiffArray( $to )
		);

		$diffOps['claim'] = $this->statementListDiffer->getDiff(
			$this->getStatementList( $from ),
			$this->getStatementList( $to )
		);

		return new EntityDiff( $diffOps );
	}

	private function toDiffArray( EntityDocument $entity ) {
		$array = array();

		if ( $entity instanceof FingerprintProvider ) {
			$fingerprint = $entity->getFingerprint();

			$array['aliases'] = $fingerprint->getAliasGroups()->toTextArray();
			$array['label'] = $fingerprint->getLabels()->toTextArray();
			$array['description'] = $fingerprint->getDescriptions()->toTextArray();
		}

		if ( $entity instanceof Item ) {
			$siteLinks = $entity->getSiteLinkList();

			if ( !$siteLinks->isEmpty() ) {
				$array['links'] = $this->getLinksInDiffFormat( $siteLinks );
			}
		}

		return $array;
	}

	private function getLinksInDiffFormat( SiteLinkList $siteLinks ) {
		$links = array();

		/** @var SiteLink $siteLink */
		foreach ( $siteLinks as $siteLink ) {
			$links[$siteLink->getSiteId()] = array(
				'name' => $siteLink->getPageName(),
				'badges' => array_map(
					function( ItemId $id ) {
						return $id->getSerialization();
					},
					$siteLink->getBadges()
				)
			);
		}

		return $links;
	}

	private function getStatementList( EntityDocument $entity ) {
		return $entity instanceof StatementListProvider
			? $entity->getStatements()
			: new StatementList();
	}

	/**
	 * @param EntityDocument $entity
	 *
	 * @throws InvalidArgumentException
	 * @return EntityDiff
	 */
	public function getConstructionDiff( EntityDocument $entity ) {
		return $this->diffEntities( $this->newEmptyEntity( $entity ), $entity );
	}

	/**
	 * @param EntityDocument $entity
	 *
	 * @throws InvalidArgumentException
	 * @return EntityDiff
	 */
	public function getDestructionDiff( EntityDocument $entity ) {
		return $this->diffEntities( $entity, $this->newEmptyEntity( $entity ) );
	}

	/**
	 * @param EntityDocument $entity
	 *
	 * @throws InvalidArgumentException
	 * @return Item|Property
	 */
	private function newEmptyEntity( EntityDocument $entity ) {
		if ( $entity instanceof Item ) {
			return new Item();
		} elseif ( $entity instanceof Property ) {
			return Property::newFromType( '' );
		}

		throw new InvalidArgumentException( '$entity must be an instance of Item or Property' );
	}

}
