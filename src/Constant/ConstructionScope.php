<?php

namespace App\Constant;

abstract class ConstructionScope
{
    public const string LIST_EMPLOYEES = 'LIST_EMPLOYEES';
    public const string MANAGE_EMPLOYEES = 'MANAGE_EMPLOYEES';

    /**
     * @return list<string>
     */
    public static function getScopes(): array
    {
        return [self::LIST_EMPLOYEES, self::MANAGE_EMPLOYEES];
    }
}
