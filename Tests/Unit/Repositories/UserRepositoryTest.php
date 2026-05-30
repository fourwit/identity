<?php

namespace Modules\Identity\Tests\Unit\Repositories;

use Tests\TestCase;
use Modules\Identity\Repositories\UserRepository;
use Modules\Identity\Models\User;
use Modules\Identity\Models\IdentityProfile;
use Modules\Identity\Enums\UserStatus;
use Modules\Identity\Support\BootstrapsIdentitySchema;

use Illuminate\Foundation\Testing\RefreshDatabase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;
    use BootstrapsIdentitySchema;

    protected UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        config(['identity.views.layout' => 'identity::components.layouts.master', 'identity.features.account_web_routes' => true]);
        $this->bootstrapIdentitySchemaForTests();

        $this->repository = new UserRepository();
    }

    /** @test */
    public function test_shared_mode_create_splits_core_and_profile_fields()
    {
        config([
            'identity.mode' => 'shared',
            'identity.models.user' => User::class,
            'identity.tables.users' => 'users',
            'identity.tables.profiles' => 'identity_profiles',
        ]);

        $created = $this->repository->create([
            'name' => 'Shared User',
            'email' => 'shared@example.com',
            'password' => 'secret1234',
            'first_name' => 'Shared',
            'last_name' => 'Mode',
            'phone' => '9999999999',
            'username' => 'shared_user',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('users', ['id' => $created->id, 'email' => 'shared@example.com']);
        $this->assertDatabaseHas('identity_profiles', ['user_id' => $created->id, 'username' => 'shared_user']);
    }

    /** @test */
    public function test_find_by_uuid_returns_null_when_uuid_column_missing()
    {
        config([
            'identity.mode' => 'shared',
            'identity.models.user' => IdentityProfile::class,
            'identity.tables.users' => 'identity_profiles',
        ]);

        $this->assertNull($this->repository->findByUuid('any-uuid'));
    }

    /** @test */
    public function test_shared_mode_search_filters_by_status_from_identity_profiles()
    {
        config([
            'identity.mode' => 'shared',
            'identity.models.user' => User::class,
            'identity.tables.users' => 'users',
            'identity.tables.profiles' => 'identity_profiles',
        ]);

        $active = $this->repository->create([
            'name' => 'Active Shared',
            'email' => 'active-shared@example.com',
            'password' => 'secret1234',
            'status' => 'active',
        ]);

        $inactive = $this->repository->create([
            'name' => 'Inactive Shared',
            'email' => 'inactive-shared@example.com',
            'password' => 'secret1234',
            'status' => 'inactive',
        ]);

        $results = $this->repository->search(null, 'active');

        $this->assertCount(1, $results);
        $this->assertEquals($active->id, $results->first()->id);
        $this->assertNotEquals($inactive->id, $results->first()->id);
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
        $active = User::factory()->create(['name' => 'Active User']);
        IdentityProfile::updateOrCreate(['user_id' => $active->id], ['status' => 'active']);
        $inactive = User::factory()->create(['name' => 'Inactive User']);
        IdentityProfile::updateOrCreate(['user_id' => $inactive->id], ['status' => 'inactive']);

        $results = $this->repository->search(null, 'active');

        $this->assertCount(1, $results);
        $this->assertEquals(UserStatus::ACTIVE, $results->first()->status);    }
}
