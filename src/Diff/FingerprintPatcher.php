<?php

namespace Wikibase\DataModel\Services\Diff;

use Diff\DiffOp\Diff\Diff;
use Diff\Patcher\PatcherException;
use Wikibase\DataModel\Services\Diff\Internal\AliasGroupListPatcher;
use Wikibase\DataModel\Term\Fingerprint;

/**
 * @since 3.13
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Thiemo Kreuz
 */
class FingerprintPatcher {

	/**
	 * @var TermListPatcher
	 */
	private $termListPatcher;

	/**
	 * @var AliasGroupListPatcher
	 */
	private $aliasGroupListPatcher;

	public function __construct() {
		$this->termListPatcher = new TermListPatcher();
		$this->aliasGroupListPatcher = new AliasGroupListPatcher();
	}

	/**
	 * @param Fingerprint $fingerprint
	 * @param Diff $patch typically an {@link EntityDiff}
	 *
	 * @throws PatcherException
	 */
	public function patchFingerprint( Fingerprint $fingerprint, Diff $patch ) {
		if ( !$patch->isAssociative() ) {
			throw new PatcherException( '$patch must be associative' );
		}

		if ( isset( $patch['label'] ) ) {
			$this->termListPatcher->patchTermList(
				$fingerprint->getLabels(),
				$patch['label']
			);
		}

		if ( isset( $patch['description'] ) ) {
			$this->termListPatcher->patchTermList(
				$fingerprint->getDescriptions(),
				$patch['description']
			);
		}

		if ( isset( $patch['aliases'] ) ) {
			$this->aliasGroupListPatcher->patchAliasGroupList(
				$fingerprint->getAliasGroups(),
				$patch['aliases']
			);
		}
	}

}
