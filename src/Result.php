<?php

namespace ebitkov\Mailjet;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @template T of object
 * @extends ArrayCollection<int, T>
 */
final class Result extends ArrayCollection
{
    /**
     * @param array<T> $elements
     */
    public function __construct(
        public readonly int $totalCount = 0,
        array $elements = []
    ) {
        parent::__construct($elements);
    }
}