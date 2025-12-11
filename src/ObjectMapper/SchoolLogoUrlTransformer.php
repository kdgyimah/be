<?php

namespace App\ObjectMapper;

use App\Entity\School\School;
use Symfony\Component\ObjectMapper\TransformCallableInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class SchoolLogoUrlTransformer implements TransformCallableInterface
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function __invoke(mixed $value, object $source, ?object $target): ?string
    {
        if (!$source instanceof School) {
            throw new \RuntimeException();
        }

        dump($value);

        if ($value === null) {
            return null;
        }

        return $this->urlGenerator->generate('api_school_logo', ['id' => $source->id]);
    }
}
