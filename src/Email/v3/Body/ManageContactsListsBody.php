<?php

namespace ebitkov\Mailjet\Email\v3\Body;

use Symfony\Component\OptionsResolver\OptionsResolver;

final class ManageContactsListsBody extends OptionsResolver
{
    public const ACTIONS = ['addforce', 'addnoforce', 'remove', 'unsub'];


    public function __construct()
    {
        $this
            ->setDefault('ContactsLists', function (OptionsResolver $resolver) {
                $resolver
                    ->setPrototype(true)
                    ->setRequired(['ListID', 'Action'])
                    ->setAllowedTypes('ListID', 'int')
                    ->setAllowedValues('Action', self::ACTIONS);
            });
    }
}
