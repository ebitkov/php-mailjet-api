<?php

namespace ebitkov\Mailjet\Email\v3\Body;

use Symfony\Component\OptionsResolver\OptionsResolver;

final class BulkManageContactsBody extends OptionsResolver
{
    public function __construct()
    {
        $this
            ->setDefault('Contacts', function (OptionsResolver $contactsResolver) {
                $contactsResolver
                    ->setPrototype(true)
                    ->setRequired('Email')
                    ->setDefined(['Name', 'IsExcludedFromCampaigns', 'Properties'])
                    ->setAllowedTypes('Email', 'string')
                    ->setAllowedTypes('Name', 'string')
                    ->setAllowedTypes('IsExcludedFromCampaigns', 'bool')
                    ->setAllowedTypes('Properties', 'array');
            })
            ->setDefault('ContactsLists', function (OptionsResolver $listResolver) {
                $listResolver
                    ->setPrototype(true)
                    ->setRequired(['ListID', 'Action'])
                    ->setAllowedValues('Action', ['addforce', 'addnoforce', 'remove', 'unsub']);
            });
    }
}
