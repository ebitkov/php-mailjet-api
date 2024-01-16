<?php

namespace ebitkov\Mailjet\Tests\Filter;

use ebitkov\Mailjet\Filter\ListRecipientFilters;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

class ListRecipientFiltersTest extends TestCase
{
    public function testValidData(): void
    {
        $resolver = new ListRecipientFilters();
        $now = new \DateTime();
        $in = [
            'Blocked' => true,
            'Contact' => 12,
            'ContactEmail' => 'max.mustermann@beispiel.de',
            'ContactsList' => 124,
            'IgnoreDeleted' => false,
            'IsExcludedFromCampaign' => true,
            'LastActivityAt' => $now,
            'ListName' => 'hello',
            'Opened' => false,
            'Unsub' => true,
            'Limit' => 1,
            'Offset' => 3,
            'Sort' => 'IsActive+Desc'
        ];
        $out = $resolver->resolve($in);

        $this->assertSame([
            'Blocked' => true,
            'Contact' => 12,
            'ContactEmail' => 'max.mustermann@beispiel.de',
            'ContactsList' => 124,
            'IgnoreDeleted' => false,
            'IsExcludedFromCampaign' => true,
            'LastActivityAt' => $now->format(\DateTimeInterface::RFC3339),
            'ListName' => 'hello',
            'Opened' => false,
            'Unsub' => true,
            'Limit' => 1,
            'Offset' => 3,
            'Sort' => 'IsActive+Desc'
        ], $out);
    }

    public function testUndefinedOption(): void
    {
        $this->expectException(UndefinedOptionsException::class);

        $resolver = new ListRecipientFilters();
        $resolver->resolve(['invalidArgument' => 'hello']);
    }

    public function testInvalidTypeForOption(): void
    {
        $this->expectException(InvalidOptionsException::class);

        $resolver = new ListRecipientFilters();
        $resolver->resolve(['Contact' => 'hello']);
    }
}
