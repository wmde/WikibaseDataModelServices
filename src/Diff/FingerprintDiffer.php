<?php

namespace Wikibase\DataModel\Services\Diff;

use Wikibase\DataModel\Term\AliasGroup;
use Wikibase\DataModel\Term\Fingerprint;

/**
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class FingerprintDiffer {

	/**
	 * @param Fingerprint $first
	 * @param Fingerprint $second
	 *
	 * @return Fingerprint Contains the terms present in $first that are not present in $second
	 */
	public function diff( Fingerprint $first, Fingerprint $second ): Fingerprint {
		$difference = new Fingerprint();

		foreach ( $first->getLabels() as $term ) {
			if ( !$second->getLabels()->hasTerm( $term ) ) {
				$difference->getLabels()->setTerm( $term );
			}
		}

		foreach ( $first->getDescriptions() as $term ) {
			if ( !$second->getDescriptions()->hasTerm( $term ) ) {
				$difference->getDescriptions()->setTerm( $term );
			}
		}

		foreach ( $first->getAliasGroups() as $aliasGroup ) {
			$difference->setAliasGroup(
				$aliasGroup->getLanguageCode(),
				$this->diffAliasGroup( $aliasGroup, $second )
			);
		}

		return $difference;
	}

	private function diffAliasGroup( AliasGroup $aliasGroup, Fingerprint $second ) {
		if ( $second->hasAliasGroup( $aliasGroup->getLanguageCode() ) ) {
			$secondAliases = $second->getAliasGroup( $aliasGroup->getLanguageCode() )->getAliases();
			$differenceAliases = [];

			foreach ( $aliasGroup->getAliases() as $aliasText ) {
				if ( !in_array( $aliasText, $secondAliases ) ) {
					$differenceAliases[] = $aliasText;
				}
			}

			return $differenceAliases;
		}

		return $aliasGroup->getAliases();
	}

}
