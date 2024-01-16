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
        $propertyName = str_replace(['Id', 'id'], 'ID', $propertyName);
        return ucfirst($propertyName);
    }

    /**
     * @inheritDoc
     */
    public function denormalize(string $propertyName): string
    {
        $propertyName = str_replace('ID', 'Id', $propertyName);
        return lcfirst($propertyName);
    }
}
