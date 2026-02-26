<?php

namespace Imiskuf\BasicApiBundle\EventSubscriber;

use Imiskuf\BasicApiBundle\Annotation\Link;
use Doctrine\Common\Annotations\Reader;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Metadata\StaticPropertyMetadata;
use ReflectionObject;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Routing\RouterInterface;

class LinkSerializerSubscriber implements EventSubscriberInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var Reader|null
     */
    private $annotationReader;

    /**
     * @var ExpressionLanguage
     */
    private $expressionLanguage;

    /**
     * @param RouterInterface $router
     * @param Reader|null $annotationReader
     */
    public function __construct(
        RouterInterface $router,
        Reader $annotationReader = null
    ) {
        $this->router = $router;
        $this->annotationReader = $annotationReader;
        $this->expressionLanguage = new ExpressionLanguage();
    }

    /**
     * @return array
     */
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
        $annotations = [];

        if (null !== $this->annotationReader) {
            $annotations = $this->annotationReader->getClassAnnotations($reflection);
        }

        $attributes = $reflection->getAttributes(Link::class);
        foreach ($attributes as $attribute) {
            $annotations[] = $attribute->newInstance();
        }

        $links = [];
        foreach ($annotations as $annotation) {
            if (!$annotation instanceof Link) {
                continue;
            }

            $links[$annotation->name] = $this->router->generate(
                $annotation->route,
                $this->resolveParameters($annotation->parameters, $object)
            );
        }

        if ($links) {
            $visitor->visitProperty(new StaticPropertyMetadata(null, '_links', $links), null);
        }
    }

    /**
     * @param array $parameters
     * @param $object
     * @return array
     */
    private function resolveParameters(array $parameters, $object): array
    {
        foreach ($parameters as $key => $value) {
            $parameters[$key] = $this->expressionLanguage->evaluate($value, ['object' => $object]);
        }

        return $parameters;
    }
}
