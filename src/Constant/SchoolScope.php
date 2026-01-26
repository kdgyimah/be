<?php

namespace App\Constant;

abstract class SchoolScope
{
    public const string MANAGE_STUDENTS = 'MANAGE_STUDENTS';
    public const string MANAGE_PROGRAMS = 'MANAGE_PROGRAMS';
    public const string MANAGE_PROSPECTS = 'MANAGE_PROSPECTS';
    public const string MANAGER_FINANCE = 'MANAGER_FINANCE';
    public const string MANAGE_COMMUNICATIONS = 'MANAGE_COMMUNICATIONS';
    public const string SHOW = 'SHOW';

    /**
     * @return list<string>
     */
    public static function getScopes(): array
    {
        return [
            self::MANAGE_STUDENTS,
            self::MANAGE_PROGRAMS,
            self::MANAGE_PROSPECTS,
            self::MANAGER_FINANCE,
            self::MANAGE_COMMUNICATIONS,
            self::SHOW,
        ];
    }
}
