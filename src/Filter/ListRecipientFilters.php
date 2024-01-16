<?php

namespace ebitkov\Mailjet\Filter;

use DateTimeInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ListRecipientFilters extends OptionsResolver
{
    public function __construct()
    {
        $this->setDefined('Blocked');
        $this->setAllowedTypes('Blocked', 'bool');

        $this->setDefined('Contact');
        $this->setAllowedTypes('Contact', 'int');

        $this->setDefined('ContactEmail');
        $this->setAllowedTypes('ContactEmail', 'string');

        $this->setDefined('ContactsList');
        $this->setAllowedTypes('ContactsList', 'int');

        $this->setDefined('IgnoreDeleted');
        $this->setAllowedTypes('IgnoreDeleted', 'bool');

        $this->setDefined('IsExcludedFromCampaign');
        $this->setAllowedTypes('IsExcludedFromCampaign', 'bool');

        $this->setDefined('LastActivityAt');
        $this->setAllowedTypes('LastActivityAt', DateTimeInterface::class);
        $this->setNormalizer('LastActivityAt', function (Options $options, DateTimeInterface $lastActivityAt): string {
            return $lastActivityAt->format(DateTimeInterface::RFC3339);
        });

        $this->setDefined('ListName');
        $this->setAllowedTypes('ListName', 'string');

        $this->setDefined('Opened');
        $this->setAllowedTypes('Opened', 'bool');

        $this->setDefined('Unsub');
        $this->setAllowedTypes('Unsub', 'bool');

        $this->setDefined('Limit');
        $this->setAllowedTypes('Limit', 'int');

        $this->setDefined('Offset');
        $this->setAllowedTypes('Offset', 'int');

        $this->setDefined('countOnly');
        $this->setAllowedTypes('countOnly', 'bool');

        $this->setDefined('Sort');
        $this->setAllowedTypes('Sort', 'string');
    }
}
