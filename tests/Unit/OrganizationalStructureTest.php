<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\OrganizationalUnit;
use App\Models\Position;
use PHPUnit\Framework\TestCase;

class OrganizationalStructureTest extends TestCase
{
    public function test_organizational_units_follow_the_defined_parent_hierarchy(): void
    {
        self::assertSame([], OrganizationalUnit::ALLOWED_PARENT_TYPES['ceo']);
        self::assertSame(['ceo'], OrganizationalUnit::ALLOWED_PARENT_TYPES['chrgao']);
        self::assertSame(['ceo'], OrganizationalUnit::ALLOWED_PARENT_TYPES['cfo']);
        self::assertSame(['ceo'], OrganizationalUnit::ALLOWED_PARENT_TYPES['cpo']);
        self::assertSame(['chrgao', 'cfo', 'cpo'], OrganizationalUnit::ALLOWED_PARENT_TYPES['directorate']);
        self::assertSame(['directorate'], OrganizationalUnit::ALLOWED_PARENT_TYPES['division']);
        self::assertSame(['directorate'], OrganizationalUnit::ALLOWED_PARENT_TYPES['bureau']);
        self::assertSame(['division'], OrganizationalUnit::ALLOWED_PARENT_TYPES['subdivision']);
        self::assertSame(['division', 'cpo'], OrganizationalUnit::ALLOWED_PARENT_TYPES['workshop']);
    }

    public function test_position_categories_match_reporting_levels(): void
    {
        self::assertSame(1, Position::CATEGORY_LEVELS['c_suite']);
        self::assertSame(2, Position::CATEGORY_LEVELS['manager']);
        self::assertSame(2, Position::CATEGORY_LEVELS['head_of_bureau']);
        self::assertSame(3, Position::CATEGORY_LEVELS['assistant_manager']);
        self::assertSame(4, Position::CATEGORY_LEVELS['supervisor_staff']);
        self::assertSame(5, Position::CATEGORY_LEVELS['pelaksana']);
        self::assertSame('Eksekutif C-Suite (CEO/CHRGAO/CFO/CPO)', Position::CATEGORY_OPTIONS['c_suite']);
        self::assertSame('Kepala Biro', Position::CATEGORY_OPTIONS['head_of_bureau']);
    }
}
