<?php

namespace ebitkov\Mailjet\Filter;

use Symfony\Component\OptionsResolver\OptionsResolver;

final class ContactFilter extends OptionsResolver
{
    public const CAMPAIGN = 'Campaign';
    public const CONTACTS_LIST = 'ContactsList';
    public const IS_EXCLUDED_FROM_CAMPAIGN = 'IsExcludedFromCampaign';
    public const LIMIT = 'Limit';
    public const OFFSET = 'Offset';
    public const COUNT_ONLY = 'countOnly';
    public const SORT = 'Sort';

    public function __construct()
    {
        $this->setDefined(self::CAMPAIGN);
        $this->setAllowedTypes(self::CAMPAIGN, 'int');

        $this->setDefined(self::CONTACTS_LIST);
        $this->setAllowedTypes(self::CONTACTS_LIST, 'int');

        $this->setDefined(self::IS_EXCLUDED_FROM_CAMPAIGN);
        $this->setAllowedTypes(self::IS_EXCLUDED_FROM_CAMPAIGN, 'bool');

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
