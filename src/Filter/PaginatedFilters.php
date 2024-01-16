<?php

namespace ebitkov\Mailjet\Filter;

use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class PaginatedFilters extends OptionsResolver
{
    public const LIMIT = 'Limit';
    public const OFFSET = 'Offset';
    public const COUNT_ONLY = 'countOnly';
    public const SORT = 'Sort';

    public function __construct()
    {
        $this->setDefined(self::LIMIT);
        $this->setAllowedTypes(self::LIMIT, 'int');

        $this->setDefined(self::OFFSET);
        $this->setAllowedTypes(self::OFFSET, 'int');

        $this->setDefined(self::COUNT_ONLY);
        $this->setAllowedTypes(self::COUNT_ONLY, 'bool');

        $this->setDefined(self::SORT);
        $this->setAllowedTypes(self::SORT, 'string');
    }
}
