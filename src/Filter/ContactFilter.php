<?php

namespace ebitkov\Mailjet\Filter;

final class ContactFilter extends PaginatedFilters
{
    public const CAMPAIGN = 'Campaign';
    public const CONTACTS_LIST = 'ContactsList';
    public const IS_EXCLUDED_FROM_CAMPAIGN = 'IsExcludedFromCampaign';

    public function __construct()
    {
        $this->setDefined(self::CAMPAIGN);
        $this->setAllowedTypes(self::CAMPAIGN, 'int');

        $this->setDefined(self::CONTACTS_LIST);
        $this->setAllowedTypes(self::CONTACTS_LIST, 'int');

        $this->setDefined(self::IS_EXCLUDED_FROM_CAMPAIGN);
        $this->setAllowedTypes(self::IS_EXCLUDED_FROM_CAMPAIGN, 'bool');

        parent::__construct();
    }
}
