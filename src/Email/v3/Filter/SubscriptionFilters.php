<?php

namespace ebitkov\Mailjet\Email\v3\Filter;

use DateTimeInterface;
use Symfony\Component\OptionsResolver\Options;

final class SubscriptionFilters extends PaginatedFilters
{
    public const BLOCKED = 'Blocked';
    public const CONTACT = 'Contact';
    public const CONTACT_EMAIL = 'ContactEmail';
    public const CONTACTS_LIST = 'ContactsList';
    public const IGNORE_DELETED = 'IgnoreDeleted';
    public const IS_EXCLUDED_FROM_CAMPAIGN = 'IsExcludedFromCampaign';
    public const LAST_ACTIVITY_AT = 'LastActivityAt';
    public const LIST_NAME = 'ListName';
    public const OPENED = 'Opened';
    public const UNSUB = 'Unsub';


    public function __construct()
    {
        $this->setDefined(self::BLOCKED);
        $this->setAllowedTypes(self::BLOCKED, 'bool');

        $this->setDefined(self::CONTACT);
        $this->setAllowedTypes(self::CONTACT, 'int');

        $this->setDefined(self::CONTACT_EMAIL);
        $this->setAllowedTypes(self::CONTACT_EMAIL, 'string');

        $this->setDefined(self::CONTACTS_LIST);
        $this->setAllowedTypes(self::CONTACTS_LIST, 'int');

        $this->setDefined(self::IGNORE_DELETED);
        $this->setAllowedTypes(self::IGNORE_DELETED, 'bool');

        $this->setDefined(self::IS_EXCLUDED_FROM_CAMPAIGN);
        $this->setAllowedTypes(self::IS_EXCLUDED_FROM_CAMPAIGN, 'bool');

        $this->setDefined(self::LAST_ACTIVITY_AT);
        $this->setAllowedTypes(self::LAST_ACTIVITY_AT, DateTimeInterface::class);
        $this->setNormalizer(
            self::LAST_ACTIVITY_AT,
            function (Options $options, DateTimeInterface $lastActivityAt): string {
                return $lastActivityAt->format(DateTimeInterface::RFC3339);
            }
        );

        $this->setDefined(self::LIST_NAME);
        $this->setAllowedTypes(self::LIST_NAME, 'string');

        $this->setDefined(self::OPENED);
        $this->setAllowedTypes(self::OPENED, 'bool');

        $this->setDefined(self::UNSUB);
        $this->setAllowedTypes(self::UNSUB, 'bool');

        parent::__construct();
    }
}
