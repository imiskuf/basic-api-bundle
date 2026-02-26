<?php

namespace Imiskuf\BasicApiBundle\EventSubscriber;

use Imiskuf\BasicApiBundle\Annotation\Link;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Metadata\StaticPropertyMetadata;
use ReflectionObject;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Routing\RouterInterface;

class LinkSerializerSubscriber implements EventSubscriberInterface
{
    private RouterInterface $router;

    private ExpressionLanguage $expressionLanguage;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
        $this->expressionLanguage = new ExpressionLanguage();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            [
                'event' => 'serializer.post_serialize',
                'method' => 'onPostSerialize',
                'format' => 'json'
            ]
        ];
    }

    public function onPostSerialize(ObjectEvent $event): void
    {
        /** @var JsonSerializationVisitor $visitor */
        $visitor = $event->getVisitor();

        $object = $event->getObject();
        $reflection = new ReflectionObject($object);

        $attributes = $reflection->getAttributes(Link::class);
        $links = [];

        foreach ($attributes as $attribute) {
            /** @var Link $link */
            $link = $attribute->newInstance();

            $links[$link->name] = $this->router->generate(
                $link->route,
                $this->resolveParameters($link->parameters, $object)
            );
        }

        if ($links) {
            $visitor->visitProperty(new StaticPropertyMetadata(null, '_links', $links), null);
        }
    }

    private function resolveParameters(array $parameters, object $object): array
    {
        foreach ($parameters as $key => $value) {
            $parameters[$key] = $this->expressionLanguage->evaluate($value, ['object' => $object]);
        }

        return $parameters;
    }
}
