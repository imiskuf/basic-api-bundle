<?php

namespace Imiskuf\BasicApiBundle\Serializer\Handler;

use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\Exception\InvalidMetadataException;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use UnitEnum;

final class EnumHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods(): array
    {
        $methods = [];

        foreach (['json', 'xml'] as $format) {
            $methods[] = [
                'type' => 'enum',
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'format' => $format,
                'method' => 'deserializeEnum',
            ];
            $methods[] = [
                'type' => 'enum',
                'format' => $format,
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'method' => 'serializeEnum',
            ];
        }

        return $methods;
    }

    public function serializeEnum(
        SerializationVisitorInterface $visitor,
        UnitEnum $enum,
        array $type,
        SerializationContext $context
    ) {
        if ((isset($type['params'][1]) && 'value' === $type['params'][1]) || (!isset($type['params'][1]) && $enum instanceof \BackedEnum)) {
            if (!$enum instanceof \BackedEnum) {
                throw new InvalidMetadataException(sprintf('The type "%s" is not a backed enum, thus you can not use "value" as serialization mode for its value.', get_class($enum)));
            }

            $valueType = isset($type['params'][2]) ? ['name' => $type['params'][2]] : null;

            return $context->getNavigator()->accept($enum->value, $valueType);
        } else {
            return $context->getNavigator()->accept($enum->name);
        }
    }

    public function deserializeEnum(DeserializationVisitorInterface $visitor, $data, array $type): ?UnitEnum
    {
        $type = $type['params'][0]['name'] ?? null;
        if (null === $type || !is_a($type, \BackedEnum::class, true)) {
            throw new \LogicException();
        }

        return $type::from($data);
    }
}
