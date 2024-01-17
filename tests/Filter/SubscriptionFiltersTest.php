<?php

namespace ebitkov\Mailjet\Tests\Filter;

use ebitkov\Mailjet\Email\v3\Filter\SubscriptionFilters;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

class SubscriptionFiltersTest extends TestCase
{
    public function testValidData(): void
    {
        $resolver = new SubscriptionFilters();
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

        $resolver = new SubscriptionFilters();
        $resolver->resolve(['invalidArgument' => 'hello']);
    }

    public function testInvalidTypeForOption(): void
    {
        $this->expectException(InvalidOptionsException::class);

        $resolver = new SubscriptionFilters();
        $resolver->resolve(['Contact' => 'hello']);
    }
}
