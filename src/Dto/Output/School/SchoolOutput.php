<?php

namespace App\Dto\Output\School;

use App\Entity\School\School;
use App\ObjectMapper\SchoolLogoUrlTransformer;
use App\ObjectMapper\SchoolRolesTransformer;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(source: School::class)]
class SchoolOutput
{
    public string $id;

    public string $name;

    #[Map(source: 'logoFilename', transform: SchoolLogoUrlTransformer::class)]
    public ?string $logoUrl;


    /** @var list<string> */
    #[Map(source: 'id', transform: SchoolRolesTransformer::class)]
    public array $roles;
}
