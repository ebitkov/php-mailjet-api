<?php

namespace ebitkov\Mailjet;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @template T of object
 * @extends ArrayCollection<int, T>
 */
final class Result extends ArrayCollection
{
    public function __construct(
        public readonly ?int $totalCount = null,
        array $elements = []
    ) {
        parent::__construct($elements);
    }
}
