<?php

namespace Wikibase\DataModel\Services\EntityId;

use ArrayIterator;
use Iterator;
use IteratorIterator;
use Wikibase\DataModel\Entity\EntityId;

/**
 * An entity ID pager that wraps an iterable and traverses it once.
 * It is not seekable or rewindable.
 *
 * @since 5.3
 * @license GPL-2.0-or-later
 */
class IterableEntityIdPager implements EntityIdPager {

	/** @var Iterator */
	private $iterator;

	/**
	 * @param iterable<EntityId> $iterable
	 */
	public function __construct( iterable $iterable ) {
		if ( $iterable instanceof Iterator ) {
			$this->iterator = $iterable;
		} elseif ( is_array( $iterable ) ) {
			$this->iterator = new ArrayIterator( $iterable );
		} else {
			$this->iterator = new IteratorIterator( $iterable );
		}
		$this->iterator->rewind();
	}

	/**
	 * @see EntityIdPager::fetchIds
	 *
	 * @param int $limit
	 *
	 * @return EntityId[]
	 */
	public function fetchIds( $limit ) {
		$ids = [];
		while ( $limit-- > 0 && $this->iterator->valid() ) {
			$ids[] = $this->iterator->current();
			$this->iterator->next();
		}
		return $ids;
	}

}
