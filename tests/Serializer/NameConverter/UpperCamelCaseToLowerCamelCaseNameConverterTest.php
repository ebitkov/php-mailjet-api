<?php

namespace ebitkov\Mailjet\Tests\Serializer\NameConverter;

use ebitkov\Mailjet\Serializer\NameConverter\UpperCamelCaseToLowerCamelCaseNameConverter;
use PHPUnit\Framework\TestCase;

class UpperCamelCaseToLowerCamelCaseNameConverterTest extends TestCase
{
    /**
     * @return array<array<int, string>>
     */
    public static function getData(): array
    {
        return [
            ['IsUnsubscribed', 'isUnsubscribed'],
            ['ContactID', 'contactId'],
            ['ID', 'id'],
            ['IsActive', 'isActive'],
            ['ListID', 'listId'],
            ['ListName', 'listName'],
            ['SubscribedAt', 'subscribedAt'],
            ['UnsubscribedAt', 'unsubscribedAt'],
        ];
    }

    /**
     * @dataProvider getData
     */
    public function testNormalize(string $normalized, string $denormalized): void
    {
        $converter = new UpperCamelCaseToLowerCamelCaseNameConverter();
        $this->assertSame($normalized, $converter->normalize($denormalized));
    }

    /**
     * @dataProvider getData
     */
    public function testDenormalize(string $normalized, string $denormalized): void
    {
        $converter = new UpperCamelCaseToLowerCamelCaseNameConverter();
        $this->assertSame($denormalized, $converter->denormalize($normalized));
    }
}
