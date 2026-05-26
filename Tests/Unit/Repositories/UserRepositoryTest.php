<?php

namespace Modules\Identity\Tests\Unit\Repositories;

use Tests\TestCase;
use Modules\Identity\Repositories\UserRepository;
use Modules\Identity\Models\User;
use Modules\Identity\Enums\UserStatus;

use Illuminate\Foundation\Testing\RefreshDatabase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new UserRepository();
    }

    /** @test */
    public function test_find_by_id_returns_user()
    {
        $user = User::factory()->create();

        $found = $this->repository->findById($user->id);

        $this->assertNotNull($found);
        $this->assertEquals($user->id, $found->id);
    }

    /** @test */
    public function test_find_by_id_returns_null_when_not_found()
    {
        $found = $this->repository->findById(99999);

        $this->assertNull($found);
    }

    /** @test */
    public function test_find_by_email_returns_user()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $found = $this->repository->findByEmail('test@example.com');

        $this->assertNotNull($found);
        $this->assertEquals('test@example.com', $found->email);
    }

    /** @test */
    public function test_search_returns_matching_users()
    {
        User::factory()->create(['name' => 'John Doe']);
        User::factory()->create(['name' => 'Jane Smith']);

        $results = $this->repository->search('John');

        $this->assertCount(1, $results);
        $this->assertEquals('John Doe', $results->first()->name);
    }

    /** @test */
    public function test_search_with_status_filter()
    {
        User::factory()->create(['name' => 'Active User', 'status' => 'active']);
        User::factory()->create(['name' => 'Inactive User', 'status' => 'inactive']);

        $results = $this->repository->search(null, 'active');

        $this->assertCount(1, $results);
        $this->assertEquals(UserStatus::ACTIVE, $results->first()->status);    }
}