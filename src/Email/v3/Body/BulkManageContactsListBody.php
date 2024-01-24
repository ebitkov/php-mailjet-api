<?php

namespace ebitkov\Mailjet\Email\v3\Body;

use Symfony\Component\OptionsResolver\OptionsResolver;

final class BulkManageContactsListBody extends OptionsResolver
{
    public function __construct()
    {
        $this
            ->setPrototype(true)
            ->setRequired(['ListID', 'Action'])
            ->setAllowedValues('Action', ['addforce', 'addnoforce', 'remove', 'unsub']);
    }
}
