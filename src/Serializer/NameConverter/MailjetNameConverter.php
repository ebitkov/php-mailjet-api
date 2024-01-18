<?php

namespace ebitkov\Mailjet\Serializer\NameConverter;

use Symfony\Component\Serializer\NameConverter\AdvancedNameConverterInterface;

class MailjetNameConverter implements AdvancedNameConverterInterface
{
    private const SPECIAL_RULES = [
        'Uuid' => 'UUID',
        'uuid' => 'UUID',
        'Id' => 'ID',
        'id' => 'ID',
        'html' => 'HTML'
    ];

    /**
     * @param array<string, bool> $context
     */
    public function normalize(
        string $propertyName,
        string $class = null,
        string $format = null,
        array $context = []
    ): string {
        $default = new UpperCamelCaseToLowerCamelCaseNameConverter();

        $propertyName = str_replace(
            array_keys(self::SPECIAL_RULES),
            array_values(self::SPECIAL_RULES),
            $propertyName
        );

        return $default->normalize($propertyName);
    }

    /**
     * @param array<string, bool> $context
     */
    public function denormalize(
        string $propertyName,
        string $class = null,
        string $format = null,
        array $context = []
    ): string {
        $propertyName = str_replace(
            array_values(self::SPECIAL_RULES),
            array_keys(self::SPECIAL_RULES),
            $propertyName
        );

        return (new UpperCamelCaseToLowerCamelCaseNameConverter())->denormalize($propertyName);
    }
}
