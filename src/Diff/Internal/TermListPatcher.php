<?php

namespace Wikibase\DataModel\Services\Diff\Internal;

use Diff\DiffOp\AtomicDiffOp;
use Diff\DiffOp\Diff\Diff;
use Diff\DiffOp\DiffOpAdd;
use Diff\DiffOp\DiffOpChange;
use Diff\DiffOp\DiffOpRemove;
use Diff\Patcher\PatcherException;
use Wikibase\DataModel\Term\TermList;

/**
 * Package private.
 *
 * @since 3.6
 *
 * @license GPL-2.0+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Thiemo Mättig
 */
class TermListPatcher {

	/**
	 * @since 3.6
	 *
	 * @param TermList $terms
	 * @param Diff $patch
	 *
	 * @throws PatcherException
	 */
	public function patchTermList( TermList $terms, Diff $patch ) {
		foreach ( $patch as $lang => $diffOp ) {
			$this->patchTerm( $terms, $lang, $diffOp );
		}
	}

	/**
	 * @see MapPatcher
	 *
	 * @param TermList $terms
	 * @param string $lang
	 * @param AtomicDiffOp $diffOp
	 *
	 * @throws PatcherException
	 */
	private function patchTerm( TermList $terms, $lang, AtomicDiffOp $diffOp ) {
		$hasLang = $terms->hasTermForLanguage( $lang );

		switch ( true ) {
			case $diffOp instanceof DiffOpAdd:
				/** @var $diffOp DiffOpAdd */
				if ( !$hasLang ) {
					$terms->setTextForLanguage( $lang, $diffOp->getNewValue() );
				}
				break;

			case $diffOp instanceof DiffOpChange:
				/** @var $diffOp DiffOpChange */
				if ( $hasLang
					&& $terms->getByLanguage( $lang )->getText() === $diffOp->getOldValue()
				) {
					$terms->setTextForLanguage( $lang, $diffOp->getNewValue() );
				}
				break;

			case $diffOp instanceof DiffOpRemove:
				/** @var $diffOp DiffOpRemove */
				if ( $hasLang
					&& $terms->getByLanguage( $lang )->getText() === $diffOp->getOldValue()
				) {
					$terms->removeByLanguage( $lang );
				}
				break;

			default:
				throw new PatcherException( 'Invalid terms diff' );
		}
	}

}
