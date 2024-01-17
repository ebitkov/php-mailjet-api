<?php

namespace ebitkov\Mailjet\Email\v3\Filter;

final class ContactsListFilters extends PaginatedFilters
{
    public const ADDRESS = 'address';
    public const EXCLUDE_ID = 'ExcludeID';
    public const IS_DELETED = 'IsDeleted';
    public const NAME = 'Name';

    public function __construct()
    {
        $this->setDefined(self::ADDRESS);
        $this->setAllowedTypes(self::ADDRESS, 'string');

        $this->setDefined(self::EXCLUDE_ID);
        $this->setAllowedTypes(self::EXCLUDE_ID, 'int');

        $this->setDefined(self::IS_DELETED);
        $this->setAllowedTypes(self::IS_DELETED, 'bool');

        $this->setDefined(self::NAME);
        $this->setAllowedTypes(self::NAME, 'string');

        parent::__construct();
    }
}
