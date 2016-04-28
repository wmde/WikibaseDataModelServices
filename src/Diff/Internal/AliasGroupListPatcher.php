<?php

namespace Wikibase\DataModel\Services\Diff\Internal;

use Diff\DiffOp\Diff\Diff;
use Diff\DiffOp\DiffOp;
use Diff\DiffOp\DiffOpAdd;
use Diff\DiffOp\DiffOpChange;
use Diff\DiffOp\DiffOpRemove;
use Diff\Patcher\MapPatcher;
use Diff\Patcher\PatcherException;
use Wikibase\DataModel\Term\AliasGroupList;

/**
 * Package private.
 *
 * @since 3.6
 *
 * @license GPL-2.0+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Thiemo Mättig
 */
class AliasGroupListPatcher {

	/**
	 * @since 3.6
	 *
	 * @param AliasGroupList $groups
	 * @param Diff $patch
	 *
	 * @throws PatcherException
	 */
	public function patchAliasGroupList( AliasGroupList $groups, Diff $patch ) {
		foreach ( $patch as $lang => $diffOp ) {
			$this->patchAliasGroup( $groups, $lang, $diffOp );
		}
	}

	/**
	 * @see MapPatcher
	 *
	 * @param AliasGroupList $groups
	 * @param string $lang
	 * @param DiffOp $diffOp
	 *
	 * @throws PatcherException
	 */
	private function patchAliasGroup( AliasGroupList $groups, $lang, DiffOp $diffOp ) {
		$hasLang = $groups->hasGroupForLanguage( $lang );

		switch ( true ) {
			case $diffOp instanceof DiffOpAdd:
				/** @var $diffOp DiffOpAdd */
				if ( !$hasLang ) {
					$groups->setAliasesForLanguage( $lang, $diffOp->getNewValue() );
				}
				break;

			case $diffOp instanceof DiffOpChange:
				/** @var $diffOp DiffOpChange */
				$this->applyAliasGroupChange( $groups, $lang, $diffOp );
				break;

			case $diffOp instanceof DiffOpRemove:
				/** @var $diffOp DiffOpRemove */
				if ( $hasLang
					&& $groups->getByLanguage( $lang )->getAliases() === $diffOp->getOldValue()
				) {
					$groups->removeByLanguage( $lang );
				}
				break;

			case $diffOp instanceof Diff:
				/** @var $diffOp Diff */
				$this->applyAliasGroupDiff( $groups, $lang, $diffOp );
				break;

			default:
				throw new PatcherException( 'Invalid aliases diff' );
		}
	}

	/**
	 * @param AliasGroupList $groups
	 * @param string $lang
	 * @param DiffOpChange $patch
	 */
	private function applyAliasGroupChange( AliasGroupList $groups, $lang, DiffOpChange $patch ) {
		if ( $groups->hasGroupForLanguage( $lang )
			&& $groups->getByLanguage( $lang )->getAliases() === $patch->getOldValue()
		) {
			$groups->setAliasesForLanguage( $lang, $patch->getNewValue() );
		}
	}

	/**
	 * @param AliasGroupList $groups
	 * @param string $lang
	 * @param Diff $patch
	 */
	private function applyAliasGroupDiff( AliasGroupList $groups, $lang, Diff $patch ) {
		$hasLang = $groups->hasGroupForLanguage( $lang );

		if ( $hasLang || !$this->containsOperationsOnOldValues( $patch ) ) {
			$aliases = $hasLang ? $groups->getByLanguage( $lang )->getAliases() : array();
			$aliases = $this->getPatchedAliases( $aliases, $patch );
			$groups->setAliasesForLanguage( $lang, $aliases );
		}
	}

	/**
	 * @param Diff $diff
	 *
	 * @return bool
	 */
	private function containsOperationsOnOldValues( Diff $diff ) {
		return $diff->getChanges() !== array()
			|| $diff->getRemovals() !== array();
	}

	/**
	 * @see ListPatcher
	 *
	 * @param string[] $aliases
	 * @param Diff $patch
	 *
	 * @throws PatcherException
	 * @return string[]
	 */
	private function getPatchedAliases( array $aliases, Diff $patch ) {
		foreach ( $patch as $diffOp ) {
			switch ( true ) {
				case $diffOp instanceof DiffOpAdd:
					/** @var $diffOp DiffOpAdd */
					$aliases[] = $diffOp->getNewValue();
					break;

				case $diffOp instanceof DiffOpChange:
					/** @var $diffOp DiffOpChange */
					$key = array_search( $diffOp->getOldValue(), $aliases, true );
					if ( $key !== false ) {
						unset( $aliases[$key] );
						$aliases[] = $diffOp->getNewValue();
					}
					break;

				case $diffOp instanceof DiffOpRemove:
					/** @var $diffOp DiffOpRemove */
					$key = array_search( $diffOp->getOldValue(), $aliases, true );
					if ( $key !== false ) {
						unset( $aliases[$key] );
					}
					break;

				default:
					throw new PatcherException( 'Invalid aliases diff' );
			}
		}

		return $aliases;
	}

}
