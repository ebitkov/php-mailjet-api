<?php

namespace ebitkov\Mailjet\Serializer\NameConverter;

use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class UpperCamelCaseToLowerCamelCaseNameConverter implements NameConverterInterface
{
    /**
     * @inheritDoc
     */
    public function normalize(string $propertyName): string
    {
        return ucfirst($propertyName);
    }

    /**
     * @inheritDoc
     */
    public function denormalize(string $propertyName): string
    {
        return lcfirst($propertyName);
    }
}
