<?php

namespace Wikibase\DataModel\Services\Diff;

use RuntimeException;
use Wikibase\DataModel\Entity\EntityDocument;

/**
 * @since 1.0
 *
 * @license GPL-2.0+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EntityDiffer {

	/**
	 * @var EntityDifferStrategy[]
	 */
	private $differStrategies;

	public function __construct() {
		$this->registerEntityDifferStrategy( new ItemDiffer() );
		$this->registerEntityDifferStrategy( new PropertyDiffer() );
		$this->registerEntityDifferStrategy( new ItemAndPropertyDiffer() );
	}

	public function registerEntityDifferStrategy( EntityDifferStrategy $differ ) {
		$this->differStrategies[] = $differ;
	}

	/**
	 * @param EntityDocument $from
	 * @param EntityDocument $to
	 *
	 * @return EntityDiff
	 * @throws RuntimeException
	 */
	public function diffEntities( EntityDocument $from, EntityDocument $to ) {
		$differ = $this->getDiffStrategy( $from->getType(), $to->getType() );
		return $differ->diffEntities( $from, $to );
	}

	/**
	 * @param string $fromType
	 * @param string $toType
	 *
	 * @throws RuntimeException
	 * @return EntityDifferStrategy
	 */
	private function getDiffStrategy( $fromType, $toType ) {
		foreach ( $this->differStrategies as $differ ) {
			if ( $differ->canDiffEntityTypes( $fromType, $toType ) ) {
				return $differ;
			}
		}

		throw new RuntimeException( 'Diffing the provided types of entities is not supported' );
	}

	/**
	 * @param EntityDocument $entity
	 *
	 * @return EntityDiff
	 */
	public function getConstructionDiff( EntityDocument $entity ) {
		$differ = $this->getDiffStrategy( $entity->getType(), $entity->getType() );
		return $differ->getConstructionDiff( $entity );
	}

	/**
	 * @param EntityDocument $entity
	 *
	 * @return EntityDiff
	 */
	public function getDestructionDiff( EntityDocument $entity ) {
		$differ = $this->getDiffStrategy( $entity->getType(), $entity->getType() );
		return $differ->getDestructionDiff( $entity );
	}

}
