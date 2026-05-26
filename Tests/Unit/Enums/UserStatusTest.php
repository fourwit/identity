<?php

namespace Modules\Identity\Tests\Unit\Enums;

use PHPUnit\Framework\TestCase;
use Modules\Identity\Enums\UserStatus;

class UserStatusTest extends TestCase
{
    /** @test */
    public function test_enum_has_correct_values()
    {
        $this->assertEquals('active', UserStatus::ACTIVE->value);
        $this->assertEquals('inactive', UserStatus::INACTIVE->value);
        $this->assertEquals('suspended', UserStatus::SUSPENDED->value);
        $this->assertEquals('pending', UserStatus::PENDING->value);
    }

    /** @test */
    public function test_label_method_returns_correct_labels()
    {
        $this->assertEquals('Active', UserStatus::ACTIVE->label());
        $this->assertEquals('Inactive', UserStatus::INACTIVE->label());
        $this->assertEquals('Suspended', UserStatus::SUSPENDED->label());
        $this->assertEquals('Pending', UserStatus::PENDING->label());
    }

    /** @test */
    public function test_values_method_returns_all_statuses()
    {
        $values = UserStatus::values();

        $this->assertContains('active', $values);
        $this->assertContains('inactive', $values);
        $this->assertContains('suspended', $values);
        $this->assertContains('pending', $values);
        $this->assertCount(4, $values);
    }

    /** @test */
    public function test_is_active_method()
    {
        $this->assertTrue(UserStatus::ACTIVE->isActive());
        $this->assertFalse(UserStatus::INACTIVE->isActive());
        $this->assertFalse(UserStatus::SUSPENDED->isActive());
    }
}